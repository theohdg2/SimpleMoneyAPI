<?php

namespace SimpleMoney\theohdg2;

use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class TaskConfigAutoReload extends Task{
    private Config $option;
    private Config $save;

    public function __construct(Config $saveConfig, Config $configOption)
    {
        $this->save = $saveConfig;
        $this->option = $configOption;
    }
    public function onRun(): void
    {
        $this->save->reload();
        $this->option->reload();
    }
}