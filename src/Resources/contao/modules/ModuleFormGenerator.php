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
 * Class ModuleFormGenerator
 * @package Trilobit\FormvalidationBundle
 */
class ModuleFormGenerator extends \Contao\Form
{
    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }


    /**
     * @return string
     */
    protected function compile()
    {
        // compiles parent class, so all variables are set
        $strParentCompile = parent::compile();

        $this->import('Database');

        // getting formId
        $formId = strlen($this->formID) ? $this->formID : $this->id;

        // Lade alle Formularfelder der jeweiligen Seite
        $objFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? AND invisible!=1 ORDER BY sorting")
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

            if (   $objFields->type == 'efgLookupRadio'
                || $objFields->type == 'efgLookupCheckbox'
            )
            {
                if ($objFields->type == 'efgLookupRadio')
                {
                    $objFields->type = 'radio';
                }
                else
                {
                    $objFields->type = 'checkbox';
                }

                // Loading Label / Value of EFG-Chechbox/Radios
                $arrSettings = deserialize($objFields->efgLookupOptions);

                $arrLookupField = explode('.', $arrSettings['lookup_field']);
                $sqlLookupTable = $arrLookupField[0];
                $sqlLookupField = $arrLookupField[1];

                $arrLookupValField = explode('.', $arrSettings['lookup_val_field']);
                $sqlLookupIdField = $arrLookupValField[1];

                $arrLookupWhere = explode('.', $arrSettings['lookup_where']);
                $strLookupWhere = \String::decodeEntities($arrLookupWhere[0]);

                $sqlLookupWhere = (!empty($strLookupWhere) ? " WHERE " . $strLookupWhere : "");
                $sqlLookupOrder = $arrLookupField[0] . '.' . $arrLookupField[1];

                $sqlLookup = "SELECT " . $sqlLookupField . (!empty($sqlLookupIdField) ? ', ' : '') . $sqlLookupIdField . " FROM " . $sqlLookupTable . $sqlLookupWhere . (!empty($sqlLookupOrder) ? " ORDER BY " . $sqlLookupOrder : "");

                if (!empty($sqlLookupTable))
                {
                    $objOptions = \Database::getInstance()->prepare($sqlLookup)->execute();
                }

                if ($objOptions->numRows)
                {
                    $arrOptions = array();
                    $counter = 0;
                    while ($arrOpt = $objOptions->fetchAssoc())
                    {
                        if ($sqlLookupIdField)
                        {
                            $arrOptions[$counter]['label'] = $arrOpt[$sqlLookupField];
                            $arrOptions[$counter]['value'] = $arrOpt[$sqlLookupIdField];
                        }
                        else
                        {
                            $arrOptions[$counter]['label'] = $arrOpt[$sqlLookupField];
                            $arrOptions[$counter]['value'] = $arrOpt[$sqlLookupField];
                        }
                        $counter++;
                    }
                    $objFields->options = serialize($arrOptions);
                }
            }

            if ($objFields->type == 'efgLookupSelect')
            {
                $objFields->type = 'select';
            }

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
