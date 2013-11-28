<?php
namespace Lolli\Gaw\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class WorkerCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Redis\RedisWorker
	 */
	protected $redis;

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
			$job = $this->redis->blPop('toExecute', 0);
			$job = $job[1];
			var_dump($job);
			$jobArray = json_decode($job, TRUE);
			var_dump($jobArray);
			$command = $jobArray['command'];
			$result = $this->$command($jobArray['data']);
			if (!empty($jobArray['data']['clientBlockingOn'])) {
				// @TODO: format to give back also json?!
				$this->redis->rPush($jobArray['data']['clientBlockingOn'], $result);
			}
			$this->redis->rPush('executed', $job);
		}
	}

	protected function beginBuildBase(array $data) {
		$planet = $this->planetRepository->findOneByPosition($data['galaxyNumber'], $data['systemNumber'], $data['planetNumber']);

		$delay = $this->planetCalculationService->getBaseBuildTime($planet);
		$readyTime = $data['time'] + $delay;
		$doneBuildBaseData = array(
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
			'time' => $readyTime,
		);
		$this->redis->scheduleDelayedJob('doneBuildBase', array($planet->getPlanetPositionString()), $doneBuildBaseData);
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