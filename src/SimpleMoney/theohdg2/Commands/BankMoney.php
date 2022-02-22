<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class BankMoney extends Command{

    public function __construct(){
        parent::__construct("bank", "access to your bank", "/bank <toggle: deposit|withdraw>");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        if(empty($args[1])){
            $sender->sendMessage($prefix.$this->getUsage()." <toggle: deposit|see|withdraw>");
        }
        if(!is_numeric($args[1])){
            $sender->sendMessage($prefix.$configLanguage->get("numeric-value"));
        }
        if($args[1] >= SimpleMoney::getInstance()->getMaxMoney()){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        if((SimpleMoney::getInstance()->getMoneyInBank($sender->getName()) + (int)$args[1]) >= SimpleMoney::getInstance()->getMaxMoney()){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        if($args[1] < 0){
            $sender->sendMessage($prefix.$configLanguage->get("takemoney-invalide-value"));
        }
        switch($args[0]){
            case "deposit":
                SimpleMoney::getInstance()->addMoneyInBank($sender->getName(),$args[1]);
                $sender->sendMessage($prefix.str_replace(["{value}","{args}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("bank-deposit")));
                break;
            case "withdraw":
                SimpleMoney::getInstance()->reduceMoneyInBank($sender->getName(),$args[1]);
                $sender->sendMessage($prefix.str_replace(["{value}","{args}"],[$args[1],SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("bank-withdraw")));
                break;
        }
        $sender->sendMessage($prefix.$this->getUsage()." <toggle: deposit|see|withdraw>");
    }
}