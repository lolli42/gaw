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
 * Whether a structure can be build according to tech tree.
 * Usually used within f:if
 */
class isStructureAvailableViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * TRUE if given structure can be build according to tech tree
	 *
	 * @param integer $structureName The structure to build
	 * @param Planet $planet The planet to check
	 * @throws Exception
	 * @return boolean TRUE if structure is available
	 */
	public function render($structureName, $planet = NULL) {
		if ($planet === NULL) {
			$planet = $this->renderChildren();
		}
		if (!($planet instanceof Planet)) {
			throw new Exception('Not a planet given', 1386596030);
		}
		return $this->planetCalculationService->isStructureAvailable($planet, $structureName);
	}
}
