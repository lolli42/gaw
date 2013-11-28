<?php
namespace Lolli\Gaw\Service;

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