<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class Redis extends \Redis {

	/**
	 * @var array
	 */
	protected $settings;

	protected function getGameTimeNow() {
		$this->multi();
		$this->get('realTime');
		$this->time();
		$this->get('gameTime');
		$result = $this->exec();

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