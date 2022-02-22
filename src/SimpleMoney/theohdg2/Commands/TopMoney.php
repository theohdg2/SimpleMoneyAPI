<?php

namespace SimpleMoney\theohdg2\Commands;

use SimpleMoney\theohdg2\SimpleMoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class TopMoney extends Command{
    public function __construct(){
        parent::__construct("topmoney", "show topmoney", "/topmoney", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $sender->sendMessage(SimpleMoney::getInstance()->getTopMoney());
    }
}