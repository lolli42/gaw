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

/**
 * Player controller
 *
 * @Flow\Scope("singleton")
 */
class PlayerController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlayerRepository
	 */
	protected $playerRepository;

	/**
	 * @param \Lolli\Gaw\Domain\Model\Player $player
	 */
	public function selectPlanetAction(\Lolli\Gaw\Domain\Model\Player $player) {
		$this->playerRepository->update($player);
		$this->redirect('index', 'planetBuilding');
	}
}