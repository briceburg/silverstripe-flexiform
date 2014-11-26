<?php

/**
 * Utility functions for the FlexiForms module.
 */
class FlexiFormUtil
{

    public static function include_requirements()
    {
        $moduleDir = self::get_module_dir();
        Requirements::css($moduleDir . '/css/flexiforms.css');
    }

    public static function get_module_dir()
    {
        return basename(dirname(dirname(__DIR__)));
    }
}
