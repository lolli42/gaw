<?php
namespace Lolli\Gaw\ViewHelpers\Math;

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
 * Multiply two values
 */
class MultiplyViewHelper extends AbstractViewHelper {

	/**
	 * Render real time for given game time
	 *
	 * @param integer $factorOne
	 * @param integer $factorTwo
	 * @return integer product
	 */
	public function render($factorOne, $factorTwo) {
		return $factorOne * $factorTwo;
	}
}
