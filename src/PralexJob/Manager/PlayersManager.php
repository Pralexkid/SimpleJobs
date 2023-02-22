<?php

namespace PralexJob\Manager;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;
use PralexJob\Main;

class PlayersManager
{
    public static function initData(string $player) : void
    {
        $config = Main::getInstance()->getPlayersData();
        $config->setNested($player, array(
            "mineur-lvl" => 0,
            "mineur-exp" => 0,
            "agriculteur-lvl" => 0,
            "agriculteur-exp" => 0,
            "bucheron-lvl" => 0,
            "bucheron-exp" => 0,
            "chasseur-lvl" => 0,
            "chasseur-exp" => 0,
            "assembleur-lvl" => 0,
            "assembleur-exp" => 0
        ));
        $config->save();
    }

    public static function getLevel(string $player, string $job) : int
    {
        $config = Main::getInstance()->getPlayersData();
        return $config->getNested($player.".".$job."-lvl");
    }

    public static function getExp(string $player, string $job) : int
    {
        $config = Main::getInstance()->getPlayersData();
        return $config->getNested($player.".".$job."-exp");
    }

    public static function getExpManquant(string $player, string $job) : int
    {
        $settings = Main::getInstance()->getSettings()->getAll();
        $lvl = self::getLevel($player, $job);
        $exp = self::getExp($player, $job);
        $exp_next_lvl = $settings['jobs'][$job]['niveaux'][$lvl + 1]['exp-requis'];
        return $exp_next_lvl - $exp;
    }

    public static function getExpRequis(string $job, int $lvl) : int
    {
        $settings = Main::getInstance()->getSettings()->getAll();
        return $settings['jobs'][$job]['niveaux'][$lvl + 1]['exp-requis'];
    }

    public static function isAtMaxLevel(string $player, string $job) : bool
    {
        $settings = Main::getInstance()->getSettings()->getAll();
        $count = count($settings['jobs'][$job]['niveaux']);
        if (($count - self::getLevel($player, $job)) === 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function addExp(string $player, string $job, int $nbr) : void
    {
        if (!self::isAtMaxLevel($player, $job))
        {
            $config = Main::getInstance()->getPlayersData();
            $settings = Main::getInstance()->getSettings()->getAll();
            $lvl = self::getLevel($player, $job);
            $exp = self::getExp($player, $job);
            $jobname = $settings['jobs'][$job][$job."-name"];
            if ($exp + $nbr >= self::getExpRequis($job, $lvl))
            {
                $add = $exp + $nbr - self::getExpRequis($job, $lvl);
                $config->setNested($player.".".$job."-lvl", $lvl + 1);
                $config->save();
                $config->setNested($player.".".$job."-exp", 0);
                $config->save();
                $msg = str_replace(
                    ["{player}", "{lvl}", "{job}"],
                    [$player, $lvl + 1, $jobname],
                    $settings['message-to-all']
                );
                if ($settings['message-to-all'] !== null)
                {
                    Server::getInstance()->broadcastMessage($msg);
                }
                $config->setNested($player.".".$job."-exp", self::getExp($player, $job) + $add);
                $config->save();
                $pl = Server::getInstance()->getPlayerExact($player);
                if ($pl instanceof Player)
                {
                    switch ($settings['jobs'][$job]['niveaux'][$lvl + 1]['recompense']['type'])
                    {
                        case "item":
                            $item = LegacyStringToItemParser::getInstance()->parse($settings['jobs'][$job]['niveaux'][$lvl + 1]['recompense']['item']);
                            $item->setCount($settings['jobs'][$job]['niveaux'][$lvl + 1]['recompense']['count']);
                            if ($pl->getInventory()->getAddableItemQuantity($item) >= $item->getCount())
                            {
                                $pl->getInventory()->addItem($item);
                            } else
                            {
                                $pl->getWorld()->dropItem($pl->getPosition(), $item);
                            }
                                break;
                        case "commande":
                            $commande = str_replace("{player}", $pl->getName(), $settings['jobs'][$job]['niveaux'][$lvl + 1]['recompense']['commande']);
                            Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $commande);
                    }
                }
                return;
            }
            $config->setNested($player.".".$job."-exp", $exp + $nbr);
            $config->save();
        }
    }
}