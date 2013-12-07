<?php
namespace Lolli\Gaw\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
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