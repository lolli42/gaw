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
			$this->redisFacade->notifyDispatcherJobCompleted($job);
		}
	}

	protected function beginBuildBase(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);

		$delay = $this->planetCalculationService->getBaseBuildTime($planet);
		$readyTime = $data['time'] + $delay;
		$doneBuildBaseData = array(
			'command' => 'doneBuildBase',
			'tags' => array($planet->getPlanetPositionString()),
			'time' => $readyTime,
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		$this->redisFacade->scheduleDelayedJob($doneBuildBaseData);
		$planet->setStructureInProgress(1);
		$planet->setStructureReadyTime($readyTime);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
		return TRUE;
	}

	protected function doneBuildBase(array $data) {
		//updateRessOnPlaniAtTime($data['time']);
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);
		$planet->incrementBase();
		$planet->setStructureInProgress(0);
		$planet->setStructureReadyTime(0);
		$this->planetRepository->update($planet);
		$this->persistenceManager->persistAll();
	}

	// updateRessOnPlaniAtTime()

}