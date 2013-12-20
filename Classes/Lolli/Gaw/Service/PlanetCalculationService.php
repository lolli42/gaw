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
			'base' => '$x',
		),
		'siliconMine' => array(
			'base' => '$x',
		),
		'xenonMine' => array(
			'base' => '$x',
			'ironMine' => 1,
			'siliconMine' => 1,
		),
		'hydrazineMine' => array(
			'base' => '$x',
			'ironMine' => 1,
			'siliconMine' => 1,
		),
		'energyMine' => array(
			'base' => '$x',
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
		'iron' => 0.139, // 500 hour
		'silicon' => 0.111, // 400 hour
		'xenon' => 0.0555, // 200 hour
		'hydrazine' => 0.089, // 320 hour
		'energy' => 0.0555, // 200 hour
	);

	/**
	 * Structure build times
	 *
	 * @var array
	 */
	protected $structureBuildTime = array(
		'base' => '50 * pow($x, 2) * 1000000',
		'ironMine' => '(50 * pow($x, 3) + 50 * $x) * (1 / $y) * 1000000',
		'siliconMine' => '(40 * pow($x, 3) + 40 * $x) * (1 / $y) * 1000000',
		'xenonMine' => '(58 * pow($x, 3) + 58 * $x) * (1 / $y) * 1000000',
		'hydrazineMine' => '(55 * pow($x, 3) + 55 * $x) * (1 / $y) * 1000000',
		'energyMine' => '(60 * pow($x, 3) + 60 * $x) * (1 / $y) * 1000000',
	);

	/**
	 * Resource production formulas by mine level
	 * Micro units per micro second!
	 *
	 * @var array
	 */
	protected $mineProduction = array(
		'ironMine' => '(4 * pow($x, 2) + 20 * $x) / 60 / 60',
		'siliconMine' => '(4.5 * pow($x, 2) + 30 * $x) / 60 / 60',
		'xenonMine' => '(3.5 * pow($x, 2) + 12 * $x) / 60 / 60',
		'hydrazineMine' => '(5 * pow($x, 2) + 40 * $x) / 60 / 60',
		'energyMine' => '(4 * pow($x, 2) + 20 * $x) / 60 / 60',
	);

	/**
	 * Formulas for resource calculations of structure building level requirements
	 *
	 * @var array
	 */
	protected $structureResourceRequirements = array(
		'base' => array(
			'iron' => '(20 * pow($x, 2) - 40 * $x + 40) * 1000000',
			'silicon' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
			'xenon' => '(15 * pow($x, 2) - 30 * $x + 30) * 1000000',
		),
		'ironMine' => array(
			'iron' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
			'silicon' => '(30 * pow($x, 2) - 60 * $x + 60) * 1000000',
			'energy' => '(8 * pow($x, 2) - 16 * $x + 16) * 1000000',
		),
		'siliconMine' => array(
			'iron' => '(30 * pow($x, 2) - 60 * $x + 60) * 1000000',
			'silicon' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
			'energy' => '(8 * pow($x, 2) - 16 * $x + 16) * 1000000',
		),
		'xenonMine' => array(
			'iron' => '(3 * pow($x, 2) - 6 * $x + 6) * 1000000',
			'silicon' => '(40 * pow($x, 2) - 80 * $x + 80) * 1000000',
			'energy' => '(20 * pow($x, 2) - 40 * $x + 40) * 1000000',
		),
		'hydrazineMine' => array(
			'iron' => '(12 * pow($x, 2) - 24 * $x + 24) * 1000000',
			'silicon' => '(40 * pow($x, 2) - 80 * $x + 80) * 1000000',
			'xenon' => '(22 * pow($x, 2) - 44 * $x + 44) * 1000000',
			'energy' => '(25 * pow($x, 2) - 50 * $x + 50) * 1000000',
		),
		'energyMine' => array(
			'iron' => '(60 * pow($x, 2) - 120 * $x + 120) * 1000000',
			'silicon' => '(50 * pow($x, 2) - 100 * $x + 100) * 1000000',
			'xenon' => '(45 * pow($x, 2) - 90 * $x + 90) * 1000000',
			'hydrazine' => '(30 * pow($x, 2) - 60 * $x + 60) * 1000000',
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
	 * Whether a structure can be build on planet according to tech tree
	 *
	 * @param Planet $planet Planet to check
	 * @param string $structure The structure to build
	 * @param integer $level Structure level to build
	 * @throws Exception
	 * @return boolean TRUE if structure can be build
	 */
	public function isStructureAvailable(Planet $planet, $structure, $level) {
		$result = TRUE;
		$techTreeOfStructure = $this->getStructureTechTreeRequirements($structure, $level);
		foreach ($techTreeOfStructure as $requiredStructureName => $requiredLevel) {
			$currentRequiredStructureQueue = $this->countSpecificStructuresInBuildQueue($planet, $requiredStructureName);
			$currentRequiredStructurePropertyName = 'get' . ucfirst($requiredStructureName);
			$currentRequiredStructureLevel = $currentRequiredStructureQueue + $planet->$currentRequiredStructurePropertyName();
			if ($currentRequiredStructureLevel < $requiredLevel) {
				$result = FALSE;
			}
		}
		return $result;
	}

	/**
	 * Calculate tech requirements for a specific structure level
	 *
	 * @param string $structure Handled structure
	 * @param integer $level Structure level to build
	 * @return array
	 * @throws Exception
	 */
	public function getStructureTechTreeRequirements($structure, $level) {
		if (!isset($this->structureTechTree[$structure])) {
			throw new Exception(
				'Structure not in techtree', 1386595094
			);
		}
		$result = array();
		$techTreeOfStructure = $this->structureTechTree[$structure];
		foreach ($techTreeOfStructure as $structureName => $requiredLevelFormula) {
			$requiredLevel = $this->evaluateFormula($requiredLevelFormula, $level);
			$result[$structureName] = $requiredLevel;
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
		$currentBase = $planet->getBase();
		$numberOfBasesInQueue = $this->countSpecificStructuresInBuildQueue($planet, 'base');
		$baseLevel = $currentBase + $numberOfBasesInQueue;

		$structureGetter = 'get' . ucfirst($structureName);
		$currentStructure = $planet->$structureGetter();
		$numberOfStructuresInQueue = $this->countSpecificStructuresInBuildQueue($planet, $structureName);
		$structureLevel = $currentStructure + $numberOfStructuresInQueue + 1;

		return $this->getBuildTimeOfStructureByBaseLevel($structureName, $structureLevel, $baseLevel);
	}

	/**
	 * Calculate time to build a specific structure level depending on base level
	 *
	 * @param string $structureName Structure to build, eg. 'ironMine'
	 * @param integer $level Structure level to build
	 * @param integer $baseLevel Given Base level
	 * @return integer $time Time to build structure
	 * @throws Exception
	 */
	public function getBuildTimeOfStructureByBaseLevel($structureName, $level, $baseLevel) {
		if (!isset($this->structureBuildTime[$structureName])) {
			throw new Exception('Structure does not exist', 1387136244);
		}
		if (!is_integer($level) || $level < 0) {
			throw new Exception('Level must be positive integer', 1387136323);
		}
		if (!is_integer($baseLevel) || $baseLevel <= 0) {
			throw new Exception('Level must be positive integer', 1387136361);
		}
		return (int)round($this->evaluateFormula($this->structureBuildTime[$structureName], $level, $baseLevel));
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
		if (!isset($this->structureResourceRequirements[$structureName])) {
			throw new Exception(
				'Structure does not exist', 1386872359
			);
		}
		$units = 0;
		if (isset($this->structureResourceRequirements[$structureName][$resourceName])) {
			$formula = $this->structureResourceRequirements[$structureName][$resourceName];
			$units = (int)round($this->evaluateFormula($formula, $x));
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
	 * Returns array of planet resource production at given time
	 *
	 * @param Planet $planet
	 * @param $time
	 * @return array
	 * @throws Exception
	 */
	public function resourcesProducedUntil(Planet $planet, $time) {
		if ($time < $planet->getLastResourceUpdate()) {
			// @TODO: I got an exception here once after restarting worker and dispatcher, figure out how that could happen
			throw new Exception('Given time must not be lower than last resource update time', 1386523956);
		}
		$elapsedTime = $time - $planet->getLastResourceUpdate();

		$iron = (int)($this->resourceFullProductionByTimeLevel('iron', $elapsedTime, $planet->getIronMine(), $planet->getEnergyMine()));
		$silicon = (int)($this->resourceFullProductionByTimeLevel('silicon', $elapsedTime, $planet->getSiliconMine(), $planet->getEnergyMine()));
		$xenon = (int)($this->resourceFullProductionByTimeLevel('xenon', $elapsedTime, $planet->getXenonMine(), $planet->getEnergyMine()));

		$producedHydrazine = (int)($this->resourceFullProductionByTimeLevel('hydrazine', $elapsedTime, $planet->getHydrazineMine(), $planet->getEnergyMine()));
		$energy = (int)($this->resourceFullProductionByTimeLevel('energy', $elapsedTime, $planet->getEnergyMine(), $planet->getEnergyMine()));
		$currentHydrazine = $planet->getHydrazine();
		if ($producedHydrazine < 0 && ($currentHydrazine + $producedHydrazine) < 0) {
			// If energy production sucks up hydrazine and no hydrazine is left
			$hydrazine = -1 * $currentHydrazine; // sets "new" value to 0
			$energy = $currentHydrazine; // energy produced as much hydrazine was there plus current production
		} else {
			$hydrazine = $producedHydrazine;
		}

		return array(
			'iron' => $iron,
			'silicon' => $silicon,
			'xenon' => $xenon,
			'hydrazine' => $hydrazine,
			'energy' => $energy,
		);
	}

	/**
	 * Calculate resource production of mine in given time frame and mine level
	 *
	 * @param string $resource Resource to calculate
	 * @param integer $time Time frame in micro seconds
	 * @param integer $level Mine level
	 * @param integer $energyMineLevel Energy mine level - needed for hydrazine drain
	 * @throws Exception
	 * @return integer Production
	 */
	public function resourceFullProductionByTimeLevel($resource, $time, $level, $energyMineLevel) {
		// @TODO: This feels weird, maybe Planet should be given to this method to reduce number of arguments?!
		if ($resource === 'hydrazine') {
			$basicProduction = $this->resourceBasicProductionByTime($resource, $time);
			$mineHydrazineProduction = $this->resourceMineProductionByTimeAndMineLevel('hydrazine', $time, $level);
			$mineEnergyProduction = $this->resourceMineProductionByTimeAndMineLevel('energy', $time, $energyMineLevel);
			$mineProduction = $mineHydrazineProduction - $mineEnergyProduction;
		} else {
			$basicProduction = $this->resourceBasicProductionByTime($resource, $time);
			$mineProduction = $this->resourceMineProductionByTimeAndMineLevel($resource, $time, $level);
		}
		return (int)($basicProduction + $mineProduction);
	}

	/**
	 * Calculate resource base production by time
	 *
	 * @param string $resource Resource name, eg. 'iron'
	 * @param integer $time Time frame
	 * @throws Exception
	 * @return integer Production in micro units
	 */
	public function resourceBasicProductionByTime($resource, $time) {
		if (!is_integer($time) || $time < 0) {
			throw new Exception(
				'Time is not a positive integer', 1387118126
			);
		}
		if (!isset($this->basicProduction[$resource])) {
			throw new Exception(
				'Resource not found', 1387118154
			);
		}
		return (int)($time * $this->basicProduction[$resource]);
	}

	/**
	 * Calculate resource production of mine in given time frame and mine level
	 *
	 * @param string $resource Resource to calculate
	 * @param integer $time Timeframe in micro seconds
	 * @param integer $level Mine level
	 * @throws Exception
	 * @return integer Production
	 */
	public function resourceMineProductionByTimeAndMineLevel($resource, $time, $level) {
		if (!is_integer($time) || $time <= 0) {
			throw new Exception(
				'Time is not a positive integer', 1387110946
			);
		}
		if (!is_integer($level) || $level < 0) {
			throw new Exception(
				'Level is not a positive integer', 1387110998
			);
		}
		$mine = $resource . 'Mine';
		if (!isset($this->mineProduction[$mine])) {
			throw new Exception(
				'Mine formula not set', 1387111072
			);
		}
		$microUnitsPerMicroSecond = $this->evaluateFormula($this->mineProduction[$mine], $level);
		$production = (int)round($time * $microUnitsPerMicroSecond);
		return $production;
	}

	/**
	 * Evaluate given formula
	 *
	 * @param string $formula Formula to be evaluated
	 * @param integer $x X
	 * @param integer $y Y Second argument if given
	 * @throws Exception
	 * @return float Result
	 */
	protected function evaluateFormula($formula, $x, $y = NULL) {
		if (!is_integer($x) || $x < 0) {
			throw new Exception(
				'x is not an integer or smaller than zero', 1386872139
			);
		}
		if (!is_null($y) && (!is_integer($y) || $y < 0)) {
			throw new Exception(
				'y is not an integer or smaller than zero', 1387135993
			);
		}
		$result = 0;
		eval('$result = ' . $formula . ';');
		return $result;
	}
}