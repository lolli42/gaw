<?php
namespace Lolli\Gaw\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Lolli\Gaw\Domain\Model\Planet;
use TYPO3\Flow\Annotations as Flow;

/**
 * Planet specifications and calculation
 *
 * @Flow\Scope("singleton")
 */
class PlanetCalculationService {

	/**
	const STRUCTURE_NONE = '';
	const STRUCTURE_BASE = 'base';
	const STRUCTURE_IRON = 'ironMine';
	const STRUCTURE_SILICON = 'siliconMine';
	const STRUCTURE_XENON = 'xenonMine';
	const STRUCTURE_HYDRAZINE = 'hydrazineMine';
	const STRUCTURE_ENERGY = 'energyMine';
	 */

	/**
	 * Technology tree for structures
	 *
	 * @var array
	 */
	protected $structureTechTree = array(
		'base' => array(),
		'ironMine' => array(
			'base' => 1,
		),
		'siliconMine' => array(
			'base' => 1,
		),
		'xenonMine' => array(
			'base' => 2,
			'ironMine' => 1,
			'siliconMine' => 1,
		),
		'hydrazineMine' => array(
			'base' => 2,
			'ironMine' => 1,
			'siliconMine' => 1,
		),
		'energyMine' => array(
			'base' => 2,
			'ironMine' => 1,
			'siliconMine' => 1,
			'hydrazineMine' => 2,
		),
	);

	/**
	 * Points for each structure level
	 *
	 * @var array
	 */
	protected $pointsByStructure = array(
		'base' => 25,
		'ironMine' => 10,
		'siliconMine' => 10,
		'xenonMine' => 15,
		'hydrazineMine' => 20,
		'energyMine' => 25,
	);

	/**
	 * Basic resource production
	 *
	 * @var array
	 */
	protected $basicProduction = array(
		'iron' => 0.05, // microunits per mircosecond -> 60 * 60 -> 180 units / h
		'silicon' => 0.05,
		'xenon' => 0.05,
		'hydrazine' => 0.05,
		'energy' => 0.05,
	);

	/**
	 * Get structure techtree
	 *
	 * @return array
	 */
	public function getStructureTechTree() {
		return $this->structureTechTree;
	}

	/**
	 * Get points by structure table
	 *
	 * @return array
	 */
	public function getPointsByStructure() {
		return $this->pointsByStructure;
	}

	/**
	 * Calculate points of planet
	 *
	 * @param Planet $planet The planet to work on
	 * @return integer points
	 */
	public function calculateTotalPoints(Planet $planet) {
		$pointsByStructure = $this->pointsByStructure;
		$total = 0;
		foreach ($pointsByStructure as $structureName => $points) {
			$methodName = 'get' . ucfirst($structureName);
			$total = $total + $planet->$methodName() * $points;
		}
		return $total;
	}

	/**
	 * Whether a structure can be build on planet according to tech tree
	 *
	 * @param Planet $planet Planet to check
	 * @param string $structure The structure to build
	 * @throws Exception
	 * @return boolean TRUE if structure can be build
	 */
	public function isStructureAvailable(Planet $planet, $structure) {
		if (!isset($this->structureTechTree[$structure])) {
			throw new \Lolli\Gaw\Service\Exception(
				'Structure not in techtree', 1386595094
			);
		}
		$techTreeOfStructure = $this->structureTechTree[$structure];
		$result = TRUE;
		foreach ($techTreeOfStructure as $structureName => $requiredLevel) {
			$propertyName = 'get' . ucfirst($structureName);
			if ($planet->$propertyName() < $requiredLevel) {
				$result = FALSE;
			}
		}
		return $result;
	}

	/**
	 * Calculate time to build a structure
	 *
	 * @param Planet $planet Planet to work on
	 * @param string $structureName The structure to build
	 * @return integer seconds
	 */
	public function getBuildTimeOfStructure(Planet $planet, $structureName) {
		// @TODO: Method needs to check queue if a base is queued for the same building already
		// 20 secs
		return 20000000;
		// return (int)(1000000 * ($planet->getBase() + 1)); // 32 * 60
	}

	/**
	 * Calculate when a specific structure is ready
	 *
	 * @param Planet $planet Planet to work on
	 * @param string $structureName The structure to build
	 * @param integer $time Time as offset, used if build queue is empty
	 * @return int Ready time in microseconds
	 */
	public function getReadyTimeOfStructure(Planet $planet, $structureName, $time) {
		$buildTime = $this->getBuildTimeOfStructure($planet, $structureName);
		$currentBuildQueue = $planet->getStructureBuildQueue();
		if ($currentBuildQueue->count() > 0) {
			/** @var \Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem $lastStructureInQueue */
			$lastStructureInQueue = $currentBuildQueue->last();
			$readyTime = $lastStructureInQueue->getReadyTime() + $buildTime;
		} else {
			$readyTime = $time + $buildTime;
		}
		return $readyTime;
	}

	/**
	 * Calculate resources on planet at given time
	 *
	 * @param Planet $planet Planet to work on
	 * @param integer $time Absolute game time in microseconds
	 * @throws Exception If time is in the past in comparison to last resource update time
	 * @return array Resources
	 */
	public function resourcesAtTime(Planet $planet, $time) {
		if ($time < $planet->getLastResourceUpdate()) {
			throw new Exception('Given time must not be lower than last resource update time', 1386523956);
		}
		$elapsedTime = $time - $planet->getLastResourceUpdate();
		$iron = $planet->getIron() + (int)($elapsedTime * $this->basicProduction['iron']);
		$silicon = $planet->getSilicon() + (int)($elapsedTime * $this->basicProduction['silicon']);
		$xenon = $planet->getXenon() + (int)($elapsedTime * $this->basicProduction['xenon']);
		$hydrazine = $planet->getHydrazine() + (int)($elapsedTime * $this->basicProduction['hydrazine']);
		$energy = $planet->getEnergy() + (int)($elapsedTime * $this->basicProduction['energy']);
		return array(
			'iron' => $iron,
			'silicon' => $silicon,
			'xenon' => $xenon,
			'hydrazine' => $hydrazine,
			'energy' => $energy,
		);
	}
}