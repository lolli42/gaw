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
 * Register a player
 *
 * @Flow\Scope("singleton")
 */
class RegistrationController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlayerRepository
	 */
	protected $playerRepository;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Domain\Repository\PlanetRepository
	 */
	protected $planetRepository;

	/**
	 * @Flow\Inject
	 * @var \Lolli\Gaw\Redis\ClientFacade
	 */
	protected $redisFacade;

	/**
	 * Show register form
	 */
	public function indexAction() {
	}

	/**
	 * Register a player
	 *
	 * @param string $loginName
	 * @param string $gameNick
	 * @param string $password
	 */
	public function registerAction($loginName, $gameNick, $password) {
		$existingAccount = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($loginName, 'DefaultProvider');
		if ($existingAccount instanceof \TYPO3\Flow\Security\Account) {
			$this->addFlashMessage("Login Name $loginName ist belegt");
			$this->redirect('index');
		}

		$existingPlayer = $this->playerRepository->findOneByGameNick($gameNick);
		if ($existingPlayer instanceof \Lolli\Gaw\Domain\Model\Player) {
			$this->addFlashMessage("Game Nick $gameNick ist belegt");
			$this->redirect('index');
		}

		$player = new \Lolli\Gaw\Domain\Model\Player();
		$player->setGameNick($gameNick);
		$this->playerRepository->add($player);

		$roles = array('Lolli.Gaw:Player');
		$account = $this->accountFactory->createAccountWithPassword($loginName, $password, $roles);
		$this->accountRepository->add($account);
		$player->addAccount($account);

		$data = array(
			'command' => 'createRandomPlanet',
			'tags' => array('planet'),
		);
		$result = $this->redisFacade->scheduleBlockingJob($data);
		$planet = $this->planetRepository->findOneByPosition($result['galaxyNumber'], $result['systemNumber'], $result['planetNumber']);
		$player->addPlanet($planet);
		$this->planetRepository->update($planet);

		$this->addFlashMessage("Registrierung erfolgreich, jetzt einloggen mit login Name $loginName");
		$this->redirect('index', 'Authentication');
	}
}