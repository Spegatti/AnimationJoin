<?php

namespace Terpz710\AnimationJoin;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Terpz710\AnimationJoin\EventListener;

class Main extends PluginBase {

    public function onEnable(): void {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this, $config), $this);
        $this->saveDefaultConfig();
    }
}
