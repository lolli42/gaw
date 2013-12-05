<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class RedisFacade {

	/**
	 * @var \Redis
	 */
	protected $redis;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Initialize object
	 */
	public function initializeObject() {
		$this->redis = new \Redis();
		$this->redis->connect($this->settings['Redis']['hostname'], $this->settings['Redis']['port']);
		$this->redis->select($this->settings['Redis']['database']);
	}

	/**
	 * Get redis instance directly
	 *
	 * @return \Redis
	 */
	protected function getRedis() {
		return $this->redis;
	}

	/**
	 * Get "now" game time in microseconds
	 *
	 * @return int
	 */
	protected function getGameTimeNow() {
		$this->redis->multi();
		$this->redis->get('realTime');
		$this->redis->time();
		$this->redis->get('gameTime');
		$result = $this->redis->exec();

		$lastRealTime = $result[0];
		$realTime = $this->redisTimeToMicroseconds($result[1]);
		$gameTime = $result[2];

		return (int) ($gameTime + ($realTime - $lastRealTime));
	}

	/**
	 * array($seconds, $microseconds) -> integer time in microseconds
	 *
	 * @param array $redisTime
	 * @return mixed
	 */
	public function redisTimeToMicroseconds(array $redisTime) {
		return ($redisTime[0] * 1000000) + $redisTime[1];
	}
}