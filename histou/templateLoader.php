<?php
/**
Loads Templates from files.
PHP version 5
@category Loader
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

require_once 'histou/template.php';

/**
Loads Templates from files.
PHP version 5
@category Loader
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class TemplateLoader
{
    /**
    Creates a Template from file.
    @param string $filename foldername.
    @return object.
    **/
    public static function loadTemplate($filename)
    {
        if (static::endswith($filename, '.php')) {
            return TemplateLoader::_loadPHPTemplates($filename);
        } elseif (static::endswith($filename, '.simple')) {
            return TemplateLoader::_loadSimpleTemplates($filename);
        }
    }

    /**
    Creates a Basic Template.
    @param string $filename foldername.
    @return object.
    **/
    private static function _loadPHPTemplates($filename)
    {
        if (!static::isFileValidPHP($filename)) {
            return null;
        }
        include $filename;
        return new Template($filename, $rule, $genTemplate);
    }

    /**
    Creates a Simple Template.
    @param string $filename foldername.
    @return object.
    **/
    private static function _loadSimpleTemplates($filename)
    {
        return new SimpleTemplate($filename);
    }

    /**
    Tests if a string ends with a given string
    @param string $stringToSearch string to search in.
    @param string $extension      string to search for.
    @return object.
    **/
    public static function endsWith($stringToSearch, $extension)
    {
        return $extension === "" ||
        (
        ($temp = strlen($stringToSearch) - strlen($extension)) >= 0
        && strpos($stringToSearch, $extension, $temp) !== false);
    }

    /**
    Uses the php -l command to test if a file contains valid PHP code.
    @param string $filePath path to the file to check.
    @return bool.
    **/
    public static function isFileValidPHP($filePath)
    {
        ob_start();
        system(PHP_COMMAND." -l $filePath", $returnCode);
        ob_end_clean();
        return $returnCode == 0;
    }
}
