<?php

namespace LogikSuite\Translation;

use Google\Cloud\Translate\V2\TranslateClient;

/**
 * Google translate API
 *
 * @copyright 2021 LogikSuite
 * @license MIT
 */
class GoogleTranslate
{

    /**
     * Google project ID
     *
     * @var [string]
     */
    protected $_sProjectID = null;

    /**
     * Google translation api instance
     *
     * @var [object]
     */
    protected $_oTranslator = null;

    /**
     * Class constructor
     *
     * Initializes Google translate API
     */
    public function __construct()
    {

        /**
         * Using google translate requires a project and some credentials info,
         *
         * This information is usefull only in the logiksuite/build project, the google api will not be called
         * in your production environment.
         *
         * Configuration file must be created in build project /config/logiksuite/translation.php
         *
         */
        $this->_sProjectID = config('logiksuite.translation.google_translate.project_id');
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . config('logiksuite.translation.google_translate.credentials'));

        $this->_oTranslator = new TranslateClient([
            'projectId' => $this->_sProjectID
        ]);
    }

    /**
     * Translate a text using target locale
     *
     * @param [string] $sText Source text to translate
     * @param [string] $sTarget Target locale
     * @return void
     */
    public function translate($sText, $sTarget)
    {

        $aTranslation = $this->_oTranslator->translate($sText, [
            'target' => $sTarget
        ]);

        return $aTranslation;
    }
}
