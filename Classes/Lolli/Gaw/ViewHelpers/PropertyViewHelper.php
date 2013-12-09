<?php
namespace Lolli\Gaw\ViewHelpers;

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
 * Get "dynamic" property from an object.
 *
 * This is not possible in fluid: {object.{aPropertyName}}
 *
 * = Examples =
 *
 * <code title="Defaults">
 * {planet -> gaw:property(propertyName: ironMine)}
 * </code>
 * <output>
 * 23
 * </output>
 */
class PropertyViewHelper extends AbstractViewHelper {

	/**
	 * Render the supplied byte count as a human readable string.
	 *
	 * @param string $propertyName The property to fetch, without "get"
	 * @param object $object The object to work on
	 * @throws Exception
	 * @return mixed Property value
	 */
	public function render($propertyName, $object = NULL) {
		if ($object === NULL) {
			$object = $this->renderChildren();
		}
		if (!is_object($object)) {
			throw new Exception('Not an object given', 1386591052);
		}

		return \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($object, $propertyName);
	}

}
