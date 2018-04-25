<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2014 Leo Feyer
 *
 * @package     Trilobit
 * @author      trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license     LGPL-3.0-or-later
 * @copyright   trilobit GmbH
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;
use Contao\Date;
use Contao\Environment;
use Contao\Input;
use Contao\File;
use Contao\Database;

/**
 * Class JsonFileGenerator
 * @package Trilobit\FormvalidationBundle
 */
class JsonFileGenerator
{

    /**
     * @param $arrElements
     * @param $formId
     * @throws \Exception
     */
    public function createJsonFile($arrElements, $formId)
    {
        // prepare validation file content
        $liveValidationValue = 'var trilobit_liveValidation;'
                             . 'if (!trilobit_liveValidation) { trilobit_liveValidation = new Array(); }'
                             . 'trilobit_liveValidation.push('
                             . $this->createJson($formId, $arrElements)
                             . ');'
                             ;
        /*
        $liveValidationValue = 'var trilobit_liveValidation = trilobit_liveValidation || [];'
                             . 'if (!trilobit_liveValidation) { trilobit_liveValidation = new Array(); }'
                             . 'trilobit_liveValidation.push('
                             . $this->createJson($formId, $arrElements)
                             . ');'
                             ;
        */

        // get checksum
        $strChecksum = md5($liveValidationValue);

        // prepare path and filename
        $strValidationFile = 'assets/'
            . 'js/'
            . 'lv_' . $formId
            . '_' . $strChecksum
            . '.js'
        ;

        // write validation file
        if (!file_exists(TL_ROOT . '/' . $strValidationFile))
        {
            $objFile = new File($strValidationFile);
            $objFile->write($liveValidationValue);
            $objFile->close();
        }

        // include JavaScripts
        if (self::getBELoginStatus())
        {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/trilobitformvalidation/js/livevalidation_standalone.js';
        }
        else
        {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/trilobitformvalidation/js/livevalidation_standalone.compressed.js';
        }

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/trilobitformvalidation/js/trilobit_livevalidation.js';

        if (!Config::get('trilobitformvalidationDisableDefaultCss'))
        {
            $GLOBALS['TL_CSS'][] = 'bundles/trilobitformvalidation/css/trilobit_livevalidation.css';
        }

        // Include JavaScripts as last JS
        if (!Config::get('trilobitformvalidationDisableHeadJs'))
        {
            $GLOBALS['TL_HEAD']['trilobitformvalidation_f' . $formId] = '<script type="text/javascript" src="' . $strValidationFile . '"></script>';
        }
        else
        {
            $GLOBALS['TL_FORMVALIDATION']['FORMS'][$formId] = $liveValidationValue;
        }

    }

    /**
     *
     */
    protected function createSubmittedTag()
    {
        $GLOBALS['TL_HEAD'][] = '<script type="text/javascript">var trilobitFormSubmitted = true;</script>';
    }

    /**
     * @param $formId
     * @param $arrElements
     * @return string
     * @throws \Exception
     */
    protected function createJson($formId, $arrElements)
    {
        // exclution for alphanumeric check
        $alnumExtend = array('#', '&', '(', ')', '/', '>', '<', '=');

        // empty value in case of a mandatory select
        $select = array('');

        // Regex-Pattern
        $regexAlpha = '/^[a-z ._-]+$/i';
        $regexAlnum = '/^[a-z0-9 ._-]+$/i';
        $regexPhone = '/^(\+|\()?(\d+[ \+\(\)\/-]*)+$/';
        $regexUrl   = '/^[a-zA-Z0-9\.\+\/\?#%:,;\{\}\(\)\[\]@&=~_-]*$/';

        //$regexEmail = '/^(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)?(\w+[!#\$%&\'\*\+\-\/=\?^_`\{\|\}~]*)+@\w+([_\.-]*\w+)*\.[a-z]{2,6}$/i';

        //$regexEmail = '/^(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)'
        //            .  '?(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)+'
        //            .  '?(\w+[!#\$%&\'\*\+\-\/=\?^_`\{\|\}~]*)+'
        //            .  '@'
        //            .  '\w+([_\.-]*\w+)*\.[a-z]{2,6}$/i'
        //            ;

        // Jira HWP-41
        // 2014-11-18, 15:40
        $regexEmail = '/^(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)?(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)+?(\w+[!#\$%&\'\*\+\-\/=\?^_`\{\|\}~]*)+@\w[\w_\.-]+\.[a-z]{2,}$/i';

        // Load Contao date-regex
        $objDate    = new Date();
        $regexDate  = $objDate->getRegexp($GLOBALS['TL_CONFIG']['dateFormat']);
        $regexDatim = $objDate->getRegexp($GLOBALS['TL_CONFIG']['datimFormat']);
        $regexTime  = $objDate->getRegexp($GLOBALS['TL_CONFIG']['timeFormat']);

        // Remove PHP subpattern
        $regexDate  = preg_replace('/\?P\<.\>/', '', $regexDate);
        $regexDatim = preg_replace('/\?P\<.\>/', '', $regexDatim);
        $regexTime  = preg_replace('/\?P\<.\>/', '', $regexTime);

        // add delimiter
        $regexDate  = '/^' . $regexDate . '$/';
        $regexDatim = '/^' . $regexDatim . '$/';
        $regexTime  = '/^' . $regexTime . '$/';

        $arrValidation = array();

        foreach ($arrElements as $key => $value)
        {
            $arrField = array();

            if (   $value['mandatory']
                && $value['mandatory'] == 1
                && $value['type'] != 'checkbox'
                && $value['type'] != 'radio'
            )
            {
                $arrField[0]['validationType'] = 'Validate.Presence';
                $arrField[0]['validationAttributes']['failureMessage'] = $value['mandatoryMessage'];
            }

            if (   $value['type'] == 'checkbox'
                || $value['type'] == 'radio'
            )
            {
                if (   $value['mandatory']
                    && $value['mandatory'] == 1
                )
                {
                    $arrField[1]['validationType'] = $value['type'];
                    $arrField[1]['validationAttributes']['failureMessage'] = $value['mandatoryMessage'];
                    $arrField[1]['validationAttributes']['mandatory'] = 1;
                }
            }
            else if (   $value['type'] == 'digit'
                     || $value['type'] == 'captcha'
            )
            {
                $arrField[1]['validationAttributes']['notANumberMessage'] = $value['failureMessage'];
            }
            else if (   $value['type'] == 'alpha'
                     || $value['type'] == 'alnum'
                     || $value['type'] == 'date'
                     || $value['type'] == 'datim'
                     || $value['type'] == 'time'
                     || $value['type'] == 'phone'
                     || $value['type'] == 'email'
                     || $value['type'] == 'url'
                     || $value['type'] == 'extnd'
                     || $value['type'] == 'passwordMatch'
            )
            {
                $arrField[1]['validationAttributes']['failureMessage'] = $value['failureMessage'];
            }

            // set different config for each formcheck-type
            switch($value['type'])
            {
                // Fieldtype Digits
                // only digits allowed
                case 'digit':
                    $arrField[1]['validationType'] = 'Validate.Numericality';
                    break;

                // Fieldtype Alphabetic
                // only alphabetic chars allowed
                case 'alpha':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexAlpha;
                    break;

                // Fieldtype Alphanumeric
                // digits and alphabetic chars
                case 'alnum':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexAlnum;
                    break;

                // Fieldtype Date
                // only a valid date is allowed
                // Format is set up in the backend settings
                case 'date':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexDate;
                    break;

                // Fieldtype Datim
                // Only a valid date and time are allowed
                // Format is set up in the backend settings
                case 'datim':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexDatim;
                    break;

                // Fieldtype Time
                // Only a valid time is allowed
                // Format is set up in the backend settings
                case 'time':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexTime;
                    break;

                // Fieldtype phone
                // Only a valid phonenumber
                case 'phone':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexPhone;
                    break;

                // Fieldtype Email
                // Needs a valid Email
                case 'email':
                    $arrField[1]['validationType'] = 'Validate.Email';
                    break;

                // Fieldtype Email
                // Needs a valid Email
                #case 'email':
                #    $currentField[1]['validationType'] = 'Validate.Format';
                #    $currentField[1]['validationAttributes']['pattern'] = $regexEmail;
                #    break;

                // Feldtyp URL
                // Only a valid URL is allowed
                case 'url':
                    $arrField[1]['validationType'] = 'Validate.Format';
                    $arrField[1]['validationAttributes']['pattern'] = $regexUrl;
                    break;

                // Feldtyp Extnd
                // All Chars are allowed.
                // Exclusion: # & ( ) / > = <
                case 'extnd':
                    $arrField[1]['validationType'] = 'Validate.Exclusion';
                    $arrField[1]['validationAttributes']['within'] = $alnumExtend;
                    $arrField[1]['validationAttributes']['partialMatch'] = true;
                    break;

                case 'passwordMatch':
                    if (preg_match("/(.*?)\_confirm/", $key, $treffer))
                    {
                        $arrField[1]['validationType'] = 'Validate.Confirmation';
                        $arrField[1]['validationAttributes']['match'] = $treffer[1];
                    }
                    break;

                case 'captcha':
                    $arrField[1]['validationType'] = 'Validate.Numericality';
                    break;

                case 'checkbox':
                    $arrField[1]['validationType'] = 'trilobitCheckboxValidation';
                    $arrField[1]['validationAttributes']['name'] = $value['name'];
                    $arrField[1]['validationAttributes']['elements'] = $value['elements'];
                    break;

                case 'radio':
                    $arrField[1]['validationType'] = 'trilobitRadioValidation';
                    $arrField[1]['validationAttributes']['name'] = $value['name'];
                    $arrField[1]['validationAttributes']['elements'] = $value['elements'];
                    break;
            }

            if ($value['minlength'])
            {
                $arrField[2]['validationType'] = 'Validate.Length';
                $arrField[2]['validationAttributes']['minimum'] = $value['minlength'];
                $arrField[2]['validationAttributes']['tooShortMessage'] = $value['minlengthMessage'];
            }

            // in case of a maxlength
            if ($value['maxlength'])
            {
                $arrField[2]['validationType'] = 'Validate.Length';
                $arrField[2]['validationAttributes']['maximum'] = $value['maxlength'];
                $arrField[2]['validationAttributes']['tooShortMessage'] = $value['maxlengthMessage'];
            }


            // add current field to validation config
            //$arrValidation[$key]['validations'] = $currentField;

            $arrValidation[] = array
            (
                'key'         => $key,
                'validations' => $arrField,
            );
        }

        if (Input::post('FORM_SUBMIT') == $formId)
        {
            $this->createSubmittedTag();
        }

        // Returns strin in JSON format
        return html_entity_decode(json_encode($arrValidation));
    }

    /**
     * @return bool
     */
    public function getBELoginStatus()
    {
        $strCookie = 'BE_USER_AUTH';

        $hash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? Environment::get('ip') : '') . $strCookie);

        // Validate the cookie hash
        if (Input::cookie($strCookie) == $hash)
        {
            // Try to find the session
            $objSession = Database::getInstance()
                ->prepare("SELECT * FROM tl_session WHERE hash=? AND name=?")
                ->limit(1)
                ->execute($hash, $strCookie);

            // Validate the session ID and timeout
            if (   $objSession->numRows
                && $objSession->sessionID == session_id()
                && (   $GLOBALS['TL_CONFIG']['disableIpCheck']
                    || $objSession->ip == Environment::get('ip')
                )
                && ($objSession->tstamp + $GLOBALS['TL_CONFIG']['sessionTimeout']) > time())
            {
                return true;
            }
        }

        return false;
    }
}
