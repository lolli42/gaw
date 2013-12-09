<?php
namespace Lolli\Gaw\Controller\Game;

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

/**
 * Abstract game controller implement common game controller stuff
 *
 * @Flow\Scope("singleton")
 */
abstract class AbstractGameController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \Lolli\Gaw\Domain\Model\Player
	 */
	protected $player;

	/**
	 * @var \Lolli\Gaw\Domain\Model\Planet
	 */
	protected $selectedPlanet;

	/**
	 * Set up common stuff
	 */
	protected function initializeAction() {
		/** @var \Lolli\Gaw\Domain\Model\Player $player */
		$player = $this->securityContext->getPartyByType('Lolli\Gaw\Domain\Model\Player');
		$this->player = $player;
		$this->selectedPlanet = $player->getSelectedPlanet();
	}

	/**
	 * Assign common objects to view
	 */
	protected function initializeView() {
		$this->view->assignMultiple(
			array(
				'player' => $this->player,
				'selectedPlanet' => $this->player->getSelectedPlanet(),
			)
		);
	}
}