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

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A structure in a planets build queue
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class PlanetStructureBuildQueueItem {

	/**
	 * Link to planet this entity belongs to
	 *
	 * @var \Lolli\Gaw\Domain\Model\Planet
	 * @ORM\ManyToOne(inversedBy="structureBuildQueue")
	 * @Flow\Lazy
	 */
	protected $planet;

	/**
	 * Name of structure, eg. "ironMine"
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Game ready time in microseconds
	 *
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $readyTime = 0;

	/**
	 * Get connected planet
	 *
	 * @return Planet
	 */
	public function getPlanet() {
		return $this->planet;
	}

	/**
	 * Set connected planet
	 *
	 * @param Planet $planet Planet this structure belongs to
	 */
	public function setPlanet(Planet $planet) {
		$this->planet = $planet;
	}

	/**
	 * Set structure name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get structure name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set ready time
	 *
	 * @param int $readyTime
	 */
	public function setReadyTime($readyTime) {
		$this->readyTime = $readyTime;
	}

	/**
	 * Get ready time
	 *
	 * @return int
	 */
	public function getReadyTime() {
		return (int)$this->readyTime;
	}
}
