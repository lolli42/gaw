<?php
namespace Lolli\Gaw\Domain\Repository;

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
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PlanetRepository extends Repository {

	/**
	 * Find a planet by its absolute position
	 *
	 * @param integer $galaxy Galaxy number
	 * @param integer $system System number
	 * @param integer $position Position in system
	 * @return \Lolli\Gaw\Domain\Model\Planet
	 */
	public function findOneByPosition($galaxy, $system, $position) {
		$query = $this->createQuery();
		return $query
			->matching(
				$query->logicalAnd(
					$query->equals('galaxyNumber', $galaxy),
					$query->equals('systemNumber', $system),
					$query->equals('planetNumber', $position)
				)
			)
			->execute()
			->getFirst();
	}

	/**
	 * Find all planets in a galaxy system
	 *
	 * @param $galaxy
	 * @param $system
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface Planets
	 */
	public function findByGalaxyAndSystem($galaxy, $system) {
		$query = $this->createQuery();
		return $query
			->matching(
				$query->logicalAnd(
					$query->equals('galaxyNumber', $galaxy),
					$query->equals('systemNumber', $system)
				)
			)
			->setOrderings(
				array(
					'systemNumber' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
				)
			)
			->execute();
	}
}