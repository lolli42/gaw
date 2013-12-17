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

	/**
	 * @test
	 */
	public function resourcesProducedUntilCalculatesHydrazineAndEnergyCorrectlyIfHydrazineIsHigherThanEnergy() {
		/** @var \Lolli\Gaw\Service\PlanetCalculationService|\PHPUnit_Framework_MockObject_MockObject $subject */
		$subject = $this->getAccessibleMock('Lolli\Gaw\Service\PlanetCalculationService', array('resourceFullProductionByTimeLevel'), array(), '', FALSE);

		/** @var \Lolli\Gaw\Domain\Model\Planet|\PHPUnit_Framework_MockObject_MockObject $planetMock */
		$planetMock = $this->getMock('\Lolli\Gaw\Domain\Model\Planet', array(), array(), '', FALSE);
		$planetMock->expects($this->any())->method('getLastResourceUpdate')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getIronMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getSiliconMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getXenonMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getHydrazineMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getEnergyMine')->will($this->returnValue(0));

		$subject->expects($this->at(0))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(1))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(2))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));

		// 12 hydrazine is there, 10 hy is produced with nrg drain, 3 nrg is produced -> 10 hy and 3 nrg should be added
		$planetMock->expects($this->once())->method('getHydrazine')->will($this->returnValue(12));
		$subject->expects($this->at(3))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(10));
		$subject->expects($this->at(4))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(3));
		$result = $subject->resourcesProducedUntil($planetMock, 60*60*1000000);
		$this->assertSame(10, $result['hydrazine']);
		$this->assertSame(3, $result['energy']);
	}

	/**
	 * @test
	 */
	public function resourcesProducedUntilCalculatesHydrazineAndEnergyCorrectlyIfHydrazineIsLowerThanEnergy() {
		/** @var \Lolli\Gaw\Service\PlanetCalculationService|\PHPUnit_Framework_MockObject_MockObject $subject */
		$subject = $this->getAccessibleMock('Lolli\Gaw\Service\PlanetCalculationService', array('resourceFullProductionByTimeLevel'), array(), '', FALSE);

		/** @var \Lolli\Gaw\Domain\Model\Planet|\PHPUnit_Framework_MockObject_MockObject $planetMock */
		$planetMock = $this->getMock('\Lolli\Gaw\Domain\Model\Planet', array(), array(), '', FALSE);
		$planetMock->expects($this->any())->method('getLastResourceUpdate')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getIronMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getSiliconMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getXenonMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getHydrazineMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getEnergyMine')->will($this->returnValue(0));

		$subject->expects($this->at(0))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(1))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(2))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));

		// 12 hydrazine is there, -3 hy is produced, 13 nrg is produced -> 3 hy should be removed, 13 nrg added
		$planetMock->expects($this->once())->method('getHydrazine')->will($this->returnValue(12));
		$subject->expects($this->at(3))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(-3));
		$subject->expects($this->at(4))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(13));
		$result = $subject->resourcesProducedUntil($planetMock, 60*60*1000000);
		$this->assertSame(-3, $result['hydrazine']);
		$this->assertSame(13, $result['energy']);
	}

	/**
	 * @test
	 */
	public function resourcesProducedUntilCalculatesHydrazineAndEnergyCorrectlyIfHydrazineIsLowerThanEnergyAndNotEnoughHydrazineIsLeft() {
		/** @var \Lolli\Gaw\Service\PlanetCalculationService|\PHPUnit_Framework_MockObject_MockObject $subject */
		$subject = $this->getAccessibleMock('Lolli\Gaw\Service\PlanetCalculationService', array('resourceFullProductionByTimeLevel'), array(), '', FALSE);

		/** @var \Lolli\Gaw\Domain\Model\Planet|\PHPUnit_Framework_MockObject_MockObject $planetMock */
		$planetMock = $this->getMock('\Lolli\Gaw\Domain\Model\Planet', array(), array(), '', FALSE);
		$planetMock->expects($this->any())->method('getLastResourceUpdate')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getIronMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getSiliconMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getXenonMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getHydrazineMine')->will($this->returnValue(0));
		$planetMock->expects($this->any())->method('getEnergyMine')->will($this->returnValue(0));

		$subject->expects($this->at(0))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(1))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));
		$subject->expects($this->at(2))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(0));

		// 11 hydrazine is there, -13 hy is produced with nrg drain, 27 nrg base is produced -> 11 should be removed, 11 nrg produced
		$planetMock->expects($this->once())->method('getHydrazine')->will($this->returnValue(11));
		$subject->expects($this->at(3))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(-13));
		$subject->expects($this->at(4))->method('resourceFullProductionByTimeLevel')->will($this->returnValue(27));
		$result = $subject->resourcesProducedUntil($planetMock, 60*60*1000000);
		$this->assertSame(-11, $result['hydrazine']);
		$this->assertSame(11, $result['energy']);
	}

	/**
	 * @test
	 */
	public function resourceProductionByTimeAndMineLevelCalculatesIronProductionIronMineZero() {
		$microSeconds = 1;
		$level = 0;
		$expected = 0;
		$result = $this->subject->resourceMineProductionByTimeAndMineLevel('iron', $microSeconds, $level);
		$this->assertSame($expected, $result);
	}

	/**
	 * @test
	 */
	public function resourceProductionByTimeAndMineLevelCalculatesIronProductionIronMineTen() {
		$microSeconds = 1000000 * 60 * 60; // 1 hour
		$level = 10;
		$expected = 600000000; // 600 per hour
		$result = $this->subject->resourceMineProductionByTimeAndMineLevel('iron', $microSeconds, $level);
		$this->assertSame($expected, $result);
	}
}
?>