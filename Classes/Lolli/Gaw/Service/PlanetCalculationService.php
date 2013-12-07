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

class PlanetCalculationService {

	/**
	 * @param Planet $planet
	 * @return integer seconds
	 */
	public function getBaseBuildTime(Planet $planet) {
		// 4 secs
		return (int)(4000000);
//		return (int)(1000000 * ($planet->getBase() + 1)); // 32 * 60
	}
}