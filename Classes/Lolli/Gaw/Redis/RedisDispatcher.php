<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * Redis class for dispatcher
 *
 * @Flow\Scope("singleton")
 */
class RedisDispatcher extends Redis {

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
}