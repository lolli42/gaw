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
 * Used in tool tip to show tech requirements of a specific structure level
 */
class StructureTechRequirementViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\Flow\I18n\Translator
	 * @FLOW\Inject
	 */
	protected $translator;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Service\PlanetCalculationService
	 */
	protected $planetCalculationService;

	/**
	 * Tool tip for structure tech requirement
	 *
	 * @param integer $structureName The structure to build
	 * @param integer $structureLevel The level to build
	 * @throws Exception
	 * @return string
	 */
	public function render($structureName, $structureLevel) {
		$structureLevel = (int)$structureLevel;
		$techTree = $this->planetCalculationService->getStructureTechTreeRequirements($structureName, $structureLevel);
		$result = array();
		foreach ($techTree as $structureName => $requiredLevel) {
			$id = 'planet.' . $structureName;
			$translation = $this->translator->translateById($id, array(), NULL, NULL, 'Main', 'Lolli.Gaw');
			$result[] = $translation . ' ' . $requiredLevel;
		}
		return implode(', ', $result);
	}
}
