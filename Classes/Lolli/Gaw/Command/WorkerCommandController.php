<?php
namespace Lolli\Gaw\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Game worker
 *
 * @Flow\Scope("singleton")
 */
class WorkerCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Redis\WorkerFacade
	 */
	protected $redisFacade;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlanetRepository
	 */
	protected $planetRepository;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Main worker loop
	 *
	 * @return void
	 */
	public function runCommand() {
		var_dump('ready to work');
		while (TRUE) {
			$job = $this->redisFacade->waitForJob();
			$jobArray = $this->redisFacade->getJobArrayFromJob($job);
			var_dump($jobArray);
			$command = $jobArray['command'];
			try {
				$result = $this->$command($jobArray);
				$result['success'] = TRUE;
			} catch (Exception\CatchableWorkerException $e) {
				$result['success'] = FALSE;
				$result['exceptionMessage'] = $e->getMessage();
				$result['exceptionCode'] = $e->getCode();
			}
			if (!empty($jobArray['clientBlockingOn'])) {
				$this->redisFacade->pushClientFeedback($jobArray['clientBlockingOn'], $result);
			}
			$this->redisFacade->notifyDispatcherJobCompleted($job[1]);
		}
	}

	/**
	 * Update resources produced meanwhile
	 *
	 * @param array $data Data to work on
	 * @return array
	 */
	protected function updateResourcesOnPlanet(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$time = $this->redisFacade->getGameTimeNow();
		$newResources = $this->planetCalculationService->resourcesProducedUntil($planet, $time);
		$this->addResourcesToPlanet($planet, $newResources);
		$planet->setLastResourceUpdate($time);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
		return array();
	}

	/**
	 * Find a not settled planet position and create a planet here
	 * Triggered by "client"
	 *
	 * @param array $data Data to work on - unused here
	 * @return array Result data
	 */
	protected function createRandomPlanet(array $data) {
		// @TODO: Make smarter - This may take a while if there are not many open positions left
		$foundPlanet = FALSE;
		do {
			$galaxyNumber = 1;
			$systemNumber = mt_rand(1, 300);
			$planetNumber = mt_rand(1, 12);
			$existingPlanet = $this->planetRepository->findOneByPosition($galaxyNumber, $systemNumber, $planetNumber);
			if (!($existingPlanet instanceof \Lolli\Gaw\Domain\Model\Planet)) {
				$foundPlanet = TRUE;
			}
		} while ($foundPlanet == FALSE);
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setGalaxyNumber($galaxyNumber);
		$planet->setSystemNumber($systemNumber);
		$planet->setPlanetNumber($planetNumber);
		$planet->setLastResourceUpdate($this->redisFacade->getGameTimeNow());
		$planet->setPoints($this->planetCalculationService->calculateTotalPoints($planet));
		$this->planetRepository->add($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
		return array(
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
	}

	/**
	 * Add a structure to planet build queue
	 * Triggered by "client"
	 *
	 * @param array $data Data to work on
	 * @throws Exception\CatchableWorkerException Logic errors that may happen
	 * @throws Exception\WorkerException Logic errors that are programming errors
	 * @return array Result
	 */
	protected function addPlanetStructureToBuildQueue(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);

		// Do not queue if already maximum number of structures are in build queue, may happen -> catchable!
		if ($planet->getStructureBuildQueue()->count() >= $this->settings['Game']['Planet']['maximumStructureQueueLength']) {
			throw new Exception\CatchableWorkerException(
				'Can not queue structure building, maximum queue length reached', 1386788435
			);
		}

		$structureName = $data['structureName'];

		// Next level to build
		$method = 'get' . ucfirst($structureName);
		$currentLevel = $planet->$method();
		$inQueue = $this->planetCalculationService->countSpecificStructuresInBuildQueue($planet, $structureName);
		$nextLevel = $currentLevel + $inQueue + 1;

		// Do not queue if building is not available due to tech tree: May happen if using multiple tabs -> catchable!
		if (!$this->planetCalculationService->isStructureAvailable($planet, $structureName, $nextLevel)) {
			throw new Exception\CatchableWorkerException(
				'Can not queue structure building, tech tree not fulfilled', 1386886693
			);
		}

		// Do not queue if resources are not available: May eg. happen if something else was build in other tab -> catchable!
		$requiredResources = $this->planetCalculationService->getResourcesRequiredForStructureLevel($structureName, $nextLevel);
		if (!$this->planetCalculationService->isResourcesAvailable($planet, $requiredResources)) {
			throw new Exception\CatchableWorkerException(
				'Can not queue structure building, not enough resources', 1386887424
			);
		}

		$readyTime = $this->planetCalculationService->getReadyTimeOfStructure($planet, $structureName, $data['time']);

		// Add increment job
		// [!!!] This data and order (!) has to be the same data as in removeLastStructureFromBuildQueueAction()
		// @TODO: Remove duplicate array definitions (all over the place, also in "client" controllers)
		$data = array(
			'command' => 'incrementPlanetStructure',
			'structureName' => $structureName,
			'tags' => array($planet->getPlanetPositionString()),
			'time' => $readyTime,
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		$this->redisFacade->scheduleDelayedJob($data);

		$structureBuildQueueItem = new \Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem();
		$structureBuildQueueItem->setName($structureName);
		$structureBuildQueueItem->setReadyTime($readyTime);
		$planet->addStructureToStructureBuildQueue($structureBuildQueueItem);

		// Decrement planet resources
		$this->removeResourcesFromPlanet($planet, $requiredResources);

		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		// @TODO: Detach the queue item here, too? If so: Refactor detach to own class separated from repo?
		$this->planetRepository->detach($planet);

		return array(
			'readyTime' => $readyTime
		);
	}

	/**
	 * Remove the last item from planet build queue
	 * Triggered by "client"
	 *
	 * @param array $data Data to work on
	 * @throws Exception\CatchableWorkerException
	 * @return array Result
	 */
	protected function removeLastStructureFromBuildQueue(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);

		// Stop if queue is empty
		$planetStructureQueue = $planet->getStructureBuildQueue();
		if ($planetStructureQueue->isEmpty()) {
			throw new Exception\CatchableWorkerException(
				'Can not cancel planet structure build queue item, queue is empty', 1386789606
			);
		}

		/** @var \Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem $lastQueueItem */
		$lastQueueItem = $planetStructureQueue->last();
		$structureName = $lastQueueItem->getName();

		// Cancel 'increment' job
		// [!!!] This data and order (!) has to be the same as data in addPlanetStructureToBuildQueue()
		// @TODO: Remove duplicate array definitions (all over the place, also in "client" controllers)
		$data = array(
			'command' => 'incrementPlanetStructure',
			'structureName' => $structureName,
			'tags' => array($planet->getPlanetPositionString()),
			'time' => $lastQueueItem->getReadyTime(),
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		// Method will throw an exception if job was not found, we can safely continue here if it doesn't
		$this->redisFacade->removeOneScheduledJob($data);

		// Increment planet resources
		$inQueue = $this->planetCalculationService->countSpecificStructuresInBuildQueue($planet, $structureName);
		$method = 'get' . ucfirst($structureName);
		$levelToAbort = $planet->$method() + $inQueue;
		$resourcesForLevel = $this->planetCalculationService->getResourcesRequiredForStructureLevel($structureName, $levelToAbort);
		$this->addResourcesToPlanet($planet, $resourcesForLevel);

		// Remove queue item
		$planetStructureQueue->removeElement($lastQueueItem);

		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		// @TODO: Detach the queue item here, too? If so: Refactor detach to own class separated from repo?
		$this->planetRepository->detach($planet);
	}

	/**
	 * Increment a planet structure level
	 * Triggered by "worker"
	 *
	 * @param array $data Queued data
	 * @throws Exception
	 */
	protected function incrementPlanetStructure(array $data) {
		$structureName = $data['structureName'];
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$currentBuildQueue = $planet->getStructureBuildQueue();
		if ($currentBuildQueue->count() < 1) {
			throw new Exception\WorkerException(
				'Expected to find at least one item in build queue, but it is empty', 1386609470
			);
		}
		/** @var \Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem $currentBuildQueueItem */
		$currentBuildQueueItem = $currentBuildQueue->first();
		if ($currentBuildQueueItem->getName() !== $data['structureName']) {
			throw new Exception\WorkerException(
				'Top most queue item name does not correspond with expected structure name', 1386609692
			);
		}
		if ($currentBuildQueueItem->getReadyTime() !== (int)$data['time']) {
			throw new Exception\WorkerException(
				'Top most queue item ready time does not correspond with expected ready time', 1386609761
			);
		}
		$newResources = $this->planetCalculationService->resourcesProducedUntil($planet, $data['time']);
		$this->addResourcesToPlanet($planet, $newResources);
		$planet->setLastResourceUpdate($data['time']);
		$incrementMethodName = 'increment' . ucfirst($structureName);
		$planet->$incrementMethodName();
		$planet->setPoints($this->planetCalculationService->calculateTotalPoints($planet));
		$planet->removeStructureFromBuildQueue($currentBuildQueueItem);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
	}

	/**
	 * Add given resources to planet
	 *
	 * @param \Lolli\Gaw\Domain\Model\Planet $planet
	 * @param array $resources The resources to add
	 */
	protected function addResourcesToPlanet(\Lolli\Gaw\Domain\Model\Planet $planet, array $resources) {
		foreach ($resources as $resourceName => $units) {
			$getter = 'get' . ucfirst($resourceName);
			$setter = 'set' . ucfirst($resourceName);
			$currentUnits = $planet->$getter();
			$planet->$setter($currentUnits + $units);
		}
	}

	/**
	 * Remove given resources from planet
	 *
	 * @param \Lolli\Gaw\Domain\Model\Planet $planet
	 * @param array $resources The resources to remove
	 */
	protected function removeResourcesFromPlanet(\Lolli\Gaw\Domain\Model\Planet $planet, array $resources) {
		foreach ($resources as $resourceName => $units) {
			$getter = 'get' . ucfirst($resourceName);
			$setter = 'set' . ucfirst($resourceName);
			$currentUnits = $planet->$getter();
			$planet->$setter($currentUnits - $units);
		}
	}
}