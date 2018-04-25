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
     * @param $elements
     * @param $formId
     * @throws \Exception
     */
    public function createJsonFile($elements, $formId)
    {
        // prepare validation file content
        $liveValidationValue = 'var trilobit_liveValidation;'
                             . 'if (!trilobit_liveValidation) { trilobit_liveValidation = new Array(); }'
                             . 'trilobit_liveValidation.push('
                             . $this->createJson($formId, $elements)
                             . ');'
                             ;

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

        if (!Config::get('livevalidationDisableDefaultCss')
        )
        {
            $GLOBALS['TL_CSS'][] = 'bundles/trilobitformvalidation/css/trilobit_livevalidation.css';
        }

        // Include JavaScripts as last JS
        $GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="' . $strValidationFile . '"></script>';
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
     * @param $elements
     * @return string
     * @throws \Exception
     */
    protected function createJson($formId, $elements)
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

        foreach ($elements as $elementKey => $elementValue)
        {
            $currentField = array();

            if (   $elementValue['mandatory']
                && $elementValue['mandatory'] == 1
                && $elementValue['type'] != 'checkbox'
                && $elementValue['type'] != 'radio'
            )
            {
                $currentField[0]['validationType'] = 'Validate.Presence';
                $currentField[0]['validationAttributes']['failureMessage'] = $elementValue['mandatoryMessage'];
            }

            if (   $elementValue['type'] == 'checkbox'
                || $elementValue['type'] == 'radio'
            )
            {
                if (   $elementValue['mandatory']
                    && $elementValue['mandatory'] == 1
                )
                {
                    $currentField[1]['validationType'] = $elementValue['type'];
                    $currentField[1]['validationAttributes']['failureMessage'] = $elementValue['mandatoryMessage'];
                    $currentField[1]['validationAttributes']['mandatory'] = 1;
                }
            }
            else if (   $elementValue['type'] == 'digit'
                     || $elementValue['type'] == 'captcha'
            )
            {
                $currentField[1]['validationAttributes']['notANumberMessage'] = $elementValue['failureMessage'];
            }
            else if (   $elementValue['type'] == 'alpha'
                     || $elementValue['type'] == 'alnum'
                     || $elementValue['type'] == 'date'
                     || $elementValue['type'] == 'datim'
                     || $elementValue['type'] == 'time'
                     || $elementValue['type'] == 'phone'
                     || $elementValue['type'] == 'email'
                     || $elementValue['type'] == 'url'
                     || $elementValue['type'] == 'extnd'
                     || $elementValue['type'] == 'passwordMatch'
            )
            {
                $currentField[1]['validationAttributes']['failureMessage'] = $elementValue['failureMessage'];
            }

            // set different config for each formcheck-type
            switch( $elementValue['type'] )
            {
                // Fieldtype Digits
                // only digits allowed
                case 'digit':
                    $currentField[1]['validationType'] = 'Validate.Numericality';
                    break;

                // Fieldtype Alphabetic
                // only alphabetic chars allowed
                case 'alpha':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexAlpha;
                    break;

                // Fieldtype Alphanumeric
                // digits and alphabetic chars
                case 'alnum':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexAlnum;
                    break;

                // Fieldtype Date
                // only a valid date is allowed
                // Format is set up in the backend settings
                case 'date':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexDate;
                    break;

                // Fieldtype Datim
                // Only a valid date and time are allowed
                // Format is set up in the backend settings
                case 'datim':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexDatim;
                    break;

                // Fieldtype Time
                // Only a valid time is allowed
                // Format is set up in the backend settings
                case 'time':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexTime;
                    break;

                // Fieldtype phone
                // Only a valid phonenumber
                case 'phone':
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexPhone;
                    break;

                // Fieldtype Email
                // Needs a valid Email
                case 'email':
                    $currentField[1]['validationType'] = 'Validate.Email';
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
                    $currentField[1]['validationType'] = 'Validate.Format';
                    $currentField[1]['validationAttributes']['pattern'] = $regexUrl;
                    break;

                // Feldtyp Extnd
                // All Chars are allowed.
                // Exclusion: # & ( ) / > = <
                case 'extnd':
                    $currentField[1]['validationType'] = 'Validate.Exclusion';
                    $currentField[1]['validationAttributes']['within'] = $alnumExtend;
                    $currentField[1]['validationAttributes']['partialMatch'] = true;
                    break;

                case 'passwordMatch':
                    if (preg_match("/(.*?)\_confirm/", $elementKey, $treffer))
                    {
                        $currentField[1]['validationType'] = 'Validate.Confirmation';
                        $currentField[1]['validationAttributes']['match'] = $treffer[1];
                    }
                    break;

                case 'captcha':
                    $currentField[1]['validationType'] = 'Validate.Numericality';
                    break;

                case 'checkbox':
                    $currentField[1]['validationType'] = 'trilobitCheckboxValidation';
                    $currentField[1]['validationAttributes']['name'] = $elementValue['name'];
                    $currentField[1]['validationAttributes']['elements'] = $elementValue['elements'];
                    break;

                case 'radio':
                    $currentField[1]['validationType'] = 'trilobitRadioValidation';
                    $currentField[1]['validationAttributes']['name'] = $elementValue['name'];
                    $currentField[1]['validationAttributes']['elements'] = $elementValue['elements'];
                    break;
            }

            if ($elementValue['minlength'])
            {
                $currentField[2]['validationType'] = 'Validate.Length';
                $currentField[2]['validationAttributes']['minimum'] = $elementValue['minlength'];
                $currentField[2]['validationAttributes']['tooShortMessage'] = $elementValue['minlengthMessage'];
            }

            // in case of a maxlength
            if ($elementValue['maxlength'])
            {
                $currentField[2]['validationType'] = 'Validate.Length';
                $currentField[2]['validationAttributes']['maximum'] = $elementValue['maxlength'];
                $currentField[2]['validationAttributes']['tooShortMessage'] = $elementValue['maxlengthMessage'];
            }


            // add current field to validation config
            //$arrValidation[$elementKey]['validations'] = $currentField;

            $arrValidation[] = array
            (
                'key'         => $elementKey,
                'validations' => $currentField,
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
