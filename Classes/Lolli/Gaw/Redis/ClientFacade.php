<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * A redis "web" client
 *
 * @Flow\Scope("singleton")
 */
class ClientFacade extends RedisFacade {

	public function scheduleNowJob($command, array $tags, array $data) {
		// @TODO: should probably throw exceptions
		if (!$this->testDispatcherIsRunning()) {
			return FALSE;
		}
		$time = $this->getGameTimeNow();
		$data['time'] = $time;
		$dataString = json_encode(
			array(
				'command' => $command,
				'tags' => $tags,
				'data' => $data,
			)
		);
		$this->redis->zAdd('lolli:gaw:mainQueue', $time, $dataString);
		$this->redis->rPush('lolli:gaw:triggerDispatcher', TRUE);
		return TRUE;
	}

	public function scheduleBlockingJob($command, array $tags, array $data) {
		// @TODO: should probably throw exceptions, also overwrites true/false from now() method
		$clientBlocking = uniqid('lolli:gaw:client:blocking:');
		$data['clientBlockingOn'] = $clientBlocking;
		$this->scheduleNowJob($command, $tags, $data);
		$result = TRUE;
		$feedback = $this->redis->blPop($clientBlocking, 2);
		if (count($feedback) === 0) {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * If saved realtime and "now" differs for some seconds, it
	 * is assumed that the dispatcher is down.
	 *
	 * @return bool TRUE if system is up
	 */
	protected function testDispatcherIsRunning() {
		$this->redis->multi();
		$this->redis->get('lolli:gaw:realTime');
		$this->redis->time();
		$result = $this->redis->exec();
		$now = $this->redisTimeToMicroseconds($result[1]);
		// 10 seconds
		if ($now - $result[0] > 10000000) {
			return FALSE;
		}
		return TRUE;
	}
}