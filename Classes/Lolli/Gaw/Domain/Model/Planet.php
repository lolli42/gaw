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
 */
class Planet {

	const STRUCTURE_NONE = 0;
	const STRUCTURE_BASE = 1;

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
	 * @var float
	 */
	protected $structureReadyTime = 0;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var integer
	 */
	protected $base = 0;

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
		return $this->galaxyNumber;
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
		return $this->systemNumber;
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
		return $this->planetNumber;
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
		return $this->structureInProgress;
	}

	/**
	 * @param float $structureReadyTime
	 */
	public function setStructureReadyTime($structureReadyTime) {
		$this->structureReadyTime = $structureReadyTime;
	}

	/**
	 * @return int
	 */
	public function getStructureReadyTime() {
		return $this->structureReadyTime;
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
		return $this->base;
	}

	/**
	 * @return void
	 */
	public function incrementBase() {
		$this->base = $this->base + 1;
	}

}