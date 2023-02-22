<?php

namespace PralexJob\Manager;

use PralexJob\Main;

class SettingsManager
{
    public static function getTitleJobForm() : string
    {
        return Main::getInstance()->getSettings()->get("job-title");
    }

    public static function getJobTitleMenu(string $job) : string
    {
        $all = Main::getInstance()->getSettings()->getAll();
        return $all['jobs'][$job][$job."-title"];
    }
}