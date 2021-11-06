<?php

namespace LogikSuite\Translation;

/**
 * Laravel translation file
 *
 * @copyright 2021 LogikSuite
 */
class TranslationFile
{

    /**
     * Saves translation keys using the laravel translation file format

     * The aKeys parameter holds a list of key pair translation keys to update,
     * the function will load current file values and update values using aKeys.
     *
     * @param [string] $sFile The name of the translation file on the hard drive
     * @param [array] $aKeys Arrays of keys to update
     * @return void
     */
    public static function saveTranslationFile($sFile, $aKeys)
    {
        $aCurrentValue = [];
        if (file_exists($sFile)) {
            $aCurrentValue = include($sFile);
        }

        // Replace current values with new values
        foreach ($aKeys as $sKey => $sValue) {
            $aCurrentValue[$sKey] = $sValue;
        }

        ksort($aCurrentValue);

        $s = "<?php" . PHP_EOL . PHP_EOL;
        $s .= " return [" . PHP_EOL . PHP_EOL;

        foreach ($aCurrentValue as $sKey => $sValue) {
            $s .= "     '" . $sKey . "' => '" . addslashes($sValue) . "'," . PHP_EOL;
        }

        $s .= PHP_EOL;
        $s .= " ];" . PHP_EOL;

        file_put_contents($sFile, $s);
    }
}
