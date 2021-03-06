<?php
namespace Lolli\Gaw\ViewHelpers\Planet;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Annotations as Flow;

/**
 * Show how much resource of a resource type is required for a structure level
 */
class BuildTimeOfStructureByBaseLevelViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * Time in microseconds to build a structure level
	 *
	 * @param string $structureName The structure name, eg 'ironMine'
	 * @param integer $level Level to calculate
	 * @param string $baseLevel Level of base building
	 * @throws Exception
	 * @return integer Time in micro seconds
	 */
	public function render($structureName, $level, $baseLevel) {
		return $this->planetCalculationService->getBuildTimeOfStructureByBaseLevel($structureName, (int)$level, (int)$baseLevel);
	}
}
