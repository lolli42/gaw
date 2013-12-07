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
 * Login controller
 *
 * @Flow\Scope("singleton")
 */
class AuthenticationController extends \TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Show login form
	 */
	public function indexAction() {
		if ($this->authenticationManager->isAuthenticated()) {
			$redirectSettings = $this->settings['Controller']['Login']['Redirect']['login'];
			$this->redirect($redirectSettings['actionName'], $redirectSettings['controllerName']);
		}
	}

	/**
	 * Log out all tokens
	 */
	public function logoutAction() {
		parent::logoutAction();
		$redirectSettings = $this->settings['Controller']['Login']['Redirect']['logout'];
		$this->redirect($redirectSettings['actionName'], $redirectSettings['controllerName']);
	}

	/**
	 * Is called if authentication was successful
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		$redirectSettings = $this->settings['Controller']['Login']['Redirect']['login'];
		$this->redirect($redirectSettings['actionName'], $redirectSettings['controllerName']);
	}
}