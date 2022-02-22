<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class AddMoney extends Command{

    public function __construct(){
        parent::__construct("addmoney", "add a player's money", "/addmoney <player> <amount>", []);
        $this->setPermission("addMoney.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if (empty($args[0]) || empty($args[1])) {
            $sender->sendMessage($prefix.self::getUsage()." <player> <amount>");
        }
        if(!is_numeric($args[1])){
            $sender->sendMessage($prefix.$configLanguage->get("numeric-value"));
        }
        if($args[1] < 0){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if(!$player instanceof Player){
            $sender->sendMessage($prefix.$configLanguage->get("not-player"));
        }
        SimpleMoney::getInstance()->addMoney($player->getName(),$args[1]);
        $player->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit(),$sender->getName()],$configLanguage->get("givemoney-confirm")));
        $sender->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit(),$player->getName()],$configLanguage->get("givemoney-admin")));

    }
}