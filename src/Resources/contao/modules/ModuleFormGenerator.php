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

use Contao\Database;
use Contao\StringUtil;

/**
 * Class ModuleFormGenerator
 * @package Trilobit\FormvalidationBundle
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

    /**
     * @return mixed
     */
    protected function compile()
    {
        // compiles parent class, so all variables are set
        $strParentCompile = parent::compile();

        $this->import('Database');

        // getting formId
        $formId = strlen($this->formID) ? $this->formID : $this->id;

        // Lade alle Formularfelder der jeweiligen Seite
        $objFields = Database::getInstance()
            ->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible!=1 ORDER BY sorting")
            ->execute($formId);

        $objValidationHelper = new Helper();

        $arrElements = array();

        while($objFields->next())
        {
            if (   $objFields->type == 'submit'
                || (   $objFields->type == 'select'
                    && $objFields->multiple == 1
                )
            )
            {
                continue;
            }

            $strPrefix = '';

            if (   $objFields->type == 'checkbox'
                || $objFields->type == 'radio'
            )
            {
                $arrElements[$objFields->id]['type'] = $objFields->type;
                $arrElements[$objFields->id]['name'] = $objFields->name;

                foreach (unserialize($objFields->options) as $key => $value)
                {
                    $arrElements[$objFields->id]['elements'][$key] = $key;
                }
            }
            else
            {
                if (   $objFields->type == 'select'
                    || $objFields->type == 'upload'
                )
                {
                    $objFields->rgxp = '';
                }

                $strPrefix = 'ctrl_';
                $arrElements[$strPrefix . $objFields->id]['type'] = '';

                if ($objFields->rgxp != '')
                {
                    $arrElements[$strPrefix . $objFields->id]['type'] = $objFields->rgxp;
                    $arrElements[$strPrefix . $objFields->id]['failureMessage'] = $objValidationHelper->getFailureMessage($strPrefix . $objFields->id, $objFields->rgxp);
                }
            }

            if ($objFields->mandatory)
            {
                $arrElements[$strPrefix . $objFields->id]['mandatory'] = 1;
                $arrElements[$strPrefix . $objFields->id]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($strPrefix . $objFields->id, $objFields->label);

            }

            if ($objFields->minlength)
            {
                $arrElements[$strPrefix . $objFields->id]['minlength'] = $objFields->minlength;
                $arrElements[$strPrefix . $objFields->id]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->minlength);
            }

            if ($objFields->maxlength)
            {
                $arrElements[$strPrefix . $objFields->id]['maxlength'] = $objFields->maxlength;
                $arrElements[$strPrefix . $objFields->id]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->maxlength);
            }

            if ($objFields->type == 'password')
            {
                $arrElements[$strPrefix . $objFields->id]['minlength'] = 8;

                $arrElements[$strPrefix . $objFields->id . '_confirm']['type'] = 'passwordMatch';

                if ($arrElements[$strPrefix . $objFields->id]['mandatory'] == 1)
                {
                    $arrElements[$strPrefix . $objFields->id . '_confirm']['mandatory'] = 1;
                    $arrElements[$strPrefix . $objFields->id . '_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($strPrefix . $objFields->id . '_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                }

                $arrElements[$strPrefix . $objFields->id . '_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage($strPrefix . $objFields->id . '_confirm', 'passwordMatch');
                $arrElements[$strPrefix . $objFields->id]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($strPrefix . $objFields->id, $objFields->label, $objFields->minlength);
            }
        }


        // creates new object of FileGenerator
        // submits config
        $objJsonGenerator = new JsonFileGenerator();
        $objJsonGenerator->createJsonFile($arrElements, 'form_' . $formId);

        // return compiled parent class
        return $strParentCompile;
    }
}
