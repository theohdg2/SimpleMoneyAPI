<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class SeeMoney extends Command{

    public function __construct(){
        parent::__construct("seemoney", "see player's money", "/seemoney <player> ", []);
        $this->setPermission("seeMoney.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if (empty($args[0]) ) {
            $sender->sendMessage($prefix.self::getUsage()." <player>");
        }
        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if(!$player instanceof Player){
            $sender->sendMessage($prefix.$configLanguage->get("not-player"));
        }
        $sender->sendMessage($prefix.str_replace("{player}",$player->getName(),$configLanguage->get("seemoney")));
    }
}