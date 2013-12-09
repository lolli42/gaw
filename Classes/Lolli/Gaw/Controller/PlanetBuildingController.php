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
use Lolli\Gaw\Domain\Model\Planet;

/**
 * Planet building controller
 *
 * @Flow\Scope("singleton")
 */
class PlanetBuildingController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Redis\ClientFacade
	 */
	protected $redisFacade;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlanetRepository
	 */
	protected $planetRepository;

	/**
	 * Show planet buildings
	 */
	public function indexAction() {
		/** @var \Lolli\Gaw\Domain\Model\Player $player */
		$player = $this->securityContext->getPartyByType('Lolli\Gaw\Domain\Model\Player');
		$planet = $player->getSelectedPlanet();
		$data = array(
			'command' => 'updateResourcesOnPlanet',
			'tags' => array($planet->getPlanetPositionString()),
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		$this->redisFacade->scheduleBlockingJob($data);
		// Update planet data after some worker updated it
		$this->planetRepository->refresh($planet);

		$planetCalculationService = new \Lolli\Gaw\Service\PlanetCalculationService();

		$this->view->assignMultiple(
			array(
				'player' => $player,
				'selectedPlanet' => $planet,
				'structureTechTree' => $planetCalculationService->getStructureTechTree(),
			)
		);
	}

	public function buildBaseAction(Planet $planet) {
		$data = array(
			'command' => 'beginBuildBase',
			'tags' => array($planet->getPlanetPositionString()),
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		// @TODO: Handle result
		$success = $this->redisFacade->scheduleBlockingJob($data);
		$this->addFlashMessage('Planet wird ausgebaut');
		$this->planetRepository->refresh($planet);
		$this->redirect('index');
	}
}