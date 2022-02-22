<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class PayMoney extends Command{

    public function __construct(){
        parent::__construct("pay", "pay player", "/pay", []);
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
       if(SimpleMoney::getInstance()->config()->get("allow-pay-player-offline") == "false" ){
           if(!$player->isConnected()){
                $sender->sendMessage($prefix.str_replace("{player}",$player->getName(),$configLanguage->get("player-not-connected")));
           }
           if(SimpleMoney::getInstance()->playerHasAccount($player->getName())){
               $sender->sendMessage($prefix.str_replace("{player}",$player->getName(),$configLanguage->get("player-never-connected")));
           }
       }
       if($args[1] <= SimpleMoney::getInstance()->getMoney($sender->getName())){
           $sender->sendMessage($prefix.$configLanguage->get("money-error"));
       }
       if($args[1] < 0){
           $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
       }
       if((SimpleMoney::getInstance()->getMoney($player->getName()) + (int)$args[1]) > SimpleMoney::getInstance()->getMaxMoney()){
           $sender->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit(),$player->getName()],$configLanguage->get("givemoney-invalide-value")));
       }
        SimpleMoney::getInstance()->pay($sender->getName(),$player->getName(),$args[1]);
       $sender->sendMessage($prefix.str_replace(["{value}","{args}","{player}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit(),$player->getName()],$configLanguage->get("pay-success")));
       $player->sendMessage($prefix.str_replace(["{player}","{value}","{args}"],[$sender->getName(),$args[1],SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("money-paid")));
    }
}