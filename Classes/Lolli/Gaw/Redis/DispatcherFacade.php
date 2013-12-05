<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * Redis class for dispatcher
 *
 * @Flow\Scope("singleton")
 */
class DispatcherFacade extends RedisFacade {

	/**
	 * Get redis instance directly, public for dispatcher so
	 * dispatcher can call redis methods directly.
	 *
	 * @return \Redis
	 */
	public function getRedis() {
		return parent::getRedis();
	}
}