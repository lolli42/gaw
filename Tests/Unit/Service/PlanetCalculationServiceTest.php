<?php
namespace Lolli\Gaw\Tests\Unit\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Test case
 */
class PlanetCalculationServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\Service\Exception
	 */
	public function resourcesAtTimeThrowsExceptionIfTimeIsLowerThanLastResourceUpdateTime() {
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setLastResourceUpdate(100);
		$planetCalculationService = new \Lolli\Gaw\Service\PlanetCalculationService();
		$planetCalculationService->resourcesAtTime($planet, 50);
	}

	/**
	 * @test
	 */
	public function resourcesAtTimeCalculatesIronCorrectlyWithIronMineZero() {
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setIron(0);
		$planet->setLastResourceUpdate(0);
//		$planet->setIronMine(0);
		$oneHour = 60 * 60 * 1000000; // An hour in microseconds
		$expectedIron = 180000000; // micro units, 180 units
		$planetCalculationService = new \Lolli\Gaw\Service\PlanetCalculationService();
		$result = $planetCalculationService->resourcesAtTime($planet, $oneHour);
		$this->assertEquals($expectedIron, $result['iron']);
	}

	/**
	 * @test
	 */
	public function resourcesAtTimeCalculatesIronCorrectlyWithExistingIron() {
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setIron(123000000);
		$planet->setLastResourceUpdate(0);
//		$planet->setIronMine(0);
		$oneHour = 60 * 60 * 1000000; // An hour in microseconds
		$expectedIron = 303000000;
		$planetCalculationService = new \Lolli\Gaw\Service\PlanetCalculationService();
		$result = $planetCalculationService->resourcesAtTime($planet, $oneHour);
		$this->assertEquals($expectedIron, $result['iron']);
	}
}
?>