<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2014 Leo Feyer
 *
 * @package   trilobit
 * @author    trilobit GmbH <http://www.trilobit.de>
 * @license   LPGL
 * @copyright trilobit GmbH
 */

/**
 * Namespace
 */
namespace Trilobit\FormvalidationBundle;


/**
 * Class ModuleMember
 * @package Trilobit\FormvalidationBundle
 */
class ModuleMember extends \Contao\ModuleRegistration
{

    /**
     * @return string
     */
    public function generate()
    {
        $this->editable = deserialize($this->editable);

        // Return if there are not editable fields or if there is no logged in user
        if (   !is_array($this->editable)
            || empty($this->editable)
        )
        {
            return '';
        }

        return parent::generate();
    }


    /**
     *
     */
    protected function compile()
    {
        $strParentCompile = parent::compile();

        $formId = strlen($this->formID) ? $this->formID : $this->id;

        $elements = array();

        $objValidationHelper = new Helper();

        foreach ($this->editable as $field)
        {
            if (   $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'] == 'checkbox'
                || $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'] == 'checkboxWizard'

            )
            {
                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['mandatory'])
                {
                    $elements[$field]['type'] = 'checkbox';
                    $elements[$field]['name'] = $field;
                    $elements[$field]['mandatory'] = 1;

                    $elements[$field]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($field, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0]);

                    if (   $field == 'groups'
                        || $field == 'newsletter'
                    )
                    {
                        $this->import('Database');

                        $objSession = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?")
                            ->limit(1)
                            ->execute($formId);

                        while($objSession->next())
                        {
                            if ($field == 'groups')
                            {
                                foreach (unserialize($objSession->reg_groups) as $key => $value)
                                {
                                    $elements[$field]['elements'][$key] = $value;
                                }
                            }
                            else if ($field == 'newsletter')
                            {
                                foreach (unserialize($objSession->newsletters) as $key => $value)
                                {
                                    $elements[$field]['elements'][$key] = $value;
                                }
                            }
                        }
                    }

                    if (is_null($elements[$field]['elements']))
                    {
                        $elements[$field]['elements'][0] = 0;
                    }
                }
            }
            else
            {
                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['rgxp'])
                {
                    $elements['ctrl_' . $field]['type'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['rgxp'];
                    $elements['ctrl_' . $field]['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_' . $field, $elements['ctrl_' . $field]['type']);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'])
                {
                    $elements['ctrl_' . $field]['inputType'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['inputType'];
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['mandatory'])
                {
                    $elements['ctrl_' . $field]['mandatory'] = 1;
                    $elements['ctrl_' . $field]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_' . $field, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0]);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['minlength'])
                {
                    $elements['ctrl_' . $field]['minlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['minlength'];
                    $elements['ctrl_' . $field]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_' . $field, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements['ctrl_' . $field]['minlength']);
                }

                if ($GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['maxlength'])
                {
                    $elements['ctrl_' . $field]['maxlength'] = $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['eval']['maxlength'];
                    $elements['ctrl_' . $field]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage('ctrl_' . $field, $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['label'][0], $elements['ctrl_' . $field]['maxlength']);
                }

                if ($field == 'password')
                {
                    $elements['ctrl_password_confirm']['type'] = 'passwordMatch';
                    $elements['ctrl_password_confirm']['mandatory'] = 1;
                    $elements['ctrl_password_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                    $elements['ctrl_password_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_password_confirm', 'passwordMatch');
                }
            }
        }

        if ($this->disableCaptcha != '1')
        {
            $elements['ctrl_registration']['type'] = 'captcha';
            $elements['ctrl_registration']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_registration', 'digit');
            $elements['ctrl_registration']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_registration', $GLOBALS['TL_LANG']['MSC']['securityQuestion']);
            $elements['ctrl_registration']['mandatory'] = 1;
        }

        // creates new object of FileGenerator
        // submits config
        $fileGenerator = new TrilobitJsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_registration');

        return $strParentCompile;
    }
}
