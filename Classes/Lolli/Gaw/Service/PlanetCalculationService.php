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
		'iron' => 0.05, // micro units per micro second -> 60 * 60 -> 180 units / h
		'silicon' => 0.05,
		'xenon' => 0.05,
		'hydrazine' => 0.05,
		'energy' => 0.05,
	);

	/**
	 * Formulas for resource calculations of structure building level requirements
	 *
	 * @var array
	 */
	protected $structureResourceRequirements = array(
		'base' => array(
			'iron' => '(51 * pow($x, 2) - 102 * $x + 102) * 1000000',
			'silicon' => '(31 * pow($x, 2) - 62 * $x + 62) * 1000000',
			'xenon' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
		),
		'ironMine' => array(
			'iron' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
			'silicon' => '(32 * pow($x, 2) - 64 * $x + 64) * 1000000',
			'energy' => '(7 * pow($x, 2) - 14 * $x + 14) * 1000000',
		),
		'siliconMine' => array(
			'iron' => '(33 * pow($x, 2) - 66 * $x + 66) * 1000000',
			'silicon' => '(24 * pow($x, 2) - 48 * $x + 48) * 1000000',
			'energy' => '(6 * pow($x, 2) - 12 * $x + 12) * 1000000',
		),
		'xenonMine' => array(
			'iron' => '(3 * pow($x, 2) - 6 * $x + 6) * 1000000',
			'silicon' => '(40 * pow($x, 2) - 80 * $x + 80) * 1000000',
			'energy' => '(23 * pow($x, 2) - 46 * $x + 46) * 1000000',
		),
		'hydrazineMine' => array(
			'iron' => '(12 * pow($x, 2) - 24 * $x + 24) * 1000000',
			'silicon' => '(42 * pow($x, 2) - 84 * $x + 84) * 1000000',
			'xenon' => '(22 * pow($x, 2) - 44 * $x + 44) * 1000000',
			'energy' => '(23 * pow($x, 2) - 46 * $x + 46) * 1000000',
		),
		'energyMine' => array(
			'iron' => '(61 * pow($x, 2) - 122 * $x + 122) * 1000000',
			'silicon' => '(53 * pow($x, 2) - 106 * $x + 106) * 1000000',
			'xenon' => '(46 * pow($x, 2) - 92 * $x + 92) * 1000000',
			'hydrazine' => '(34 * pow($x, 2) - 68 * $x + 68) * 1000000',
		),
	);

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
	 * Get structure techtree
	 *
	 * @return array
	 */
	public function getStructureTechTree() {
		return $this->structureTechTree;
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
			throw new Exception(
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
	 * Number of a specific structure currently in build queue.
	 *
	 * @param Planet $planet Given planet
	 * @param string $structureName Name of structure
	 * @return integer Count
	 */
	public function countSpecificStructuresInBuildQueue(Planet $planet, $structureName) {
		$inQueue = 0;
		$queuedStructures = $planet->getStructureBuildQueue();
		foreach ($queuedStructures as $queuedStructure) {
			/** @var $queuedStructure \Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem */
			if ($queuedStructure->getName() === $structureName) {
				$inQueue = $inQueue + 1;
			}
		}
		return $inQueue;
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
	 * Calculate the resources needed for a specific building level.
	 *
	 * @param string $structureName The structure to calculate, eg. 'base' or 'ironMine'
	 * @param integer $level Level of building
	 * @throws Exception
	 * @return array Resource cost: Key is the resource, value the number of micro units
	 */
	public function getResourcesRequiredForStructureLevel($structureName, $level) {
		if (!isset($this->structureResourceRequirements[$structureName])) {
			throw new Exception(
				'Structure does not exist', 1386872360
			);
		}
		$resourceArray = array();
		foreach ($this->structureResourceRequirements[$structureName] as $resourceName => $formula) {
			$amount = $this->getResourceRequiredForStructureLevel($structureName, $level, $resourceName);
			$resourceArray[$resourceName] = $amount;
		}
		return $resourceArray;
	}

	/**
	 * Calculate amount of micro units required to build a structure level by evaluating
	 * the math functions defined in $this->structureResourceRequirements
	 *
	 * @param integer $structureName Name of structure
	 * @param integer $x Level of structure
	 * @param string $resourceName Name of resource, eg. 'iron'
	 * @return integer Number of micro units
	 * @throws Exception
	 */
	public function getResourceRequiredForStructureLevel($structureName, $x, $resourceName) {
		if (!is_integer($x) || $x < 0) {
			throw new Exception(
				'Structure level is out of bounds or smaller than zero', 1386872139
			);
		}
		if (!isset($this->structureResourceRequirements[$structureName])) {
			throw new Exception(
				'Structure does not exist', 1386872359
			);
		}
		$units = 0;
		if (isset($this->structureResourceRequirements[$structureName][$resourceName])) {
			$formula = $this->structureResourceRequirements[$structureName][$resourceName];
			eval('$units = ' . $formula . ';');
			$units = (int)round($units);
		}
		return $units;
	}

	/**
	 * Find out if given resources are available on planet
	 *
	 * @param Planet $planet Planet to handle
	 * @param array $resources Resources to check for, eg. iron=20, silicon=40
	 * @return TRUE if all Resources are available on planet
	 */
	public function isResourcesAvailable(Planet $planet, array $resources) {
		$result = TRUE;
		foreach ($resources as $resourceName => $amount) {
			$result = $result & $this->isResourceAvailable($planet, $resourceName, $amount);
		}
		return $result;
	}

	/**
	 * TRUE if at least this amount of resources is available on planet
	 *
	 * @param Planet $planet Given planet
	 * @param string $resourceName Name of resource, eg. 'iron'
	 * @param string $amount Amount in micro units to check for
	 * @return boolean TRUE if this amount of resource is available
	 */
	public function isResourceAvailable(Planet $planet, $resourceName, $amount) {
		$result = TRUE;
		$propertyName = 'get' . ucfirst($resourceName);
		if ($planet->$propertyName() - $amount < 0) {
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * Calculate resources produced by planet until given time
	 *
	 * @param Planet $planet Planet to work on
	 * @param integer $time Absolute game time in microseconds
	 * @throws Exception If time is in the past in comparison to last resource update time
	 * @return array Resources
	 */
	public function resourcesProducedUntil(Planet $planet, $time) {
		if ($time < $planet->getLastResourceUpdate()) {
			throw new Exception('Given time must not be lower than last resource update time', 1386523956);
		}
		$elapsedTime = $time - $planet->getLastResourceUpdate();
		$iron = (int)($elapsedTime * $this->basicProduction['iron']);
		$silicon = (int)($elapsedTime * $this->basicProduction['silicon']);
		$xenon = (int)($elapsedTime * $this->basicProduction['xenon']);
		$hydrazine = (int)($elapsedTime * $this->basicProduction['hydrazine']);
		$energy = (int)($elapsedTime * $this->basicProduction['energy']);
		return array(
			'iron' => $iron,
			'silicon' => $silicon,
			'xenon' => $xenon,
			'hydrazine' => $hydrazine,
			'energy' => $energy,
		);
	}
}