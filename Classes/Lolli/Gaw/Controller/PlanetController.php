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
 * Planet controller
 *
 * @Flow\Scope("singleton")
 */
class PlanetController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @return void
	 */
	public function indexAction() {
		/** @var \Lolli\Gaw\Domain\Model\Player $player */
		$player = $this->securityContext->getPartyByType('Lolli\Gaw\Domain\Model\Player');

		$this->view->assignMultiple(
			array(
				'player' => $player,
				'planets' => $this->planetRepository->findAll(),
			)
		);
	}

	/**
	 * @param \Lolli\Gaw\Domain\Model\Planet $planet
	 * @return void
	 */
	public function showAction(Planet $planet) {
		/** @var \Lolli\Gaw\Domain\Model\Player $player */
		$player = $this->securityContext->getPartyByType('Lolli\Gaw\Domain\Model\Player');

		$this->view->assignMultiple(
			array(
				'player' => $player,
				'planet' => $planet,
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
		$success = $this->redisFacade->scheduleBlockingJob($data);
		$this->addFlashMessage('build the planet, ready time' . $success['readyTime']);

		// difference between redirect and forward is here that redirect maps new and
		// thus fetches updated planet from persistence
		$this->redirect('show', NULL, NULL, array('planet' => $planet));
	}
}