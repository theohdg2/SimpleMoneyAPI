<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class ClearMoney extends Command{

    public function __construct(){
        parent::__construct("clearmoney", "clear player's money", "/clearmoney <player>", ["resetmoney"]);
        $this->setPermission("clearMoney.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if (empty($args[0])) {
            $sender->sendMessage($prefix.self::getUsage()." <player>");
        }
        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if(!$player instanceof Player){
            $sender->sendMessage($prefix.$configLanguage->get("not-player"));
        }
        SimpleMoney::getInstance()->setMoney($player->getName(),0);
        $sender->sendMessage($prefix.$configLanguage->get("clear-money-confirm"));
        $player->sendMessage($prefix.$configLanguage->get("clear-money"));
    }
}