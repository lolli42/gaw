<?php
namespace Lolli\Gaw\Command\Exception;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Lolli.Gaw".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Catchable command worker exception
 *
 * Thrown if worker commands are "ok" to fail, usually for "blocking"
 * "client" triggered jobs. Main worker loop catches those and gives
 * the message back to clients who translate them to a flash message.
 */
class CatchableWorkerException extends \Lolli\Gaw\Command\Exception {

}