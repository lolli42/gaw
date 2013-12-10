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
		// @TODO: Check if $player is an object, otherwise don't
		$this->selectedPlanet = $player->getSelectedPlanet();
	}

	/**
	 * Assign common objects to view
	 */
	protected function initializeView() {
		$this->view->assignMultiple(
			array(
				'player' => $this->player,
				// @TODO: $this->selected
				'selectedPlanet' => $this->player->getSelectedPlanet(),
			)
		);
	}

	/**
	 * A worker always gives an array with element success set to TRUE or FALSE.
	 * If success is true, the given success message is added as flash message if given
	 * If success is false, a flash message is created from given exception code
	 *
	 * @param array $result
	 * @param string|null $successMessage
	 * @throws Exception
	 */
	protected function createFlashMessageFromWorkerResult(array $result, $successMessage = NULL) {
		if (!isset($result['success'])) {
			throw new Exception('Success key not given in worker result', 1386686716);
		}
		if ($result['success'] === TRUE) {
			if (!is_null($successMessage)) {
				$this->addFlashMessage($successMessage);
			}
		} else {
			if (!isset($result['exceptionMessage']) || !isset($result['exceptionCode'])) {
				throw new Exception('Worker was not successful but did not add exception data', 1386686709);
			}
			/** @var \TYPO3\Flow\I18n\Translator $translator */
			$translator = $this->objectManager->get('TYPO3\Flow\I18n\Translator');
			$id = 'worker.exception.' . $result['exceptionCode'];
			$translation = $translator->translateById($id, array(), NULL, NULL, 'Main', 'Lolli.Gaw');
			if ($translation === $id) {
				// Fall back to exception message from worker if there is no translation
				$translation = $result['exceptionMessage'];
			}
			$this->addFlashMessage($translation, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
		}
	}
}