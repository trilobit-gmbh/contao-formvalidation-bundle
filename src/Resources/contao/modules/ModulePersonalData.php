<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;
use Contao\StringUtil;

class ModulePersonalData extends \Contao\ModulePersonalData
{
    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        parent::compile();

        $formId = null !== $this->formID && \strlen($this->formID) ? $this->formID : $this->id;

        $objValidationHelper = new Helper();

        $elements = [];

        foreach ($this->editable as $field) {
            $inputType = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'];

            if (empty($inputType)) {
                continue;
            }

            $fieldId = $field;

            if ('checkboxWizard' === $inputType) {
                $inputType = 'checkbox';
            }

            if ('checkbox' !== $inputType && 'radio' !== $inputType) {
                $fieldId = 'ctrl_'.$field;
            }

            $fieldId .= '_'.$formId;

            $elements[$fieldId]['inputType'] = $inputType;

            if (\array_key_exists('rgxp', $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval'])) {
                $elements[$fieldId]['type'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['rgxp'];
                $elements[$fieldId]['failureMessage'] = $objValidationHelper->getFailureMessage($fieldId, $elements[$fieldId]['type']);
            }

            if (\array_key_exists('mandatory', $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval'])) {
                $elements[$fieldId]['mandatory'] = 1;
                $elements[$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0]);
            }

            if (\array_key_exists('minlength', $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval'])) {
                $elements[$fieldId]['minlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['minlength'];
                $elements[$fieldId]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements[$fieldId]['minlength']);
            }

            if (\array_key_exists('maxlength', $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval'])) {
                $elements[$fieldId]['maxlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['maxlength'];
                $elements[$fieldId]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($fieldId, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements[$fieldId]['maxlength']);
            }

            if ('password' === $field) {
                $minPasswordLength = Config::get('minPasswordLength');

                $elements[$fieldId]['minlength'] = $minPasswordLength;
                $elements[$fieldId]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);

                $elements[$fieldId.'_confirm']['type'] = 'passwordMatch';
                $elements[$fieldId.'_confirm']['mandatory'] = 1;
                $elements[$fieldId.'_confirm']['minlength'] = $minPasswordLength;
                $elements[$fieldId.'_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($fieldId.'_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                $elements[$fieldId.'_confirm']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);
                $elements[$fieldId.'_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage($fieldId.'_confirm', 'passwordMatch');
            }

            if ('checkbox' === $inputType || 'radio' === $inputType) {
                $elements[$fieldId]['type'] = $inputType;
                $elements[$fieldId]['name'] = $field;

                if ('groups' === $field || 'newsletter' === $field) {
                    $this->import('Database');

                    $objSession = $this->Database->prepare('SELECT * FROM tl_module WHERE id=?')
                        ->limit(1)
                        ->execute($formId)
                    ;

                    while ($objSession->next()) {
                        if ('groups' === $field) {
                            foreach (StringUtil::deserialize($objSession->reg_groups, true) as $key => $value) {
                                $elements[$fieldId]['elements'][$key] = $value;
                            }
                        } elseif ('newsletter' === $field) {
                            foreach (StringUtil::deserialize($objSession->newsletters, true) as $key => $value) {
                                $elements[$fieldId]['elements'][$key] = $value;
                            }
                        }
                    }
                } elseif ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['options']) {
                    foreach (array_keys($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['options']) as $key => $value) {
                        $elements[$fieldId]['elements'][] = $key;
                    }
                }

                if (empty($elements[$fieldId]['elements'])) {
                    $elements[$fieldId]['elements'][0] = 0;
                }
            }
        }

        if ('1' !== $this->disableCaptcha) {
            $maxCaptchaLength = 2;

            $fieldId = 'ctrl_registration';

            $elements[$fieldId]['type'] = 'digit';
            $elements[$fieldId]['mandatory'] = 1;
            $elements[$fieldId]['mandatoryMessage'] = ' ';
            $elements[$fieldId]['failureMessage'] = $objValidationHelper->getFailureMessage($fieldId, 'digit');
            $elements[$fieldId]['maxlength'] = $maxCaptchaLength;
            $elements[$fieldId]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($fieldId, $GLOBALS['TL_LANG']['MSC']['securityQuestion'], $maxCaptchaLength);
            $elements[$fieldId]['tooLongMessage'] = $objValidationHelper->getMaxlengthMessage($fieldId, $GLOBALS['TL_LANG']['MSC']['securityQuestion'], $maxCaptchaLength);
        }

        // creates new object of FileGenerator
        // submits config
        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_registration_'.$formId);
    }
}
