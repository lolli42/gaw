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
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Planet {

	const STRUCTURE_NONE = 0;
	const STRUCTURE_BASE = 1;

	/**
	 * @var \Lolli\Gaw\Domain\Model\Player
	 * @ORM\ManyToOne(inversedBy="planets")
	 * @Flow\Lazy
	 */
	protected $player;

	/**
	 * @var integer
	 */
	protected $galaxyNumber = 0;

	/**
	 * @var integer
	 */
	protected $systemNumber = 0;

	/**
	 * @var integer
	 */
	protected $planetNumber = 0;

	/**
	 * @var integer
	 */
	protected $structureInProgress = 0;

	/**
	 * @var integer
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $structureReadyTime = 0;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var integer
	 */
	protected $base = 1;

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $lastResourceUpdate = 0;

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $iron = 2000000000; // microunits -> 2000 units

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $silicon = 2000000000;

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $xenon = 2000000000;

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $hydrazine = 2000000000;

	/**
	 * @var int
	 * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
	 */
	protected $energy = 2000000000;

	/**
	 * Get corresponding player of planet
	 *
	 * @return \Lolli\Gaw\Domain\Model\Player
	 */
	public function getPlayer() {
		return $this->player;
	}

	/**
	 * Set corresponding player of planet
	 *
	 * @param \Lolli\Gaw\Domain\Model\Player $player
	 */
	public function setPlayer(\Lolli\Gaw\Domain\Model\Player $player) {
		$this->player = $player;
	}

	/**
	 * @param integer $galaxyNumber
	 */
	public function setGalaxyNumber($galaxyNumber) {
		$this->galaxyNumber = $galaxyNumber;
	}

	/**
	 * @return int
	 */
	public function getGalaxyNumber() {
		return (int)$this->galaxyNumber;
	}

	/**
	 * @param integer $systemNumber
	 */
	public function setSystemNumber($systemNumber) {
		$this->systemNumber = $systemNumber;
	}

	/**
	 * @return int
	 */
	public function getSystemNumber() {
		return (int)$this->systemNumber;
	}

	/**
	 * @param integer $planetNumber
	 */
	public function setPlanetNumber($planetNumber) {
		$this->planetNumber = $planetNumber;
	}

	/**
	 * @return int
	 */
	public function getPlanetNumber() {
		return (int)$this->planetNumber;
	}

	/**
	 * @return string 13-122-4
	 */
	public function getPlanetPositionString() {
		return $this->galaxyNumber . '-' . $this->systemNumber . '-' . $this->planetNumber;
	}

	/**
	 * @param integer $structureInProgress
	 */
	public function setStructureInProgress($structureInProgress) {
		$this->structureInProgress = $structureInProgress;
	}

	/**
	 * @return int
	 */
	public function getStructureInProgress() {
		return (int)$this->structureInProgress;
	}

	/**
	 * @param int $structureReadyTime
	 */
	public function setStructureReadyTime($structureReadyTime) {
		$this->structureReadyTime = $structureReadyTime;
	}

	/**
	 * @return int
	 */
	public function getStructureReadyTime() {
		return (int)$this->structureReadyTime;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return integer
	 */
	public function getBase() {
		return (int)$this->base;
	}

	/**
	 * @return void
	 */
	public function incrementBase() {
		$this->base = $this->base + 1;
	}

	/**
	 * @param int $lastResourceUpdate
	 */
	public function setLastResourceUpdate($lastResourceUpdate) {
		$this->lastResourceUpdate = $lastResourceUpdate;
	}

	/**
	 * @return int
	 */
	public function getLastResourceUpdate() {
		return (int)$this->lastResourceUpdate;
	}

	/**
	 * @param int $iron
	 */
	public function setIron($iron) {
		$this->iron = $iron;
	}

	/**
	 * @return int
	 */
	public function getIron() {
		return (int)$this->iron;
	}

	/**
	 * @param int $silicon
	 */
	public function setSilicon($silicon) {
		$this->silicon = $silicon;
	}
	/**
	 * @return int
	 */
	public function getSilicon() {
		return (int)$this->silicon;
	}

	/**
	 * @param int $xenon
	 */
	public function setXenon($xenon) {
		$this->xenon = $xenon;
	}

	/**
	 * @return int
	 */
	public function getXenon() {
		return (int)$this->xenon;
	}

	/**
	 * @param int $hydrazine
	 */
	public function setHydrazine($hydrazine) {
		$this->hydrazine = $hydrazine;
	}

	/**
	 * @return int
	 */
	public function getHydrazine() {
		return (int)$this->hydrazine;
	}

	/**
	 * @param int $energy
	 */
	public function setEnergy($energy) {
		$this->energy = $energy;
	}

	/**
	 * @return int
	 */
	public function getEnergy() {
		return (int)$this->energy;
	}
}