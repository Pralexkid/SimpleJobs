<?php

namespace PralexJob;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use PralexJob\Commands\Job;
use PralexJob\Listeners\PlayersListener;

class Main extends PluginBase
{
    private static Main $instance;
    private Config $config;

    public function onEnable(): void
    {
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new PlayersListener(), $this);
        $this->getServer()->getCommandMap()->register("job", new Job("job", "Ouvre le menu des metiers"));
    }

    public function getSettings() : Config
    {
        return $this->config;
    }

    public function getPlayersData() : Config
    {
        return new Config("data.yml", Config::YAML);
    }

    public static function getInstance() : Main
    {
        return self::$instance;
    }
}