<?php
namespace Lolli\Gaw\ViewHelpers\Planet;

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
use TYPO3\Flow\Annotations as Flow;
use Lolli\Gaw\Domain\Model\Planet;

/**
 * Get next structure level that is not queued yet.
 * Example: base is 4, and 2 other bases are queued, it will return 7
 */
class StructureLevelWithQueueViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * Next level
	 *
	 * @param string $structureName The structure to find next level for
	 * @param Planet $planet The planet to check
	 * @param integer $offset Additional offset (default +1)
	 * @throws Exception
	 * @return boolean TRUE if structure is available
	 */
	public function render($structureName, $planet = NULL, $offset = 1) {
		if ($planet === NULL) {
			$planet = $this->renderChildren();
		}
		if (!($planet instanceof Planet)) {
			throw new Exception('Not a planet given', 1386880868);
		}
		$method = 'get' . ucfirst($structureName);
		// @TODO: Better with a real whitelist? This could access all getters
		if (!method_exists($planet, $method)) {
			throw new Exception('Structure does not exist', 1386879358);
		}
		$offset = (int)$offset;
		$currentLevel = $planet->$method();
		$inQueue = $this->planetCalculationService->countSpecificStructuresInBuildQueue($planet, $structureName);
		return $offset + $currentLevel + $inQueue;
	}
}
