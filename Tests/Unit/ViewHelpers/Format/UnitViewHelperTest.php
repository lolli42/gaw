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
class UnitViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \Lolli\Gaw\ViewHelpers\Format\UnitViewHelper
	 */
	protected $viewHelper;

	/**
	 * Set up
	 */
	public function setUp() {
		$this->viewHelper = $this->getMock('Lolli\Gaw\ViewHelpers\Format\UnitViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 * @expectedException \Lolli\Gaw\ViewHelpers\Format\Exception
	 */
	public function renderThrowsExceptionIfUnitIsNotAnInteger() {
		$this->viewHelper->render(array());
	}

	public function valueDataProvider() {
		return array(
			// valid values
			array(
				'value' => 1000000,
				'decimals' => 0,
				'decimalSeparator' => ',',
				'thousandsSeparator' => '.',
				'expected' => '1',
			),
			array(
				'value' => 1200000,
				'decimals' => 1,
				'decimalSeparator' => ',',
				'thousandsSeparator' => '.',
				'expected' => '1,2',
			),
			array(
				'value' => 12345000000,
				'decimals' => 0,
				'decimalSeparator' => ',',
				'thousandsSeparator' => '.',
				'expected' => '12.345',
			),
			array(
				'value' => 1234567123456,
				'decimals' => 2,
				'decimalSeparator' => ',',
				'thousandsSeparator' => '.',
				'expected' => '1.234.567,12',
			),
		);
	}

	/**
	 * @param $value
	 * @param $decimals
	 * @param $decimalSeparator
	 * @param $thousandsSeparator
	 * @param $expected
	 * @test
	 * @dataProvider valueDataProvider
	 */
	public function renderCorrectlyConvertsAValue($value, $decimals, $decimalSeparator, $thousandsSeparator, $expected) {
		$actualResult = $this->viewHelper->render($value, $decimals, $decimalSeparator, $thousandsSeparator);
		$this->assertEquals($expected, $actualResult);
	}
}