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
	 * A worker
	 *
	 * @return void
	 */
	public function runCommand() {
		while (TRUE) {
			$job = $this->redisFacade->waitForJob();
			$jobArray = $this->redisFacade->getJobArrayFromJob($job);
			var_dump($jobArray);
			$command = $jobArray['command'];
			$result = $this->$command($jobArray);
			if (!empty($jobArray['clientBlockingOn'])) {
				$this->redisFacade->pushClientFeedback($jobArray['clientBlockingOn'], $result);
			}
			$this->redisFacade->notifyDispatcherJobCompleted($job[1]);
		}
	}

	protected function updateResourcesOnPlanet(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$time = $this->redisFacade->getGameTimeNow();
		$newResources = $this->planetCalculationService->resourcesAtTime($planet, $time);
		$planet->setIron($newResources['iron']);
		$planet->setSilicon($newResources['silicon']);
		$planet->setXenon($newResources['xenon']);
		$planet->setHydrazine($newResources['hydrazine']);
		$planet->setEnergy($newResources['energy']);
		$planet->setLastResourceUpdate($time);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
		return array('foo');
	}

	protected function createRandomPlanet(array $data) {
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
		$this->planetRepository->add($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
		return array(
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
	}

	protected function addPlanetStructureToBuildQueue(array $data) {
		// @TODO: Stop if in progress
		// @TODO: Check if building is available according to tech tree
		// @TODO: Handle structure in progress correctly
		$structureName = $data['structureName'];
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$delay = $this->planetCalculationService->getBuildTimeOfStructure($planet, $structureName);
		$readyTime = $data['time'] + $delay;
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
		$planet->setStructureInProgress(1);
		$planet->setStructureReadyTime($readyTime);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
		return array('readyTime' => $readyTime);
	}

	protected function incrementPlanetStructure(array $data) {
		//@TODO updateRessOnPlaniAtTime($data['time']);
		$structureName = $data['structureName'];
		$incrementMethodName = 'increment' . ucfirst($structureName);
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$planet->$incrementMethodName();
		$planet->setStructureInProgress(0);
		$planet->setStructureReadyTime(0);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		$this->planetRepository->detach($planet);
	}
}