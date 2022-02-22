<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SetDefaultMoney extends Command{

    public function __construct(){
        parent::__construct("setdefaultmoney", "set the default money", "/setdefaultmoney <amount>", []);
        $this->setPermission("setDefaultMoney.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if(!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if(empty($args[0])){
            $sender->sendMessage($prefix.self::getUsage()." <player>");
        }
        if(!is_numeric($args[1])){
            $sender->sendMessage($prefix.$configLanguage->get("numeric-value"));
        }
        if($args[1] < 0){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        SimpleMoney::getInstance()->setDefaultMoney($args[0]);
        $sender->sendMessage($prefix.$configLanguage->get("default-money"));
    }
}