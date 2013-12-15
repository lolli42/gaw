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
 * Resource production of planet
 */
class ResourceFullProductionByResourceLevelTimeViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * Resource production
	 *
	 * @param string $resource Identifier, eg. "iron"
	 * @param integer $level Mine level
	 * @param integer $time Time in microseconds
	 * @throws Exception
	 * @return integer Micro units
	 */
	public function render($resource, $level, $time) {
		$time = (int)$time;
		$level = (int)$level;
		return $this->planetCalculationService->resourceFullProductionByTimeLevel($resource, $time, $level);
	}
}
