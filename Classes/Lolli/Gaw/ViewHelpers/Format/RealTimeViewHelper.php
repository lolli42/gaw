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
 * Calculate "expected" real time from game time if game does not stop meanwhile.
 *
 * [!!!] Output format is currently hardcoded
 * [!!!] Timezone is currently hardcoded to Europe/Berlin
 */
class RealTimeViewHelper extends AbstractViewHelper {

	/**
	 * Render real time for given game time
	 *
	 * @param integer $gameTime Game time "now" in microseconds
	 * @param integer $realTime Real time "now" in microseconds
	 * @param integer $subjectTime Time to work on, defaults to game time "now"
	 * @return string Formatted real time
	 */
	public function render($gameTime, $realTime, $subjectTime = NULL) {
		if ($subjectTime === NULL) {
			$subjectTime = $this->renderChildren();
		}
		if ($subjectTime === NULL) {
			$subjectTime = $gameTime;
		}

		$offset = $subjectTime - $gameTime;
		$realTimeSubject = round(($realTime + $offset) / 1000000);

		$date = new \DateTime('@' . $realTimeSubject);
		$timezone = new \DateTimeZone('Europe/Berlin');
		$date->setTimezone($timezone);
		return $date->format('d.m.Y H:i:s');
	}
}
