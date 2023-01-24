<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;
use Contao\Date;
use Contao\File;
use Contao\Input;
use Contao\System;

/**
 * Class JsonFileGenerator.
 */
class JsonFileGenerator
{
    /**
     * @param $elements
     * @param $formId
     *
     * @throws \Exception
     */
    public function createJsonFile($elements, $formId)
    {
        // prepare validation file content
        $liveValidationValue = 'var trilobit_liveValidation;'."\n"
                             .'if (!trilobit_liveValidation) { trilobit_liveValidation = new Array(); }'."\n"
                             .'trilobit_liveValidation.push('."\n"
                             .$this->createJson($formId, $elements)
                             .');'."\n"
                             ;

        // get checksum
        $strChecksum = md5($liveValidationValue);

        // prepare path and filename
        $strValidationFile = 'assets/'
            .'js/'
            .'lv_'.$formId
            .'_'.$strChecksum
            .'.js'
        ;

        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        // write validation file
        if (!file_exists($rootDir.'/'.$strValidationFile)) {
            $objFile = new File($strValidationFile);
            $objFile->write($liveValidationValue);
            $objFile->close();
        }

        // include JavaScripts
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/trilobitformvalidation/js/livevalidation_standalone.compressed.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/trilobitformvalidation/js/trilobit_livevalidation.js';

        // include validation css
        if (!Config::get('livevalidationDisableDefaultCss')) {
            $GLOBALS['TL_CSS'][] = 'bundles/trilobitformvalidation/css/trilobit_livevalidation.css';
        }

        // include validation json
        if (!Config::get('livevalidationDisableHeadJs')) {
            $GLOBALS['TL_HEAD']['FORMVALIDATION_'.$formId] = '<script src="'.$strValidationFile.'"></script>';
        } else {
            $GLOBALS['TL_FORMVALIDATION']['FORMS'][$formId] = $this->createJson($formId, $elements);
        }
    }

    protected function createSubmittedTag()
    {
        $GLOBALS['TL_HEAD'][] = '<script>var trilobitFormSubmitted = true;</script>';
    }

    /**
     * @param $formId
     * @param $elements
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function createJson($formId, $elements)
    {
        // exclution for alphanumeric check
        $alnumExtend = ['#', '&', '(', ')', '/', '>', '<', '='];

        // empty value in case of a mandatory select
        $select = [''];

        // Regex-Pattern
        $regexAlpha = '/^[\p{L} .-]+$/u'; // Core; see: vendor/contao/core-bundle/src/Resources/contao/library/Contao/Validator.php
        $regexAlnum = '/^[\w\p{L} .-]+$/u'; // Core; see: vendor/contao/core-bundle/src/Resources/contao/library/Contao/Validator.php

        $regexPhone = '/^(\+|\()?(\d+[ \+\(\)\/-]*)+$/';

        // Jira CONTAO-478
        // 2019-02-19
        $regexUrl = '/^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/';

        // Jira HWP-41
        // 2014-11-18
        $regexEmail = '/^(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)?(\w+[!#\$%&\'\*\+\-\/=\?^_`\.\{\|\}~]*)+?(\w+[!#\$%&\'\*\+\-\/=\?^_`\{\|\}~]*)+@\w[\w_\.-]+\.[a-z]{2,}$/i';

        // Load Contao date-regex
        $objDate = new Date();

        $regexDate = $objDate->getRegexp(Date::getNumericDateFormat());
        $regexDatim = $objDate->getRegexp(Date::getNumericDatimFormat());
        $regexTime = $objDate->getRegexp(Date::getNumericTimeFormat());

        // Remove PHP subpattern
        $regexDate = preg_replace('/\?P\<.\>/', '', $regexDate);
        $regexDatim = preg_replace('/\?P\<.\>/', '', $regexDatim);
        $regexTime = preg_replace('/\?P\<.\>/', '', $regexTime);

        // add delimiter
        $regexDate = '/^'.$regexDate.'$/';
        $regexDatim = '/^'.$regexDatim.'$/';
        $regexTime = '/^'.$regexTime.'$/';

        $arrValidation = [];

        foreach ($elements as $elementKey => $elementValue) {
            $currentField = [];

            if (!\array_key_exists('type', $elementValue)) {
                continue;
            }

            if (\array_key_exists('mandatory', $elementValue)
                && 1 === $elementValue['mandatory']
                && 'checkbox' !== $elementValue['type']
                && 'radio' !== $elementValue['type']
            ) {
                $currentField[0]['validationType'] = 'Validate.Presence';
                $currentField[0]['validationAttributes']['failureMessage'] = $elementValue['mandatoryMessage'];
            }

            if ('checkbox' === $elementValue['type']
                || 'radio' === $elementValue['type']
            ) {
                if (\array_key_exists('mandatory', $elementValue) && 1 === $elementValue['mandatory']
                ) {
                    $currentField[1]['validationType'] = $elementValue['type'];
                    $currentField[1]['validationAttributes']['failureMessage'] = $elementValue['mandatoryMessage'];
                    $currentField[1]['validationAttributes']['mandatory'] = 1;
                }
            } elseif ('digit' === $elementValue['type']
                     || 'captcha' === $elementValue['type']
            ) {
                $currentField[1]['validationAttributes']['notANumberMessage'] = $elementValue['failureMessage'];
            } elseif ('alpha' === $elementValue['type']
                     || 'alnum' === $elementValue['type']
                     || 'date' === $elementValue['type']
                     || 'datim' === $elementValue['type']
                     || 'time' === $elementValue['type']
                     || 'phone' === $elementValue['type']
                     || 'email' === $elementValue['type']
                     || 'url' === $elementValue['type']
                     || 'extnd' === $elementValue['type']
                     || 'passwordMatch' === $elementValue['type']
            ) {
                $currentField[1]['validationAttributes']['failureMessage'] = $elementValue['failureMessage'];
            }

            // set different config for each formcheck-type
            switch ($elementValue['type']) {
                // Fieldtype Digits
                // only digits allowed
                case 'captcha':
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
                //case 'email':
                //    $currentField[1]['validationType'] = 'Validate.Format';
                //    $currentField[1]['validationAttributes']['pattern'] = $regexEmail;
                //    break;

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
                    if (preg_match("/(.*?)\_confirm/", $elementKey, $treffer)) {
                        $currentField[1]['validationType'] = 'Validate.Confirmation';
                        $currentField[1]['validationAttributes']['match'] = $treffer[1];
                    }
                    break;
                case 'checkbox':
                    $currentField[1]['validationType'] = 'trilobitCheckboxValidation';
                    if (\array_key_exists('name', $elementValue)) {
                        $currentField[1]['validationAttributes']['name'] = $elementValue['name'];
                    }
                    $currentField[1]['validationAttributes']['elements'] = $elementValue['elements'];
                    break;
                case 'radio':
                    $currentField[1]['validationType'] = 'trilobitRadioValidation';
                    if (\array_key_exists('name', $elementValue)) {
                        $currentField[1]['validationAttributes']['name'] = $elementValue['name'];
                    }
                    $currentField[1]['validationAttributes']['elements'] = $elementValue['elements'];
                    break;
            }

            if (\array_key_exists('minlength', $elementValue)) {
                $currentField[2]['validationType'] = 'Validate.Length';
                $currentField[2]['validationAttributes']['minimum'] = $elementValue['minlength'];
                $currentField[2]['validationAttributes']['tooShortMessage'] = $elementValue['minlengthMessage'];
            }

            // in case of a maxlength
            if (\array_key_exists('maxlength', $elementValue)) {
                $currentField[2]['validationType'] = 'Validate.Length';
                $currentField[2]['validationAttributes']['maximum'] = $elementValue['maxlength'];
                $currentField[2]['validationAttributes']['tooLongMessage'] = $elementValue['maxlengthMessage'];
            }

            // add current field to validation config
            //$arrValidation[$elementKey]['validations'] = $currentField;

            $arrValidation[] = [
                'key' => $elementKey,
                'validations' => $currentField,
            ];
        }

        if (Input::post('FORM_SUBMIT') === $formId) {
            $this->createSubmittedTag();
        }

        // Returns strin in JSON format
        return html_entity_decode(json_encode($arrValidation));
    }
}
