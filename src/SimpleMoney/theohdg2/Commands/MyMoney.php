<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MyMoney extends Command{

    public function __construct(){
        parent::__construct("mymoney", "show mymoney", "/mymoney", ["money"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $configLanguage = SimpleMoney::getInstance()->getConfigLanguage();
        $prefix = SimpleMoney::getInstance()->config()->get("prefix");
        if (!$sender instanceof Player){
            $sender->sendMessage("Command to execute in game");
        }
        $sender->sendMessage($prefix.str_replace(["{value}","{args}"],[SimpleMoney::getInstance()->getMoney($sender->getName()),SimpleMoney::getInstance()->getMonetaryUnit()],$configLanguage->get("my-status")));
    }
}