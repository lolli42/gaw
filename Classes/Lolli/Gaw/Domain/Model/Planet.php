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

	/**
	 * @var \Lolli\Gaw\Domain\Model\Player
	 * @ORM\ManyToOne(inversedBy="planets")
	 * @Flow\Lazy
	 */
	protected $player;

	/**
	 * @var int
	 */
	protected $galaxyNumber = 0;

	/**
	 * @var int
	 */
	protected $systemNumber = 0;

	/**
	 * @var int
	 */
	protected $planetNumber = 0;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem>
	 * @ORM\OneToMany(mappedBy="planet")
	 * @ORM\OrderBy({"readyTime" = "ASC"})
	 * @Flow\Lazy
	 */
	protected $structureBuildQueue;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var int
	 */
	protected $points = 0;

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
	 * @var int
	 */
	protected $base = 1;

	/**
	 * @var int
	 */
	protected $ironMine = 0;

	/**
	 * @var int
	 */
	protected $siliconMine = 0;

	/**
	 * @var int
	 */
	protected $xenonMine = 0;

	/**
	 * @var int
	 */
	protected $hydrazineMine = 0;

	/**
	 * @var int
	 */
	protected $energyMine = 0;

	/**
	 * Constructs this planet
	 */
	public function __construct() {
		$this->structureBuildQueue = new \Doctrine\Common\Collections\ArrayCollection();
	}

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
	 * @param int $galaxyNumber
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
	 * @param int $systemNumber
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
	 * @param int $planetNumber
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
	 * @return \Doctrine\Common\Collections\Collection<\Lolli\Gaw\Domain\Model\PlanetStructureBuildQueueItem>
	 */
	public function getStructureBuildQueue() {
		return $this->structureBuildQueue;
	}

	/**
	 * Add a structure to build queue
	 *
	 * @param PlanetStructureBuildQueueItem $item
	 */
	public function addStructureToStructureBuildQueue(PlanetStructureBuildQueueItem $item) {
		$item->setPlanet($this);
		$this->structureBuildQueue->add($item);
	}

	/**
	 * Remove a structure from build queue
	 *
	 * @param PlanetStructureBuildQueueItem $item
	 */
	public function removeStructureFromBuildQueue(PlanetStructureBuildQueueItem $item) {
		$this->structureBuildQueue->removeElement($item);
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
	 * @param int $points
	 */
	public function setPoints($points) {
		$this->points = $points;
	}

	/**
	 * @return int
	 */
	public function getPoints() {
		return $this->points;
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

	/**
	 * @return int
	 */
	public function getBase() {
		return (int)$this->base;
	}

	/**
	 * @param int $base
	 */
	public function setBase($base) {
		$this->base = $base;
	}

	/**
	 * @return int
	 */
	public function incrementBase() {
		$this->base = $this->base + 1;
		return $this->base;
	}

	/**
	 * @param int $ironMine
	 */
	public function setIronMine($ironMine) {
		$this->ironMine = $ironMine;
	}

	/**
	 * @return int
	 */
	public function getIronMine() {
		return (int)$this->ironMine;
	}

	/**
	 * @return int
	 */
	public function incrementIronMine() {
		$this->ironMine = $this->ironMine + 1;
		return $this->base;
	}

	/**
	 * @param int $siliconMine
	 */
	public function setSiliconMine($siliconMine) {
		$this->siliconMine = $siliconMine;
	}

	/**
	 * @return int
	 */
	public function getSiliconMine() {
		return (int)$this->siliconMine;
	}

	/**
	 * @return int
	 */
	public function incrementSiliconMine() {
		$this->siliconMine = $this->siliconMine + 1;
		return $this->siliconMine;
	}

	/**
	 * @param int $xenonMine
	 */
	public function setXenonMine($xenonMine) {
		$this->xenonMine = $xenonMine;
	}

	/**
	 * @return int
	 */
	public function getXenonMine() {
		return (int)$this->xenonMine;
	}

	/**
	 * @return int
	 */
	public function incrementXenonMine() {
		$this->xenonMine = $this->xenonMine + 1;
		return $this->xenonMine;
	}

	/**
	 * @param int $hydrazineMine
	 */
	public function setHydrazineMine($hydrazineMine) {
		$this->hydrazineMine = $hydrazineMine;
	}

	/**
	 * @return int
	 */
	public function getHydrazineMine() {
		return (int)$this->hydrazineMine;
	}

	/**
	 * @return int
	 */
	public function incrementHydrazineMine() {
		$this->hydrazineMine = $this->hydrazineMine + 1;
		return $this->hydrazineMine;
	}

	/**
	 * @param int $energyMine
	 */
	public function setEnergyMine($energyMine) {
		$this->energyMine = $energyMine;
	}

	/**
	 * @return int
	 */
	public function getEnergyMine() {
		return (int)$this->energyMine;
	}

	/**
	 * @return int
	 */
	public function incrementEnergyMine() {
		$this->energyMine = $this->energyMine + 1;
		return $this->energyMine;
	}
}