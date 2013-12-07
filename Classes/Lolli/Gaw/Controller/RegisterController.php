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
class RegisterController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Stub to create a single user
	 */
	public function indexAction() {
		die('deactivated');
		$identifier = 'lolli';
		$password = 'aPassword';
		$roles = array('Lolli.Gaw:Player');
		$account = $this->accountFactory->createAccountWithPassword($identifier, $password, $roles);
		$this->accountRepository->add($account);
		$this->persistenceManager->persistAll();
	}
}