<?php

namespace LogikSuite\Translation;

/**
 * Translation module
 *
 * @copyright 2021 LogikSuite
 */
class TranslationModule
{

    /**
     * The base path of the project using the translation module (a.k.a. your laravel project)
     *
     * @var [string]
     */
    protected static $_sProjectRootPath = null;

    /**
     * Array of translation keys / values
     *
     * @var array
     */
    protected static $_aTranslations = [];

    /**
     * Set project root path that we will use for configs and translations
     *
     * @param [string] $sRootPath
     * @return void
     */
    public static function setProjectRootPath($sRootPath)
    {
        static::$_sProjectRootPath = $sRootPath;
    }

    /**
     * Returns project root path
     *
     * @return string
     */
    public static function getProjectRootPath()
    {
        return static::$_sProjectRootPath;
    }

    /**
     * Returns module information, used by build project
     *
     * @param [array] $aOptions
     * @return array Module information
     */
    public static function getModuleInfo($aOptions): array
    {
        // Set module options
        foreach ($aOptions as $sKey => $mValue) {
            call_user_func(TranslationModule::class . "::set" . $sKey, $mValue);
        }

        return [
            "Name" => "Translation",
            "Description" => "Translation management module",
            "Class" => TranslationModule::class,
            "Languages" => static::getLanguages()
        ];
    }

    /**
     * Returns languages supported by project, reads folders in resources/lang
     *
     * @return array List of configured languages locales
     */
    public static function getLanguages()
    {
        $aLanguages = [];
        if (static::getProjectRootPath() != null) {
            $sBaseLanguagePath = static::getProjectRootPath() . "/resources/lang";
        } else {
            $sBaseLanguagePath = resource_path("lang");
        }

        $aFiles = scandir($sBaseLanguagePath);
        foreach ($aFiles as $sFileName) {
            if ($sFileName != "." && $sFileName != ".." && is_dir($sBaseLanguagePath . "/" . $sFileName)) {
                $aLanguages[$sFileName] = [
                    "Code" => $sFileName,
                    "Path" => $sBaseLanguagePath . "/" . $sFileName,
                    "LanguageFiles" => static::getLanguageFiles($sFileName),
                ];
            }
        }

        return $aLanguages;
    }

    /**
     * Returns list of language files, reads all files ending by .php in resources/lang and sub folders
     * returns complete list found in all locales
     *
     * @param [string] $sLocale
     * @return array List of language files for a specific locale
     */
    public static function getLanguageFiles($sLocale)
    {

        $aLanguageFiles = [];
        if (static::getProjectRootPath() != null) {
            $sBaseLanguagePath = static::getProjectRootPath() . "/resources/lang/" . $sLocale;
        } else {
            $sBaseLanguagePath = resource_path("lang/" . $sLocale);
        }

        $aFiles = scandir($sBaseLanguagePath);
        foreach ($aFiles as $sFileName) {
            if ($sFileName != "." && $sFileName != ".." && !is_dir($sBaseLanguagePath . "/" . $sFileName)) {
                if (substr($sFileName, -4) == ".php") {
                    $sDisplayName = str_replace(".php", "", $sFileName);
                    $aLanguageFiles[$sDisplayName] = [
                        "Name" => $sDisplayName,
                        "Path" => $sBaseLanguagePath . "/" . $sFileName
                    ];
                }
            }
        }

        return $aLanguageFiles;
    }

    /**
     * Returns list of all keys in a specific language file
     *
     * @param [string] $sLanguageFileName
     * @return void
     */
    public static function getLanguageFileKeys($sLanguageFileName)
    {

        $aKeys = [];

        $aLanguages = static::getLanguages();
        foreach ($aLanguages as $aLanguage) {
            foreach ($aLanguage["LanguageFiles"] as $aFile) {
                if ($aFile["Name"] == $sLanguageFileName) {
                    if (file_exists($aFile["Path"])) {
                        $aFileKeys = include($aFile["Path"]);
                        $aKeys = array_merge($aFileKeys, $aKeys);
                    }
                }
            }
        }

        return $aKeys;
    }


    /**
     * Returns a translation value in a given file and locale
     *
     * @param [string] $sKey
     * @param [string] $sFile
     * @param [string] $sLocale
     * @return string
     */
    public static function get($sKey, $sFile, $sLocale)
    {
        if (!array_key_exists($sFile . "__" . $sLocale, static::$_aTranslations)) {
            // Load translation file

            $aLanguages = static::getLanguages();

            if (array_key_exists($sLocale, $aLanguages)) {
                foreach ($aLanguages[$sLocale]["LanguageFiles"] as $aFile) {
                    if ($aFile["Name"] == $sFile) {
                        if (file_exists($aFile["Path"])) {
                            static::$_aTranslations[$sFile . "__" . $sLocale] = include($aFile["Path"]);
                        }
                    }
                }
            }
        }

        if (!array_key_exists($sFile . "__" . $sLocale, static::$_aTranslations)) {
            return null;
        } elseif (!array_key_exists($sKey, static::$_aTranslations[$sFile . "__" . $sLocale])) {
            return null;
        }

        return static::$_aTranslations[$sFile . "__" . $sLocale][$sKey];
    }

    /**
     * Adds a locale to project, create a folder in resources/lang
     *
     * @param [string] $sLocale
     * @return void
     */
    public static function addLocale($sLocale)
    {

        if (static::getProjectRootPath() != null) {
            $sBaseLanguagePath = static::getProjectRootPath() . "/resources/lang";
        } else {
            $sBaseLanguagePath = resource_path("lang");
        }

        if (!file_exists($sBaseLanguagePath . "/" . $sLocale)) {
            mkdir($sBaseLanguagePath . "/" . $sLocale);
        }
    }

    /**
     * Removes a locale in project, deletes folder and all child items in resources/lang/{locale}
     *
     * @param [string] $sLocale
     * @return void
     */
    public static function removeLocale($sLocale)
    {

        if (static::getProjectRootPath() != null) {
            $sBaseLanguagePath = static::getProjectRootPath() . "/resources/lang";
        } else {
            $sBaseLanguagePath = resource_path("lang");
        }

        if (file_exists($sBaseLanguagePath . "/" . $sLocale)) {
            static::removeDirectory($sBaseLanguagePath . "/" . $sLocale);
        }
    }

    /**
     * Delete a directory and all files
     *
     * @param [string] $path
     * @return void
     */
    protected static function removeDirectory($path)
    {

        // Emnpty path exit
        if (trim($path) == "") {
            return;
        }

        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? static::removeDirectory($file) : unlink($file);
        }
        rmdir($path);
    }

    /**
     * Calls Google translate on a string for a specific locale
     *
     * @param [string] $sText
     * @param [string] $sTargetLocale
     * @return void
     */
    public static function translate($sText, $sTargetLocale)
    {

        $oTranslate = new GoogleTranslate();
        $aResult = $oTranslate->translate($sText, $sTargetLocale);
        return $aResult['text'];
    }

    /**
     * Saves laravel translation file in resources/lang/...
     *
     * @param [string] $sFile
     * @param [string] $sLocale
     * @param array $aKeys
     * @return void
     */
    public static function saveTranslationFile($sFile, $sLocale, $aKeys = [])
    {
        if (count($aKeys) == 0) {
            return;
        }

        if (static::getProjectRootPath() != null) {
            $sLanguageFileName = static::getProjectRootPath() . "/resources/lang/" . $sLocale . "/" . $sFile . ".php";
        } else {
            $sLanguageFileName = resource_path("lang/" . $sLocale . "/" . $sFile . ".php");
        }


        TranslationFile::saveTranslationFile($sLanguageFileName, $aKeys);
    }
}
