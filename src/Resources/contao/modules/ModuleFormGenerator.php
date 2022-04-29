<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-formvalidation-bundle
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;
use Contao\Database;

/**
 * Class ModuleFormGenerator.
 */
class ModuleFormGenerator extends \Contao\Form
{
    /**
     * @return mixed
     */
    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        if (empty($_SESSION['FILES'])) {
            $_SESSION['FILES'] = [];
        }

        $strParentCompile = parent::compile();

        $formId = \strlen($this->formID) ? $this->formID : $this->id;

        $elements = [];

        $this->import('Database');

        $objFields = Database::getInstance()
            ->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible='' ORDER BY sorting")
            ->execute($formId);

        $objValidationHelper = new Helper();

        while ($objFields->next()) {
            if ('selectCountry' === $objFields->type
                || 'selectLanguage' === $objFields->type
                || 'selectDatabase' === $objFields->type
            ) {
                $objFields->type = 'select';
            }

            if ('submit' === $objFields->type
                || 'html' === $objFields->type
                || 'explanation' === $objFields->type
                || 'fieldsetStart' === $objFields->type
                || 'fieldsetStop' === $objFields->type
                || 'range' === $objFields->type
                || 'checkboxDatabase' === $objFields->type
                || 'radioDatabase' === $objFields->type
                || ('select' === $objFields->type && 1 === $objFields->multiple)
            ) {
                continue;
            }

            $strPrefix = '';

            if ('checkbox' === $objFields->type || 'radio' === $objFields->type) {
                $elements[$objFields->id]['type'] = $objFields->type;
                $elements[$objFields->id]['name'] = $objFields->name;

                foreach (deserialize($objFields->options, true) as $key => $value) {
                    $elements[$objFields->id]['elements'][$key] = $key;
                }
            } else {
                if ('select' === $objFields->type || 'upload' === $objFields->type) {
                    $objFields->rgxp = '';
                }

                $strPrefix = 'ctrl_';
                $elements[$strPrefix.$objFields->id]['type'] = '';

                if ('' !== $objFields->rgxp) {
                    $elements[$strPrefix.$objFields->id]['type'] = $objFields->rgxp;
                    $elements[$strPrefix.$objFields->id]['failureMessage'] = $objValidationHelper->getFailureMessage($strPrefix.$objFields->id, $objFields->rgxp);
                }
            }

            if ($objFields->mandatory) {
                $elements[$strPrefix.$objFields->id]['mandatory'] = 1;
                $elements[$strPrefix.$objFields->id]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($strPrefix.$objFields->id, $objFields->label);
            }

            if ('captcha' === $objFields->type) {
                $objFields->maxlength = 2;

                $elements[$strPrefix.$objFields->id]['type'] = 'digit';
                $elements[$strPrefix.$objFields->id]['mandatory'] = 1;
                $elements[$strPrefix.$objFields->id]['mandatoryMessage'] = ' ';
                $elements[$strPrefix.$objFields->id]['failureMessage'] = $objValidationHelper->getFailureMessage($strPrefix.$objFields->id, 'digit');
            }

            if ('password' === $objFields->type) {
                $minPasswordLength = Config::get('minPasswordLength');

                if ($objFields->minlength || $objFields->minlength < $minPasswordLength) {
                    $objFields->minlength = $minPasswordLength;
                }

                $elements[$strPrefix.$objFields->id.'_confirm']['type'] = 'passwordMatch';

                if (\array_key_exists('mandatory', $elements[$strPrefix.$objFields->id]) && 1 === $elements[$strPrefix.$objFields->id]['mandatory']) {
                    $elements[$strPrefix.$objFields->id.'_confirm']['mandatory'] = 1;
                    $elements[$strPrefix.$objFields->id.'_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($strPrefix.$objFields->id.'_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                }

                $elements[$strPrefix.$objFields->id.'_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage($strPrefix.$objFields->id.'_confirm', 'passwordMatch');
            }

            if ($objFields->maxlength) {
                $elements[$strPrefix.$objFields->id]['maxlength'] = $objFields->maxlength;
                $elements[$strPrefix.$objFields->id]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($strPrefix.$objFields->id, $objFields->label, $objFields->maxlength);
                $elements[$strPrefix.$objFields->id]['tooLongMessage'] = $objValidationHelper->getMaxlengthMessage($strPrefix.$objFields->id, $objFields->label, $objFields->maxlength);
            }

            if ($objFields->minlength) {
                $elements[$strPrefix.$objFields->id]['minlength'] = $objFields->minlength;
                $elements[$strPrefix.$objFields->id]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix.$objFields->id, $objFields->label, $objFields->minlength);
                //$elements[$strPrefix . $objFields->id]['tooShortMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->minlength);
            }

            //$elements[$strPrefix . $objFields->id]['tooLowMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->minlength);
            //$elements[$strPrefix . $objFields->id]['tooHighMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->minlength);
        }

        // creates new object of FileGenerator
        // submits config
        $objJsonGenerator = new JsonFileGenerator();
        $objJsonGenerator->createJsonFile($elements, 'tl_form_'.$formId);

        // return compiled parent class
        return $strParentCompile;
    }
}
