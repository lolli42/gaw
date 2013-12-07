<?php
namespace Lolli\Gaw\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class GalaxyController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlanetRepository
	 */
	protected $planetRepository;

	/**
	 * Show planets in system galaxy
	 *
	 * @param int $galaxy
	 * @param int $system
	 * @throws Exception\ArgumentException
	 */
	public function indexAction($galaxy = 1, $system = 1) {
		if ($galaxy < 1 || $galaxy > 100) {
			throw new Exception\ArgumentException('Galaxy out of bounds', 1386407221);
		}
		if ($system < 1 || $system > 300) {
			throw new Exception\ArgumentException('System out of bounds', 1386407222);
		}
		$orderedPlanetArray = array_fill(1, 12, NULL);
		$planets = $this->planetRepository->findByGalaxyAndSystem($galaxy, $system);
		foreach ($planets as $planet) {
			/** @var $planet \Lolli\Gaw\Domain\Model\Planet */
			$orderedPlanetArray[$planet->getPlanetNumber()] = $planet;
		}
		$nextSystem = NULL;
		if ($system < 300) {
			$nextSystem = $system + 1;
		}
		$previousSystem = NULL;
		if ($system > 1) {
			$previousSystem = $system - 1;
		}
		$nextGalaxy = NULL;
		if ($galaxy < 100) {
			$nextGalaxy = $galaxy + 1;
		}
		$previousGalaxy = NULL;
		if ($galaxy > 1) {
			$previousGalaxy = $galaxy - 1;
		}
		$this->view->assignMultiple(
			array(
				'galaxy' => $galaxy,
				'nextGalaxy' => $nextGalaxy,
				'previousGalaxy' => $previousGalaxy,
				'system' => $system,
				'nextSystem' => $nextSystem,
				'previousSystem' => $previousSystem,
				'planets' => $orderedPlanetArray
			)
		);
	}
}