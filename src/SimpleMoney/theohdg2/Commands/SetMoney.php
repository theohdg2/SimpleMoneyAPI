<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class SetMoney extends Command{

    public function __construct(){
        parent::__construct("setmoney", "defines the money of a player", "/setmoney <player> <amount>", []);
        $this->setPermission("setMoney.use");
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
        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if(!$player instanceof Player){
            $sender->sendMessage($prefix.$configLanguage->get("not-player"));
        }
        if($args[1] <= SimpleMoney::getInstance()->getMaxMoney()){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        if($args[1] < 0){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        SimpleMoney::getInstance()->setMoney($player->getName(),$args[1]);
        $sender->sendMessage($prefix.str_replace(["{player}","{value}","{args}"],[$player->getName(),$args[1],SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("set-money-confirm")));
        $player->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit(),$sender->getName()],$configLanguage->get("set-money")));
    }
}