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
use Lolli\Gaw\Domain\Model\Planet;

/**
 * Planet building controller
 *
 * @Flow\Scope("singleton")
 */
class PlanetBuildingController extends AbstractGameController {

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
		$data = array(
			'command' => 'updateResourcesOnPlanet',
			'tags' => array($this->selectedPlanet->getPlanetPositionString()),
			'galaxyNumber' => $this->selectedPlanet->getGalaxyNumber(),
			'systemNumber' => $this->selectedPlanet->getSystemNumber(),
			'planetNumber' => $this->selectedPlanet->getPlanetNumber(),
		);
		$result = $this->redisFacade->scheduleBlockingJob($data);
		$this->createFlashMessageFromWorkerResult($result);
		// Update planet data after some worker updated it
		$this->planetRepository->refresh($this->selectedPlanet);

		$planetCalculationService = new \Lolli\Gaw\Service\PlanetCalculationService();

		$this->view->assignMultiple(
			array(
				'realTime' => $this->redisFacade->getRealTimeNow(),
				'gameTime' => $this->redisFacade->getGameTimeNow(),
				'structureTechTree' => $planetCalculationService->getStructureTechTree(),
				'currentStructureBuildQueueLength' => $this->selectedPlanet->getStructureBuildQueue()->count(),
				'pointsByStructure' => $planetCalculationService->getPointsByStructure(),
			)
		);
	}

	/**
	 * Add a structure in planet structure queue
	 *
	 * @param Planet $planet
	 * @param string $structureName
	 * @throws Exception
	 */
	public function addStructureToBuildQueueAction(Planet $planet, $structureName) {
		if (is_null(\TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($planet, $structureName))) {
			throw new Exception('Structure not found', 1386597704);
		}
		$data = array(
			'command' => 'addPlanetStructureToBuildQueue',
			'structureName' => $structureName,
			'tags' => array($planet->getPlanetPositionString()),
			'galaxyNumber' => $planet->getGalaxyNumber(),
			'systemNumber' => $planet->getSystemNumber(),
			'planetNumber' => $planet->getPlanetNumber(),
		);
		$result = $this->redisFacade->scheduleBlockingJob($data);
		$this->createFlashMessageFromWorkerResult($result, 'Planet wird ausgebaut');
		$this->planetRepository->refresh($planet);
		$this->redirect('index');
	}
}