<?php
namespace Lolli\Gaw\ViewHelpers\Iterator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Repeat rendering of children in a for loop $from to $to incrementing by 1
 */
class ForViewHelper extends AbstractViewHelper {

	/**
	 * Count up a value $from to $to
	 *
	 * @param integer $from Lower bound
	 * @param integer $to Upper bound
	 * @param string $as Current iteration count value within loop
	 * @param string $iteration The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)
	 * @return string
	 */
	public function render($from, $to, $as, $iteration = NULL) {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 * @throws Exception
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if ($arguments['from'] === NULL) {
			return '';
		}
		if ($arguments['to'] === NULL) {
			return '';
		}

		$from = intval($arguments['from']);
		$to = intval($arguments['to']);
		if ($to < $from) {
			throw new Exception(
				'To must be higher than from',1387130883
			);
		}

		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => $to - $from,
		);

		$output = '';

		for ($i = $from; $i <= $to; $i ++) {
			$templateVariableContainer->add($arguments['as'], $i);
			if ($arguments['iteration'] !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$templateVariableContainer->add($arguments['iteration'], $iterationData);
				$iterationData['index']++;
				$iterationData['cycle']++;
			}
			$output .= $renderChildrenClosure();
			$templateVariableContainer->remove($arguments['as']);
			if ($arguments['iteration'] !== NULL) {
				$templateVariableContainer->remove($arguments['iteration']);
			}
		}

		return $output;
	}
}
