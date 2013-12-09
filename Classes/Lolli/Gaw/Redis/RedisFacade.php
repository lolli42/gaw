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
	 * @return int Timestamp in microseconds
	 */
	public function getGameTimeNow() {
		$this->redis->multi();
		$this->redis->get('lolli:gaw:realTime');
		$this->redis->time();
		$this->redis->get('lolli:gaw:gameTime');
		$result = $this->redis->exec();

		$lastRealTime = $result[0];
		$realTime = $this->redisTimeToMicroseconds($result[1]);
		$gameTime = $result[2];

		return (int)($gameTime + ($realTime - $lastRealTime));
	}

	/**
	 * Get "current" real time.
	 * Should only be used in fluid and such to calculate ready times,
	 * everything else must be relative to game time!
	 *
	 * @return int Timestamp in microseconds
	 */
	public function getRealTimeNow() {
		$redisTime = $this->redis->time();
		return $this->redisTimeToMicroseconds($redisTime);
	}

	/**
	 * array($seconds, $microseconds) -> integer time in microseconds
	 *
	 * @param array $redisTime
	 * @return int Timestamp in microseconds
	 */
	public function redisTimeToMicroseconds(array $redisTime) {
		return (int)(($redisTime[0] * 1000000) + $redisTime[1]);
	}
}