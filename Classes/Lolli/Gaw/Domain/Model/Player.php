<?php
namespace Lolli\Gaw\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A player
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Player extends \TYPO3\Party\Domain\Model\AbstractParty {

	/**
	 * @var string
	 */
	protected $gameNick;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\Lolli\Gaw\Domain\Model\Planet>
	 * @ORM\OneToMany(mappedBy="player")
	 */
	protected $planets;

	/**
	 * Set game nick
	 *
	 * @param string $gameNick
	 */
	public function setGameNick($gameNick) {
		$this->gameNick = $gameNick;
	}

	/**
	 * Get game nick
	 *
	 * @return string
	 */
	public function getGameNick() {
		return $this->gameNick;
	}

	/**
	 * Constructs this Player
	 */
	public function __construct() {
		parent::__construct();
		$this->planets = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Get planets of player
	 *
	 * @return \Doctrine\Common\Collections\Collection<\Lolli\Gaw\Domain\Model\Planet>
	 */
	public function getPlanets() {
		return $this->planets;
	}

	/**
	 * Add planet to player
	 *
	 * @param \Lolli\Gaw\Domain\Model\Planet $planet
	 */
	public function addPlanet(\Lolli\Gaw\Domain\Model\Planet $planet) {
		$this->planets->add($planet);
		$planet->setPlayer($this);
	}
}
