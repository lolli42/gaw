<?php
namespace Lolli\Gaw\Redis;

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
 * Redis facade for "worker"
 *
 * @Flow\Scope("singleton")
 */
class WorkerFacade extends RedisFacade {

	/**
	 * @return array
	 */
	public function waitForJob() {
		return $this->redis->blPop('lolli:gaw:toExecute', 0);
	}

	public function getJobArrayFromJob($job) {
		$jobArray = $job[1];
		return json_decode($jobArray, TRUE);
	}

	public function pushClientFeedback($channel, array $result) {
		$this->redis->rPush($channel, json_encode($result));
	}

	public function notifyDispatcherJobCompleted($job) {
		$this->redis->rPush('lolli:gaw:executed', $job);
	}

	/**
	 * Schedule a job for "later"
	 * Time when job should be executed must be set in $data['time']
	 *
	 * @param array $data
	 */
	public function scheduleDelayedJob(array $data) {
		$this->redis->zAdd('lolli:gaw:mainQueue', $data['time'], json_encode($data));
	}

	/**
	 * Remove a job from main queue
	 *
	 * @param array $data
	 * @throws Exception
	 */
	public function removeOneScheduledJob(array $data) {
		$result = $this->redis->zrem('lolli:gaw:mainQueue', json_encode($data));
		if ($result !== 1) {
			throw new Exception(
				'Expected to remove exactly one command from queue, but result is ' . $result, 1386797216
			);
		}
	}
}