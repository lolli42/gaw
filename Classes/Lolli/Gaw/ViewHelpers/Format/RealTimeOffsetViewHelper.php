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
 * Real time plus micro time
 *
 * [!!!] Output format is currently hardcoded
 * [!!!] Timezone is currently hardcoded to Europe/Berlin
 */
class RealTimeOffsetViewHelper extends AbstractViewHelper {

	/**
	 * Render real time for given time offset
	 *
	 * @param integer $realTime Real time "now" in microseconds
	 * @param integer $offset Offset in microseconds
	 * @return string Formatted real time
	 */
	public function render($realTime, $offset) {
		// Round to seconds
		$realTime = round($realTime / 1000000);
		$offset = round($offset / 1000000);
		$realTimeSubject = $realTime + $offset;
		$date = new \DateTime('@' . $realTimeSubject);
		$timezone = new \DateTimeZone('Europe/Berlin');
		$date->setTimezone($timezone);
		return $date->format('d.m.Y H:i:s');
	}
}
