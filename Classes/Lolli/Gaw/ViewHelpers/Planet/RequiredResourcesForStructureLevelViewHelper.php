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
 * Array with required resources for structure level
 */
class RequiredResourcesForStructureLevelViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * TRUE if given structure can be build according to tech tree
	 *
	 * @param string $structureName The structure name, eg 'ironMine'
	 * @param integer $level Level to calculate
	 * @throws Exception
	 * @return array Required resources, key is name, value is micro units
	 */
	public function render($structureName, $level) {
		return $this->planetCalculationService->getResourcesRequiredForStructureLevel($structureName, $level);
	}
}
