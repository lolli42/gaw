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
	 * @var \Lolli\Gaw\Service\PlanetCalculationService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $subject;

	/**
	 * Set up
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock('Lolli\Gaw\Service\PlanetCalculationService', array('dummy'), array(), '', FALSE);
	}

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\Service\Exception
	 */
	public function resourcesAtTimeThrowsExceptionIfTimeIsLowerThanLastResourceUpdateTime() {
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setLastResourceUpdate(100);
		$this->subject->resourcesProducedUntil($planet, 50);
	}

	/**
	 * @test
	 */
	public function resourcesAtTimeCalculatesIronCorrectlyWithIronMineZero() {
		$planet = new \Lolli\Gaw\Domain\Model\Planet();
		$planet->setIron(0);
		$planet->setLastResourceUpdate(0);
		$planet->setIronMine(0);
		$oneHour = 60 * 60 * 1000000; // An hour in microseconds
		$expectedIron = 180000000; // micro units, 180 units
		$result = $this->subject->resourcesProducedUntil($planet, $oneHour);
		$this->assertEquals($expectedIron, $result['iron']);
	}

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\Service\Exception
	 */
	public function getResourcesRequiredForStructureLevelThrowsExceptionIfLevelIsNegative() {
		$this->subject->getResourcesRequiredForStructureLevel('base', -1);
	}

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\Service\Exception
	 */
	public function getResourcesRequiredForStructureLevelThrowsExceptionIfLevelIsNotOfTypeInteger() {
		$this->subject->getResourcesRequiredForStructureLevel('base', array());
	}

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\Service\Exception
	 */
	public function getResourcesRequiredForStructureLevelThrowsExceptionIfStructureDoesNotExist() {
		$this->subject->getResourcesRequiredForStructureLevel('foo', 1);
	}

	/**
	 * Data provider
	 */
	public function getResourcesRequiredForStructureLevelDataProvider() {
		return array(
			'base 0' => array(
				array(
					'base' => array(
						'iron' => '55 * pow($x, 2) + 55',
					),
				),
				'base',
				0,
				array(
					'iron' => 55,
				),
			),
			'base 1' => array(
				array(
					'base' => array(
						'iron' => '55 * pow($x, 2) + 55',
					),
				),
				'base',
				1,
				array(
					'iron' => 110,
				),
			),
			'base 35' => array(
				array(
					'base' => array(
						'iron' => '55 * pow($x, 2) + 55',
					),
				),
				'base',
				35,
				array(
					'iron' => 67430,
				),
			),
			'two resources' => array(
				array(
					'base' => array(
						'iron' => '55 * pow($x, 2) + 55',
						'silicon' => '40 * pow($x, 2) + 40',
					),
				),
				'base',
				2,
				array(
					'iron' => 275,
					'silicon' => 200,
				),
			),
		);
	}

	/**
	 * @test
	 * @param array $requirements Function array
	 * @param string $structureName Structure handled (eg. "base")
	 * @param integer $level Current structure level
	 * @param integer $expected Expected resource requirement
	 * @dataProvider getResourcesRequiredForStructureLevelDataProvider
	 */
	public function getResourcesRequiredForStructureLevelCalculatesCorrectly($requirements, $structureName, $level, $expected) {
		$this->subject->_set('structureResourceRequirements', $requirements);
		$result = $this->subject->getResourcesRequiredForStructureLevel($structureName, $level);
		$this->assertSame($expected, $result);
	}
}
?>