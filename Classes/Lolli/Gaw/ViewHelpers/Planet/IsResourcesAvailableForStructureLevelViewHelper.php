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
use Lolli\Gaw\Domain\Model\Planet;

/**
 * True if enough resources are available on planet for a specific structure level
 */
class IsResourcesAvailableForStructureLevelViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * TRUE if enough resources are available
	 *
	 * @param integer $structureName The structure to build
	 * @param integer $level The level to build
	 * @param Planet $planet The planet to check
	 * @throws Exception
	 * @return boolean TRUE if all resources are available
	 */
	public function render($structureName, $level, $planet = NULL) {
		if ($planet === NULL) {
			$planet = $this->renderChildren();
		}
		if (!($planet instanceof Planet)) {
			throw new Exception('Not a planet given', 1386882737);
		}
		$requiredResources = $this->planetCalculationService->getResourcesRequiredForStructureLevel($structureName, $level);
		return $this->planetCalculationService->isResourcesAvailable($planet, $requiredResources);
	}
}
