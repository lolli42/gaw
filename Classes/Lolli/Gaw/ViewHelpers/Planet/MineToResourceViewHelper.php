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
 * Get resource name from a mine name
 */
class MineToResourceViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * Resource that is produced at this mine
	 *
	 * @param string $mineName
	 * @return string Resource name
	 * @throws Exception
	 */
	public function render($mineName) {
		if (substr($mineName, -4) !== 'Mine') {
			throw new Exception('No such mine', 1387132617);
		}
		// @TODO: Sanitize resource name exists
		return substr($mineName, 0, -4);
	}
}
