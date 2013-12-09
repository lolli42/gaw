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
	 * @param Planet $planet
	 * @return integer seconds
	 */
	public function getBaseBuildTime(Planet $planet) {
		// 4 secs
		return (int)(4000000);
//		return (int)(1000000 * ($planet->getBase() + 1)); // 32 * 60
	}

	/**
	 * Calculate
	 *
	 * @param Planet $planet
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