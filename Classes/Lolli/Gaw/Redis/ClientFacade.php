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
 * A redis "web" client
 *
 * @Flow\Scope("singleton")
 */
class ClientFacade extends RedisFacade {

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function scheduleBlockingJob(array $data) {
		$clientBlocking = uniqid('lolli:gaw:client:blocking:');
		$data['clientBlockingOn'] = $clientBlocking;
		$this->scheduleNowJob($data);
		$feedback = $this->redis->blPop($clientBlocking, 2);
		if (count($feedback) === 0) {
			throw new Exception('No feedback from dispatcher');
		}
		return json_decode($feedback[1], TRUE);
	}

	public function scheduleNowJob(array $data) {
		$this->testDispatcherIsRunning();
		$time = $this->getGameTimeNow();
		$data['time'] = $time;
		$this->redis->zAdd('lolli:gaw:mainQueue', $time, json_encode($data));
		$this->redis->rPush('lolli:gaw:triggerDispatcher', TRUE);
	}

	/**
	 * If saved realtime and "now" differs for some seconds, it
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