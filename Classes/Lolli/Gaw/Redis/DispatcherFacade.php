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