<?php

namespace Terpz710\AnimationJoin;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\Plugin;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\entity\animation\TotemUseAnimation;
use Terpz710\AnimationJoin\Main;

class EventListener implements Listener {

    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
    $player = $event->getPlayer();

    $enableMOTD = $this->config->getNested("Enable.MOTD", true);
    $enableMessages = $this->config->getNested("Enable.Messages", true);
    $enableTitle = $this->config->getNested("Enable.Title", true);
    $enableJoinAnimation = $this->config->getNested("Enable.Join-Animation", true);

    if ($enableMOTD && $this->config->exists("MOTD")) {
        $motd = $this->config->get("MOTD");
        $player->sendMessage($motd);
    }

    $customJoinConfig = $this->config->get("Messages.Join-Message", "");
    $customLeaveConfig = $this->config->get("Messages.Leave-Message", "");

    if ($enableMessages && $customJoinConfig !== "") {
        $joinMessage = str_replace("%player%", $player->getName(), $customJoinConfig);
        $event->setJoinMessage($joinMessage);
    }

    $titleConfig = $this->config->get("Title", []);
    if ($enableTitle && isset($titleConfig["Title-Text"])) {
        $player->sendTitle(
            $titleConfig["Title-Text"],
            $titleConfig["Subtitle-Text"] ?? "",
            $titleConfig["Fade-In"] ?? 0,
            $titleConfig["Stay"] ?? 20,
            $titleConfig["Fade-Out"] ?? 0
        );
    }

    $joinAnimationConfig = $this->config->get("Join-Animation", []);
    if ($enableJoinAnimation) {
        $chosenAnimation = $joinAnimationConfig["Animation"] ?? "";
        if ($chosenAnimation === "totem") {
            $animation = new TotemUseAnimation($player);
            $animations = $animation->encode();

            foreach ($animations as $packet) {
                $player->getNetworkSession()->sendDataPacket($packet);
            }
        } elseif ($chosenAnimation === "guardian") {
            $player->getNetworkSession()->sendDataPacket(
                LevelEventPacket::create(
                    eventId: LevelEvent::GUARDIAN_CURSE,
                    eventData: 0,
                    position: $player->getPosition()
                )
            );
        }
    }
}


    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        $enableMessages = $this->config->getNested("Enable.Messages", true);
        $customLeaveConfig = $this->config->get("Messages.Leave-Message");

        if ($enableMessages) {
            $leaveMessage = str_replace("%player%", $player->getName(), $customLeaveConfig);
            $event->setQuitMessage($leaveMessage);
        }
    }
}
