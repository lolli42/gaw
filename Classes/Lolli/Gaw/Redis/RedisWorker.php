<?php
namespace Lolli\Gaw\Redis;

use TYPO3\Flow\Annotations as Flow;

/**
 * Redis object for "worker"
 *
 * @Flow\Scope("singleton")
 */
class RedisWorker extends Redis {

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

	/**
	 * Schedule a job for "later"
	 * Time when job should be executed must be set in $data['time']
	 *
	 * @param $command
	 * @param array $tags
	 * @param array $data
	 */
	public function scheduleDelayedJob($command, array $tags, array $data) {
		$dataString = json_encode(
			array(
				'command' => $command,
				'tags' => $tags,
				'data' => $data
			)
		);
		$this->zAdd('scheduled', $data['time'], $dataString);
	}
}