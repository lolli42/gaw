<?php
namespace Lolli\Gaw\ViewHelpers\Format;

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

/**
 * Formats units given in micro units (a million'st) into human-readable form
 *
 * = Examples =
 *
 * <code title="Defaults">
 * {planet.iron -> gaw:format.unit()}
 * </code>
 * <output>
 * 1.230
 * </output>
 *
 * <code title="Defaults">
 * {planet.iron -> f:format.bytes(decimals: 1, decimalSeparator: ',', thousandsSeparator: '.')}
 * </code>
 * <output>
 * 1.023,2
 * </output>
 */
class UnitViewHelper extends AbstractViewHelper {

	/**
	 * Render the supplied byte count as a human readable string.
	 *
	 * @param integer $value Unit in micro units
	 * @param integer $decimals The number of digits after the decimal point
	 * @param string $decimalSeparator The decimal point character
	 * @param string $thousandsSeparator The character for grouping the thousand digits
	 * @throws Exception
	 * @return string Formatted count in units
	 */
	public function render($value = NULL, $decimals = 0, $decimalSeparator = ',', $thousandsSeparator = '.') {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		if (!is_integer($value)) {
			throw new Exception('Unit is not an integer as expected', 1386531079);
		}

		// micro units -> units
		$units = $value / 1000000;

		return number_format($units, $decimals, $decimalSeparator, $thousandsSeparator);
	}

}
