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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use function count;
use function round;
use function str_replace;
use function time;

class BoardManager implements Listener
{
	/** @var array<string, bool> */
	private array $activeBoards = [];

	/** @var array<string, int> */
	private array $loginTimes = [];

	public function __construct(private Main $plugin)
	{
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function onJoin(PlayerJoinEvent $event) : void
	{
		$player = $event->getPlayer();
		$this->loginTimes[$player->getName()] = time();
		$this->createBoard($player);
	}

	public function onQuit(PlayerQuitEvent $event) : void
	{
		$this->removeBoard($event->getPlayer());
	}

	private function getObjectiveName(Player $player) : string
	{
		return 'boardify_' . $player->getName();
	}

	public function createBoard(Player $player) : void
	{
		if (isset($this->activeBoards[$player->getName()])) {
			return;
		}

		$config = $this->plugin->getConfigManager()->getDefaultBoard();
		$objectiveName = $this->getObjectiveName($player);

		$packet = new SetDisplayObjectivePacket();
		$packet->displaySlot = 'sidebar';
		$packet->objectiveName = $objectiveName;
		$packet->displayName = (string) ($config['title'] ?? 'Boardify');
		$packet->criteriaName = 'dummy';
		$packet->sortOrder = 0;

		$player->getNetworkSession()->sendDataPacket($packet);

		$this->activeBoards[$player->getName()] = true;
		$this->updateBoard($player);
	}

	public function removeBoard(Player $player) : void
	{
		if (!isset($this->activeBoards[$player->getName()])) {
			return;
		}

		$packet = new RemoveObjectivePacket();
		$packet->objectiveName = $this->getObjectiveName($player);
		$player->getNetworkSession()->sendDataPacket($packet);

		unset($this->activeBoards[$player->getName()]);
		unset($this->loginTimes[$player->getName()]);
	}

	public function updateBoards() : void
	{
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			if (isset($this->activeBoards[$player->getName()])) {
				$this->updateBoard($player);
			}
		}
	}

	private function updateBoard(Player $player) : void
	{
		$config = $this->plugin->getConfigManager()->getDefaultBoard();
		$lines = $config['lines'] ?? [];

		$entries = [];
		$objectiveName = $this->getObjectiveName($player);
		$online = count($player->getServer()->getOnlinePlayers());

		$score = count($lines);
		$id = 1;

		foreach ($lines as $line) {
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $objectiveName;
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->customName = $this->parsePlaceholders($player, (string) $line, $online);
			$entry->score = $score--;
			$entry->scoreboardId = $id++;
			$entries[] = $entry;
		}

		$packet = new SetScorePacket();
		$packet->type = SetScorePacket::TYPE_CHANGE;
		$packet->entries = $entries;

		$player->getNetworkSession()->sendDataPacket($packet);
	}

	private function parsePlaceholders(Player $player, string $line, int $online) : string
	{
		$playtime = isset($this->loginTimes[$player->getName()])
			? (int) ((time() - $this->loginTimes[$player->getName()]) / 60)
			: 0;

		return str_replace(
			[
				'{player}',
				'{online}',
				'{ping}',
				'{world}',
				'{x}',
				'{y}',
				'{z}',
				'{health}',
				'{max_health}',
				'{playtime}',
			],
			[
				$player->getName(),
				$online,
				$player->getNetworkSession()->getPing(),
				$player->getWorld()->getDisplayName(),
				round($player->getPosition()->getX(), 1),
				round($player->getPosition()->getY(), 1),
				round($player->getPosition()->getZ(), 1),
				round($player->getHealth(), 1),
				$player->getMaxHealth(),
				$playtime,
			],
			$line
		);
	}
}
