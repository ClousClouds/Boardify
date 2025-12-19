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

namespace ClousClouds\Boardify\commands;

use ClousClouds\Boardify\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class BoardifyCommand extends Command implements PluginOwned
{
	use PluginOwnedTrait;

	public function __construct(private Main $plugin)
	{
		parent::__construct('boardify');
		$this->setDescription('Boardify Commands');
		$this->setUsage('/boardify reload');
		$this->setAliases(['board']);
		$this->setPermission('boardify.command');

		$this->owningPlugin = $plugin;
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool
	{
		if (!$sender->hasPermission('boardify.command')) {
			$sender->sendMessage('§cYou do not have permission.');
			return true;
		}

		if (($args[0] ?? '') === 'reload') {
			$this->plugin->reloadConfig();
			$this->plugin->getConfigManager()->reload();
			$sender->sendMessage('§aBoardify config reloaded!');
			return true;
		}

		$sender->sendMessage('§cUsage: /boardify reload');
		return true;
	}
}
