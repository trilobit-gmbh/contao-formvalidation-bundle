<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-formvalidation-bundle
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;
use Contao\System;

/**
 * Class ModulePersonalData.
 */
class ModulePersonalData extends \Contao\ModulePersonalData
{
    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        $strParentCompile = parent::compile();

        $formId = \strlen($this->formID) ? $this->formID : $this->id;
        $minPasswordLength = Config::get('minPasswordLength');

        $objValidationHelper = new Helper();

        $elements = [];

        foreach ($this->editable as $field) {
            if ('checkbox' === $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType']
                || 'checkboxWizard' === $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType']
            ) {
                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['mandatory']) {
                    $elements[$field]['type'] = 'checkbox';
                    $elements[$field]['name'] = $field;
                    $elements[$field]['mandatory'] = 1;

                    $elements[$field]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($field, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0]);

                    if ('groups' === $field
                        || 'newsletter' === $field
                    ) {
                        $this->import('Database');

                        $objSession = $this->Database->prepare('SELECT * FROM tl_module WHERE id=?')->limit(1)->execute($formId);

                        while ($objSession->next()) {
                            if ('groups' === $field) {
                                foreach (deserialize($objSession->reg_groups, true) as $key => $value) {
                                    $elements[$field]['elements'][$key] = $value;
                                }
                            } elseif ('newsletter' === $field) {
                                foreach (deserialize($objSession->newsletters, true) as $key => $value) {
                                    $elements[$field]['elements'][$key] = $value;
                                }
                            }
                        }
                    }

                    if (null === $elements[$field]['elements']) {
                        $elements[$field]['elements'][0] = 0;
                    }
                }
            } else {
                // Jira CONTAO-478
                // 2019-02-19
                $fieldId = 'ctrl_'.$field;

                if (version_compare(
                        System::getContainer()->getParameter('kernel.packages')['contao/core-bundle'],
                        '4.6.0'
                    ) >= 0
                ) {
                    // [Core] Append the module ID to the form field IDs to prevent duplicate IDs (see #1493), 25 Jun 2018
                    $fieldId .= '_'.$formId;
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['rgxp']) {
                    $elements[$fieldId]['type'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['rgxp'];
                    $elements[$fieldId]['failureMessage'] = $objValidationHelper->getFailureMessage($fieldId, $elements[$fieldId]['type']);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType']) {
                    $elements[$fieldId]['inputType'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'];
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['mandatory']) {
                    $elements[$fieldId]['mandatory'] = 1;
                    $elements[$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0]);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['minlength']) {
                    $elements[$fieldId]['minlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['minlength'];
                    $elements[$fieldId]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements[$fieldId]['minlength']);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['maxlength']) {
                    $elements[$fieldId]['maxlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['maxlength'];
                    $elements[$fieldId]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements[$fieldId]['maxlength']);
                }

                if ('password' === $field) {
                    $elements[$fieldId]['minlength'] = $minPasswordLength;
                    $elements[$fieldId]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);

                    $elements[$fieldId.'_confirm']['type'] = 'passwordMatch';
                    $elements[$fieldId.'_confirm']['mandatory'] = 1;
                    $elements[$fieldId.'_confirm']['minlength'] = $minPasswordLength;
                    $elements[$fieldId.'_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($fieldId.'_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                    $elements[$fieldId.'_confirm']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);
                    $elements[$fieldId.'_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage($fieldId.'_confirm', 'passwordMatch');
                }
            }
        }

        if ('1' !== $this->disableCaptcha) {
            $elements['ctrl_registration']['type'] = 'captcha';
            $elements['ctrl_registration']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_registration', 'digit');
            $elements['ctrl_registration']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_registration', $GLOBALS['TL_LANG']['MSC']['securityQuestion']);
            $elements['ctrl_registration']['mandatory'] = 1;
        }

        // creates new object of FileGenerator
        // submits config
        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_personal_data_'.$formId);

        return $strParentCompile;
    }
}
