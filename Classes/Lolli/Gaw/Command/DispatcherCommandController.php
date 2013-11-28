<?php
namespace Lolli\Gaw\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Main game dispatcher
 *
 * @Flow\Scope("singleton")
 */
class DispatcherCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Redis\RedisDispatcher
	 */
	protected $redis;

	/**
	 * Initialize
	 */
	public function initializeObject() {
		$this->checkIntegerPrecision();
		// @TODO: check php.ini default_socket_timeout = -1
	}

	/**
	 * Main dispatcher
	 *
	 * @return void
	 */
	public function runCommand() {
		$this->setUpGameTime();
		$this->mainLoop();
		var_dump('exit');
	}

	/**
	 * Main loop gets ready to execute jobs from schedule queue, finds out if
	 * all of them can be executed in one batch, feeds them to workers and
	 * waits until all workers finished
	 */
	protected function mainLoop() {
		$this->redis->del('toExecute');
		$this->redis->del('executed');

		// @TODO: if all scheduled jobs information is also persisted in database,
		// the only *important* info that can not be re-constructed after redis-failure
		// is gameTime and realTime. Maybe push a change to this to some other (?) worker queue
		// and have a special worker persist that in db? Or rely on aof / rdb?

		$stopDispatcher = FALSE;
		while (!$stopDispatcher) {
			$newGameTime = $this->incrementGameTimeByElapsedRealTime();
			var_dump('new game time: ' . $newGameTime);

			// Get list ob jobs ready to execute from schedule queue and push to worker list
			$executeJobCandidates = $this->redis->zRangeByScore('scheduled', 0, $newGameTime);
			$numberOfToExecuteJobs = 0;
			$tagsOfCurrentBatch = array();
			$toExecuteJobs = array();
			if (count($executeJobCandidates) === 0) {
				// Clients may push to this list to trigger next script loop nearly immediately.
				// This is useful to trigger client "now" job execution if script otherwise "hangs" in waite mode.
				// In case multiple clients pushed meanwhile, just clear out the list to prepare next run.
				// If no client does something, wait for timeout and continue with next loop.
				// This can not be made atomic, since blocking pop does not work in multi/exec.
				$this->redis->blPop('dispatch', 1);
				$this->redis->del('dispatch');
			} else {
				foreach ($executeJobCandidates as $executeJobCandidate) {
					$jobArray = json_decode($executeJobCandidate, TRUE);
					$jobTags = $jobArray['tags'];
					if (count(array_intersect($tagsOfCurrentBatch, $jobTags)) > 0) {
						// Do not queue a job in this batch if an earlier job with the
						// same tag was added already, just let it wait one round, it
						// will be executed early in next batch.
						var_dump('can not queue a job, same tags');
						continue;
					} else {
						// Combine existing and new tags
						$tagsOfCurrentBatch = array_keys(
							array_merge(array_flip($tagsOfCurrentBatch), array_flip($jobTags))
						);
						$numberOfToExecuteJobs++;
						$toExecuteJobs[] = $executeJobCandidate;
						$this->redis->rPush('toExecute', $executeJobCandidate);
						$this->redis->zrem('scheduled', $executeJobCandidate);
					}
				}
				var_dump("$numberOfToExecuteJobs jobs to execute");
			}

			// Wait until all workers finished
			$numberOfExecutedJobs = 0;
			$executedJobs = array();
			if ($numberOfToExecuteJobs > 0) {
				$aJobFailed = FALSE;
				while ($numberOfExecutedJobs < $numberOfToExecuteJobs) {
					// If no job got ready after 2 seconds, assume at least one failed
					$executedJob = $this->redis->blPop('executed', 2);
					$numberOfExecutedJobs++;
					if (count($executedJob) === 0) {
						// Timeout
						var_dump('a job failed');
						$aJobFailed = TRUE;
					} else {
						$executedJobs[] = $executedJob;
					}
				}
				if ($aJobFailed) {
					// If one or multiple jobs failed, find out which ones, re-queue and exit game execution
					$failedJobs = array_diff($toExecuteJobs, $executedJobs);
					foreach ($failedJobs as $failedJob) {
						$jobArray = json_decode($failedJob, TRUE);
						$this->redis->zAdd('scheduled', $jobArray['data']['time'], $failedJob);
					}
					// @TODO: throw exeption?
					$stopDispatcher = TRUE;
				}
			}
		}
	}

	/**
	 * Get current server time, calculate elapsed time to last stored
	 * real time point, increment game time by this difference and
	 * store new times.
	 *
	 * @return integer New game time
	 */
	protected function incrementGameTimeByElapsedRealTime() {
		$this->redis->multi();
		$this->redis->get('realTime');
		$this->redis->time();
		$result = $this->redis->exec();

		$lastRealTime = $result[0];
		$realTime = $this->redis->redisTimeToMicroseconds($result[1]);;

		$elapsedTime = $realTime - $lastRealTime;

		$this->redis->multi();
		$this->redis->set('realTime', $realTime);
		$this->redis->incrBy('gameTime', $elapsedTime);
		$result = $this->redis->exec();

		return (int) $result[1];
	}

	/**
	 * Separate game time from real time.
	 * In the main loop, game time is always incremented by the time
	 * difference between last stored real time and "now".
	 * All scheduled jobs are absolute to game time, never real time.
	 * This allows stopping and re-starting the game at any point,
	 */
	protected function setUpGameTime() {
		// Set game time to 0 if not set, specifies very first start of game
		$gameTime = $this->redis->get('gameTime');
		if ($gameTime === FALSE) {
			$this->redis->set('gameTime', 0);
		}

		$secondsAndMicroSeconds = $this->redis->time();
		$this->redis->set('realTime', $this->redis->redisTimeToMicroseconds($secondsAndMicroSeconds));
	}

	/**
	 * We are calculating with microseconds here. To not work with floats,
	 * unix timestamps are multiplied by 1 million and the number of microseconds
	 * is added, all with integers. We need the according integer precision to
	 * do that, this is checked here.
	 *
	 * @throws Exception
	 */
	protected function checkIntegerPrecision() {
		if (PHP_INT_MAX < (time() * 1000000)) {
			throw new Exception(
				'PHP integer precision is too low, this should not happen on a 64bit system, see PHP_INT_MAX',
				1385479659
			);
		}
	}

}