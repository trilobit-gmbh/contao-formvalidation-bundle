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

        $elements = array();

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

            $prefix = '';

            if (   $objFields->type == 'checkbox'
                || $objFields->type == 'radio'
            )
            {
                $elements[$objFields->id]['type'] = $objFields->type;
                $elements[$objFields->id]['name'] = $objFields->name;

                foreach (unserialize($objFields->options) as $key => $value)
                {
                    $elements[$objFields->id]['elements'][$key] = $key;
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

                $prefix = 'ctrl_';
                $elements[$prefix . $objFields->id]['type'] = '';

                if ($objFields->rgxp != '')
                {
                    $elements[$prefix . $objFields->id]['type'] = $objFields->rgxp;
                    $elements[$prefix . $objFields->id]['failureMessage'] = $objValidationHelper->getFailureMessage($prefix . $objFields->id, $objFields->rgxp);
                }
            }

            if ($objFields->mandatory)
            {
                $elements[$prefix . $objFields->id]['mandatory'] = 1;
                $elements[$prefix . $objFields->id]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($prefix . $objFields->id, $objFields->label);

            }

            if ($objFields->minlength)
            {
                $elements[$prefix . $objFields->id]['minlength'] = $objFields->minlength;
                $elements[$prefix . $objFields->id]['minlengthMessage'] = $objValidationHelper->getMinlengthMessage($prefix . $objFields->id, $objFields->label, $objFields->minlength);
            }

            if ($objFields->maxlength)
            {
                $elements[$prefix . $objFields->id]['maxlength'] = $objFields->maxlength;
                $elements[$prefix . $objFields->id]['maxlengthMessage'] = $objValidationHelper->getMaxlengthMessage($prefix . $objFields->id, $objFields->label, $objFields->maxlength);
            }

            if ($objFields->type == 'password')
            {
                $elements[$prefix . $objFields->id]['minlength'] = 8;

                $elements[$prefix . $objFields->id . '_confirm']['type'] = 'passwordMatch';

                if ($elements[$prefix . $objFields->id]['mandatory'] == 1)
                {
                    $elements[$prefix . $objFields->id . '_confirm']['mandatory'] = 1;
                    $elements[$prefix . $objFields->id . '_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($prefix . $objFields->id . '_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
                }

                $elements[$prefix . $objFields->id . '_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage($prefix . $objFields->id . '_confirm', 'passwordMatch');
            }
        }


        // creates new object of FileGenerator
        // submits config
        $objJsonGenerator = new JsonFileGenerator();
        $objJsonGenerator->createJsonFile($elements, 'form_' . $formId);

        // return compiled parent class
        return $strParentCompile;
    }
}
