<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class ReduceMoney extends Command{
    public function __construct(){
        parent::__construct("reduce", "reduce a player's money", "/reduce <player> <amount>", []);
        $this->setPermission("reduceMoney.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if(empty($args[0]) || empty($args[1])){
            $sender->sendMessage($prefix.self::getUsage()." <player>");
        }
        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if(!$player instanceof Player){
            $sender->sendMessage($prefix.$configLanguage->get("not-player"));
        }
        if(!is_numeric($args[1])){
            $sender->sendMessage($prefix.$configLanguage->get("numeric-value"));
        }
        if($args[1] < 0){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        if((SimpleMoney::getInstance()->getMoney($player->getName()) - (int)$args[1]) < 0){
            $sender->sendMessage($prefix.str_replace(["{player}","{value}","{args}"],[$player->getName(),$args[1],SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("takemoney-invalide-value-for-target")));
        }
        SimpleMoney::getInstance()->reduceMoney($player->getName(),$args[0]);
        $sender->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[0],SimpleMoney::getInstance()->getMonetaryUnit(),$player->getName()],$configLanguage->get("takemoney-admin")));
        $player->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[0],SimpleMoney::getInstance()->getMonetaryUnit(),$sender->getName()],$configLanguage->get("takemoney-confirm")));
    }
}