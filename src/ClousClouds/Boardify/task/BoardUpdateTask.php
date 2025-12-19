<?php

/*
 *
 *   ____                      _ _  __
 *  |  _ \                    | (_)/ _|
 *  | |_) | ___   __ _ _ __ __| |_| |_ _   _
 *  |  _ < / _ \ / _` | '__/ _` | |  _| | | |
 *  | |_) | (_) | (_| | | | (_| | | | | |_| |
 *  |____/ \___/ \__,_|_|  \__,_|_|_|  \__, |
 *                                      __/ |
 *                                     |___/
 * @license MIT
 * @author ClousClouds Team
 * @link https://github.com/ClousClouds/Boardify
 *
 *
 */

declare(strict_types=1);

namespace KnosTx\Boardify\task;

use KnosTx\Boardify\BoardManager;
use pocketmine\scheduler\Task;

class BoardUpdateTask extends Task
{
	public function __construct(private BoardManager $boardManager) {}

	public function onRun() : void
	{
		$this->boardManager->updateBoards();
	}
}
