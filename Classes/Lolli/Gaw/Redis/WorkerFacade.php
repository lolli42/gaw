<?php
namespace Lolli\Gaw\Redis;

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