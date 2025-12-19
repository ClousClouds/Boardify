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

namespace ClousClouds\Boardify;

use pocketmine\utils\Config;

class ConfigManager
{
	private Config $config;

	public function __construct(private Main $plugin)
	{
		$this->config = new Config(
			$this->plugin->getDataFolder() . 'config.yml',
			Config::YAML
		);
	}

	public function reload() : void
	{
		$this->config->reload();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getDefaultBoard() : array
	{
		return $this->config->get('default-board', []);
	}

	public function getUpdateInterval() : int
	{
		return (int) $this->config->get('update-interval', 20);
	}
}
