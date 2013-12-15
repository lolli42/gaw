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
 * Format microseconds in days/hours/minutes/seconds
 *
 * [!!!] Output format is currently hardcoded
 */
class TimeViewHelper extends AbstractViewHelper {

	/**
	 * Format microseconds
	 *
	 * @param integer $microSeconds Micro seconds
	 * @throws Exception
	 * @return string Formatted Time
	 */
	public function render($microSeconds = NULL) {
		if ($microSeconds === NULL) {
			$microSeconds = $this->renderChildren();
		}
		$microSeconds = (int)$microSeconds;
		if ($microSeconds < 0) {
			throw new Exception('Positive microseconds expected', 1387135092);
		}
		$seconds = round($microSeconds / 1000000);
		$days = floor($seconds / (60 * 60 * 24));
		$hours = sprintf('%1$02d', floor(($seconds / (60 * 60)) % 24));
		$minutes = sprintf('%1$02d', floor(($seconds / 60) % 60));
		$seconds = sprintf('%1$02d', floor($seconds % 60));
		if ($days >= 1) {
			return $days . ' Tage ' . $hours . ':' . $minutes . ':' . $seconds;
		} else {
			return $hours . ':' . $minutes . ':' . $seconds;
		}
	}
}
