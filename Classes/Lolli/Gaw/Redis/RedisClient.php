<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * A redis "web" client
 *
 * @Flow\Scope("singleton")
 */
class RedisClient extends Redis {

	/**
	 * inject and initialize are not in base class because of issue http://forge.typo3.org/issues/53180
	 */

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @return Redis
	 */
	public function initializeObject() {
		$this->connect($this->settings['Redis']['hostname'], $this->settings['Redis']['port']);
		$this->select($this->settings['Redis']['database']);
		return $this;
	}


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
		$this->zAdd('scheduled', $time, $dataString);
		$this->rPush('dispatch', TRUE);
		return TRUE;
	}

	public function scheduleBlockingJob($command, array $tags, array $data) {
		// @TODO: should probably throw exceptions, also overwrites true/false from now() method
		$clientBlocking = uniqid('client:Blocking:');
		$data['clientBlockingOn'] = $clientBlocking;
		$this->scheduleNowJob($command, $tags, $data);
		$result = TRUE;
		$feedback = $this->blPop($clientBlocking, 2);
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
		$this->multi();
		$this->get('realTime');
		$this->time();
		$result = $this->exec();
		$now = $this->redisTimeToMicroseconds($result[1]);
		// 10 seconds
		if ($now - $result[0] > 10000000) {
			return FALSE;
		}
		return TRUE;
	}
}