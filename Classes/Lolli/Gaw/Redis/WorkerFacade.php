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
 * Redis object for "worker"
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

	public function pushClientFeedback($channel, $result) {
		// @TODO: format to give back also json?!
		$this->redis->rPush($channel, $result);
	}

	public function notifyDispatcherJobCompleted(array $job) {
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
}