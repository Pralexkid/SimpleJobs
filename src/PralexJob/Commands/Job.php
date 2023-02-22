<?php

namespace PralexJob\Commands;

use EasyUI\element\Button;
use EasyUI\element\Label;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use PralexJob\Main;
use PralexJob\Manager\PlayersManager;
use PralexJob\Manager\SettingsManager;

class Job extends Command
{
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player)
        {
            $config = Main::getInstance()->getSettings();
            $all = $config->getAll();
            $form = new SimpleForm(SettingsManager::getTitleJobForm());
            foreach ($all['jobs'] as $job => $value)
            {
                if ($value['enable'] === true)
                {
                    $form->addButton(new Button($value[$job."-name"], null, function (Player $player) use ($job, $value, $all) {
                        $form = new SimpleForm(SettingsManager::getJobTitleMenu($job));
                        $form->addButton(new Button("Stats", null, function (Player $pl) use ($job, $value, $all) {
                            $form = new CustomForm("Stats");
                            if (PlayersManager::isAtMaxLevel($pl->getName(), $job))
                            {
                                $content = str_replace(
                                    ["{lvl}","{exp}","{exp-manquant}"],
                                    [PlayersManager::getLevel($pl->getName(), $job), PlayersManager::getExp($pl->getName(), $job), "Niveau max"],
                                    $all['jobs'][$job]['stats']
                                );
                            } else
                            {
                                $content = str_replace(
                                    ["{lvl}","{exp}","{exp-manquant}"],
                                    [PlayersManager::getLevel($pl->getName(), $job), PlayersManager::getExp($pl->getName(), $job), PlayersManager::getExpManquant($pl->getName(), $job)],
                                    $all['jobs'][$job]['stats']
                                );
                            }
                            $form->addElement("label", new Label($content));
                            $pl->sendForm($form);
                        }));
                        $form->addButton(new Button("Recompenses", null, function (Player $pl) use ($job, $value, $all) {
                            $form = new SimpleForm("Niveaux :");
                            foreach ($all['jobs'][$job]['niveaux'] as $lvl => $value)
                            {
                                $form->addButton(new Button("Niveau " . $lvl, null, function (Player $player) use ($job, $lvl, $value, $all) {
                                    $form = new CustomForm("Recompense :");
                                    $form->addElement("label", new Label($all['jobs'][$job]['niveaux'][$lvl]['recompense']['label']));
                                    $player->sendForm($form);
                                }));
                            }
                            $pl->sendForm($form);
                        }));
                        $player->sendForm($form);
                    }));
                }
            }
            $sender->sendForm($form);
        }
    }
}