<?php

namespace PralexJob\Listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use PralexJob\Main;
use PralexJob\Manager\PlayersManager;

class PlayersListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event) : void
    {
        $player = $event->getPlayer()->getName();
        $config = Main::getInstance()->getPlayersData();
        if (!$config->exists($event->getPlayer()->getName()))
        {
            PlayersManager::initData($event->getPlayer()->getName());
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) : void
    {
        $config = Main::getInstance()->getSettings()->getAll();
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $im = $block->getTypeId() . ":" . $block->getStateId();
        if ($config['jobs']['mineur']['enable'] === true)
        {
            if (array_key_exists($im, $config['jobs']['mineur']['blocs']))
            {
                if ($player->getGamemode() === GameMode::CREATIVE())
                {
                    if ($config['jobs']['mineur']['enable-creative'] === false)
                    {
                        return;
                    }
                }
                $nbr = $config['jobs']['mineur']['blocs'][$im];
                PlayersManager::addExp($player->getName(), "mineur", $nbr);
                $player->sendPopup("+ ".$nbr." ".$config['jobs']['mineur']['mineur-name']);
            }
        }
        if ($config['jobs']['agriculteur']['enable'] === true)
        {
            if (array_key_exists($im, $config['jobs']['agriculteur']['blocs']))
            {
                if ($player->getGamemode() === GameMode::CREATIVE())
                {
                    if ($config['jobs']['agriculteur']['enable-creative'] === false)
                    {
                        return;
                    }
                }
                $nbr = $config['jobs']['agriculteur']['blocs'][$im];
                PlayersManager::addExp($player->getName(), "agriculteur", $nbr);
                $player->sendPopup("+ ".$nbr." ".$config['jobs']['agriculteur']['agriculteur-name']);
            }
        }
        if ($config['jobs']['bucheron']['enable'] === true)
        {
            if (array_key_exists($im, $config['jobs']['bucheron']['blocs']))
            {
                if ($player->getGamemode() === GameMode::CREATIVE())
                {
                    if ($config['jobs']['bucheron']['enable-creative'] === false)
                    {
                        return;
                    }
                }
                $nbr = $config['jobs']['bucheron']['blocs'][$im];
                PlayersManager::addExp($player->getName(), "bucheron", $nbr);
                $player->sendPopup("+ ".$nbr." ".$config['jobs']['bucheron']['bucheron-name']);
            }
        }
    }

    public function onCraft(CraftItemEvent $event) : void
    {
        $config = Main::getInstance()->getSettings()->getAll();
        $item = $event->getOutputs();
        $player = $event->getPlayer()->getName();
        if ($config['jobs']['assembleur']['enable'] === true)
        {
            $itemname = null;
            foreach ($item as $crafted)
            {
                if ($crafted->getName() === $itemname) return;
                if (array_key_exists("all", $config['jobs']['assembleur']['crafts']))
                {
                    if ($event->getPlayer()->getGamemode() === GameMode::CREATIVE())
                    {
                        if ($config['jobs']['assembleur']['enable-creative'] === false)
                        {
                            return;
                        }
                    }
                    $nbr = $config['jobs']['assembleur']['crafts']["all"];
                    PlayersManager::addExp($player, "assembleur", $nbr);
                    $event->getPlayer()->sendPopup("+ ".$nbr." ".$config['jobs']['assembleur']['assembleur-name']);
                    return;
                }
                $im = $crafted->getTypeId();
                if (array_key_exists($im, $config['jobs']['assembleur']['crafts']))
                {
                    if ($event->getPlayer()->getGamemode() === GameMode::CREATIVE())
                    {
                        if ($config['jobs']['assembleur']['enable-creative'] === false)
                        {
                            return;
                        }
                    }
                    $nbr = $config['jobs']['assembleur']['crafts'][$im];
                    PlayersManager::addExp($player, "assembleur", $nbr);
                    $event->getPlayer()->sendPopup("+ ".$nbr." ".$config['jobs']['assembleur']['assembleur-name']);
                }
                $itemname = $crafted->getName();
            }
            $itemname = null;
        }
    }

    public function onKill(EntityDeathEvent $event) : void
    {
        $config = Main::getInstance()->getSettings()->getAll();
        $cause = $event->getEntity()->getLastDamageCause();
        $entity = $event->getEntity();
        if ($cause instanceof EntityDamageByEntityEvent)
        {
            $player = $cause->getEntity();
            if ($player instanceof Player)
            {
                if ($config['jobs']['chasseur']['enable'] === true)
                {
                    if ($entity instanceof Player)
                    {
                        if (array_key_exists("player", $config['jobs']['chasseur']['entites']))
                        {
                            if ($player->getGamemode() === GameMode::CREATIVE())
                            {
                                if ($config['jobs']['chasseur']['enable-creative'] === false)
                                {
                                    return;
                                }
                            }
                            $nbr = $config['jobs']['chasseur']['entites']['player'];
                            PlayersManager::addExp($player->getName(), "chasseur", $nbr);
                            $player->sendPopup("+ ".$nbr." ".$config['jobs']['chasseur']['chasseur-name']);
                        }
                    }
                    $im = $entity->getDisplayName();
                    if (array_key_exists($im, $config['jobs']['chasseur']['entites']))
                    {
                        if ($player->getGamemode() === GameMode::CREATIVE())
                        {
                            if ($config['jobs']['chasseur']['enable-creative'] === false)
                            {
                                return;
                            }
                        }
                        $nbr = $config['jobs']['chasseur']['entites'][$im];
                        PlayersManager::addExp($player->getName(), "chasseur", $nbr);
                        $player->sendPopup("+ ".$nbr." ".$config['jobs']['chasseur']['chasseur-name']);
                    }
                }
            }
        }
    }
}