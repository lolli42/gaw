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
use TYPO3\Flow\Mvc\Controller\ActionController;
use Lolli\Gaw\Domain\Model\Planet;

class PlanetController extends ActionController {

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
		$this->view->assign('planets', $this->planetRepository->findAll());
	}

	/**
	 * @param \Lolli\Gaw\Domain\Model\Planet $planet
	 * @return void
	 */
	public function showAction(Planet $planet) {
		$this->view->assign('planet', $planet);
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
		if ($success) {
			$this->addFlashMessage('build the planet.');
		} else {
			$this->addFlashMessage('dispatcher down or job took too long or worker gave no feedback');
		}
		// difference between redirect and forward is here that redirect maps new and
		// thus fetches updated planet from persistence
		$this->redirect('show', NULL, NULL, array('planet' => $planet));
	}
}