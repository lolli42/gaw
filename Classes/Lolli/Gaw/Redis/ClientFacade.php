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
 * Redis facade for "client"
 *
 * @Flow\Scope("singleton")
 */
class ClientFacade extends RedisFacade {

	/**
	 * Put a job into queue and wait until it is finished.
	 * This allows direct feedback from worker.
	 *
	 * @param array $data Queue data
	 * @return array Feedback, contains at least 'success' boolean key
	 * @throws Exception If there is no response within recent time
	 */
	public function scheduleBlockingJob(array $data) {
		$this->testDispatcherIsRunning();
		$clientBlocking = uniqid('lolli:gaw:client:blocking:');
		$data['clientBlockingOn'] = $clientBlocking;
		$this->scheduleNowJob($data);
		$feedback = $this->redis->blPop($clientBlocking, 2);
		if (count($feedback) === 0) {
			throw new Exception('No feedback from dispatcher');
		}
		return json_decode($feedback[1], TRUE);
	}

	/**
	 * Schedule a job to be executed "now".
	 * Does not wait for a result.
	 *
	 * @param array $data Queue data
	 */
	public function scheduleNowJob(array $data) {
		$this->testDispatcherIsRunning();
		$time = $this->getGameTimeNow();
		$data['time'] = $time;
		$this->redis->zAdd('lolli:gaw:mainQueue', $time, json_encode($data));
		$this->redis->rPush('lolli:gaw:triggerDispatcher', TRUE);
	}

	/**
	 * If saved real time and "now" differs for some seconds, it
	 * is assumed that the dispatcher is down.
	 *
	 * @throws Exception
	 */
	protected function testDispatcherIsRunning() {
		$this->redis->multi();
		$this->redis->get('lolli:gaw:realTime');
		$this->redis->time();
		$result = $this->redis->exec();
		$now = $this->redisTimeToMicroseconds($result[1]);
		// 10 seconds
		if ($now - $result[0] > 10000000) {
			throw new Exception('Dispatcher down');
		}
	}
}