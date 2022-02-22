<?php

namespace SimpleMoney\theohdg2;

use SimpleMoney\theohdg2\Commands\AddMoney;
use SimpleMoney\theohdg2\Commands\BankMoney;
use SimpleMoney\theohdg2\Commands\ClearMoney;
use SimpleMoney\theohdg2\Commands\MyMoney;
use SimpleMoney\theohdg2\Commands\PayMoney;
use SimpleMoney\theohdg2\Commands\ReduceMoney;
use SimpleMoney\theohdg2\Commands\SeeMoney;
use SimpleMoney\theohdg2\Commands\SetDefaultMoney;
use SimpleMoney\theohdg2\Commands\SetMoney;
use SimpleMoney\theohdg2\Commands\TopMoney;
use Exception;
use JsonException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\Config;
use pocketmine\utils\Limits;

class SimpleMoney extends PluginBase implements Listener,PluginOwned{

    /** @var SimpleMoney */
    private static SimpleMoney $instance;
    /** @var Config */
    private Config $config;
    /** @var Config */
    private Config $saveConfig;

    protected function onEnable(): void{
        //setup folders and language files
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "Language");

        $allLang = scandir($this->getFile() . "resources\Language");
        array_shift($allLang);
        array_shift($allLang);
        foreach ($allLang as $file) {
            $this->saveResource("Language\\" . $file);
        }
        $this->saveResource("config.yml");
        $this->saveResource("economy.json");
        //set instance of this
        self::$instance = $this;
        //set default configs
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->saveConfig = new Config($this->getDataFolder() . "economy.json", Config::JSON);
        //register event
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        //register permissions
        PermissionManager::getInstance()->addPermission(new Permission("addMoney.use"));
        PermissionManager::getInstance()->addPermission(new Permission("reduceMoney.use"));
        PermissionManager::getInstance()->addPermission(new Permission("setDefaultMoney.use"));
        PermissionManager::getInstance()->addPermission(new Permission("setMoney.use"));
        PermissionManager::getInstance()->addPermission(new Permission("seeMoney.use"));
        PermissionManager::getInstance()->addPermission(new Permission("clearMoney.use"));
        //register Commands
        $this->getServer()->getCommandMap()->registerAll("economy",[
            new AddMoney(),
            new ReduceMoney(),
            new SetDefaultMoney(),
            new SetMoney(),
            new MyMoney(),
            new PayMoney(),
            new TopMoney(),
            new ClearMoney(),
            new BankMoney(),
            new SeeMoney()
        ]);
        //task auto relaod all config
        $this->getScheduler()->scheduleRepeatingTask(new TaskConfigAutoReload($this->saveConfig,$this->config),6000);
    }

    /**
     * returns this file with all the variables already defined
     * @return self
     */
    public static function getInstance(): self{
        return self::$instance;
    }

    /**
     * this function is called when a player joins the server
     * @param PlayerJoinEvent $event
     */
    public function PlayerJoinEvent(PlayerJoinEvent $event){
        if (!$this->playerHasAccount($event->getPlayer())) $this->createPlayerAccount($event->getPlayer());
    }

    /////////////////////Event////////////////////

    /**
     * returns true if the player is already in the save money file
     * @param string $playerName
     * @return bool
     */
    public function playerHasAccount(string $playerName): bool{
        return $this->getSaveConfig()->exists($playerName);
    }

    //////////////////////API/////////////////////

    /**
     * this function allows you to create/initiate in the player config with the default money and the default bank money
     * @param string $playerName
     * @return bool
     */
    public function createPlayerAccount(string $playerName): bool{
        try {
            $this->getSaveConfig()->set($playerName, ["money" => $this->getDefaultMoney(), "bank" => $this->getDefaultMoneyInBank()]);
            $this->getSaveConfig()->save();
            return true;
        } catch (JsonException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getTopMoney():string{
        $all = [];
        foreach ($this->getSaveConfig()->getAll() as $name => $data){
            $all[] = $name."=>".$data["money"].$this->getMonetaryUnit()."\n";
        }
        arsort($all);
        $str="";
        for ($i = 0; $i < 11; $i++){
            if(!isset($all[$i])) continue;
            $str.= $all[$i];
        }
        return $str;
    }

    /**
     * returns the default in-game currency
     * @return int
     */
    public function getDefaultMoney(): int{
        return (int)$this->config()->get("defaultMoney", 1000);
    }



    /**
     * returns default currency to bank
     * @return int
     */
    public function getDefaultMoneyInBank(): int{
        return (int)$this->config()->get("DefaultMoneyInBank", 1000);
    }

    /**
     * adds visible money to a player
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function addMoney(string $playerName, int $amount): bool{
        if (($add = $this->getSaveConfig()->getNested($playerName . ".money", $this->getDefaultMoney()) + $amount) <= $this->getMaxMoney()) {
            $this->getSaveConfig()->setNested($playerName . ".money", $add);
        } else {
            return false;
        }
        $this->getSaveConfig()->save();
        return true;
    }

    /**
     * defines the maximum value a player can have as visible or banked money
     * @param int $maxMoney
     * @return bool
     * @throws JsonException
     */
    public function setMaxMoney(int $maxMoney): bool{
        $this->config()->set("MaxMoney", $maxMoney);
        $this->config()->save();
        return true;
    }

    /**
     * returns the maximum value a player can have in visible or banked money
     * @return int
     */
    public function getMaxMoney(): int{
        return (int)$this->config()->get("MaxMoney", Limits::INT32_MAX);
    }

    /**
     * reduce player's visible money
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function reduceMoney(string $playerName, int $amount): bool{
        if (($set = $this->getSaveConfig()->getNested($playerName . ".money", $this->getDefaultMoney()) - $amount) >= 0) {
            $this->getSaveConfig()->setNested($playerName . ".money", $set);
            $this->getSaveConfig()->save();
        } else {
            return false;
        }
        return true;
    }

    /**
     * set visible player money
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function setMoney(string $playerName, int $amount): bool{
        if ($amount <= $this->getMaxMoney()) {
            $this->getSaveConfig()->setNested($playerName . ".money", $amount);
            $this->getSaveConfig()->save();
        } else {
            return false;
        }
        return true;
    }

    /**
     * adds money to player's bank
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function addMoneyInBank(string $playerName, int $amount): bool{
        if (($add = $this->getSaveConfig()->getNested($playerName . ".bank", $this->getDefaultMoneyInBank()) + $amount) <= $this->getMaxMoney()) {
            $this->getSaveConfig()->setNested($playerName . ".bank", $add);
        } else {
            return false;
        }
        //TODO: enlever largent au joueur
        $this->getSaveConfig()->save();
        return true;
    }

    /**
     * reduce player's money in bank
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function reduceMoneyInBank(string $playerName, int $amount): bool{
        if (($set = $this->getSaveConfig()->getNested($playerName . ".bank", $this->getDefaultMoneyInBank()) - $amount) >= 0) {
            $this->getSaveConfig()->setNested($playerName . ".bank", $set);
            $this->getSaveConfig()->save();
            return true;
        } else {
            return false;
        }
        //TODO: rajouter largent au joueur
    }

    /**
     * defines the money in the player's bank
     * @param string $playerName
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function setMoneyInBank(string $playerName, int $amount): bool{
        if ($amount <= $this->getMaxMoney()) {
            $this->getSaveConfig()->setNested($playerName . ".bank", $amount);
            $this->getSaveConfig()->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns the player's visible money
     * @param string $playerName
     * @return int
     */
    public function getMoney(string $playerName): int{
        return (int)$this->getSaveConfig()->getNested($playerName . ".money", $this->getDefaultMoney());
    }

    /**
     * returns money to player's bank
     * @param string $playerName
     * @return int
     */
    public function getMoneyInBank(string $playerName): int{
        return (int)$this->getSaveConfig()->getNested($playerName . ".bank", $this->getDefaultMoneyInBank());
    }

    /**
     * set default currency in bank
     * @param int $defaultMoney
     * @return bool
     * @throws JsonException
     */
    public function setDefaultMoneyInBank(int $defaultMoney): bool{
        $this->config()->set("DefaultMoneyInBank", $defaultMoney);
        $this->config()->save();
        return true;
    }

    /**
     * set the default in-game currency
     * @param int $defaultMoney
     * @return bool
     * @throws JsonException
     */
    public function setDefaultMoney(int $defaultMoney): bool{
        $this->config()->set("defaultMoney", $defaultMoney);
        $this->config()->save();
        return true;
    }

    /**
     * allows someone to pay $amount.
     * From giver to receiver
     * @param string $nameOfDonateur
     * @param string $nameOfReceveur
     * @param int $amount
     * @return bool
     * @throws JsonException
     */
    public function pay(string $nameOfDonateur,string $nameOfReceveur,int $amount):bool{
        if($this->getMoney($nameOfDonateur) >= $amount && ($this->getMoney($nameOfReceveur)+$amount) <= $this->getMaxMoney()){
            $this->reduceMoney($nameOfDonateur,$amount);
            $this->addMoney($nameOfReceveur,$amount);
            return true;
        }else{
            return false;
        }
    }

    public function getMonetaryUnit():string{
        return $this->config()->get("monetary-unit");
    }
    public function setMonetaryUnit(string $monetaryUnit): bool{
        $this->config()->set("monetary-unit",$monetaryUnit);
        $this->config()->save();
        return true;
    }

    //////////////////////CONFIG//////////////////////

    /**
     * returns saving player money
     * @return Config
     */
    public function getSaveConfig(): Config{
        return $this->saveConfig ?? new Config($this->getDataFolder() . "economy.json", Config::JSON);
    }

    /**
     * returns the configuration of the options
     * @return Config
     */
    public function config(): Config{
        return $this->config ?? new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }
    /**
     * returns the configuration of the chosen language
     * @return Config
     * @throws Exception
     */
    public function getConfigLanguage(): Config{
        return new Config($this->getDataFolder() . "Language/lang_".($this->config()->get("default-lang","eng")).".yml", Config::YAML);
    }

    /////////////Owning plugin/////////////////////////

    /**
     * return the owning plugin
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin{
        return $this;
    }
}
