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


use Controller;


/**
 * Class Helper
 * @package Trilobit\FormvalidationBundle
 */
class Helper extends Controller
{

    /**
     * @param $field
     * @param $label
     * @return null|string|string[]
     */
    public function getMandatoryMessage($field, $label)
    {
        $message = '';
        
        if ($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['mandatory'])
        {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['mandatory'];
        }
        else if ($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg']['ctrl_' . $field]['mandatory'])
        {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg']['ctrl_' . $field]['mandatory'];
        }
        else
        {
            $message = $GLOBALS['TL_LANG']['ERR']['mandatory'];
        }

        $label = preg_replace('/&#92;/', '&#92;&#92;', $label);
        
        $message = sprintf($message, $label, $message);
        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }


    /**
     * @param $field
     * @param $type
     * @return null|string|string[]
     * @throws \Exception
     */
    public function getFailureMessage($field, $type)
    {
        $message = '';

        if ($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['failure'])
        {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['failure'];
        }
        else
        {
            if ($type == 'datim')
            {
                $message = $GLOBALS['TL_LANG']['ERR']['dateTime'];
            }
            else
            {
                $message = $GLOBALS['TL_LANG']['ERR'][$type];
            }

            $objDate = new \Date();

            if ($type == 'date')
            {
               $message = sprintf($message, $objDate->getInputFormat($GLOBALS['TL_CONFIG']['dateFormat']));
            }
            else if ($type == 'datim')
            {
                $message = sprintf($message, $objDate->getInputFormat($GLOBALS['TL_CONFIG']['datimFormat']));
            }
            else if ($type == 'time')
            {
                $message = sprintf($message, $objDate->getInputFormat($GLOBALS['TL_CONFIG']['timeFormat']));
            }
        }

        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }


    /**
     * @param $field
     * @param $label
     * @param $minlength
     * @return null|string|string[]
     */
    public function getMinlengthMessage($field, $label, $minlength)
    {
        $message = '';

        if ($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['minlength'])
        {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['minlength'];
        }
        else
        {
            $label   = preg_replace('/&#92;/', '&#92;&#92;', $label);
            
            $message = $GLOBALS['TL_LANG']['ERR']['minlength'];
            $message = sprintf($message, $label, $minlength);
        }

        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }


    /**
     * @param $field
     * @param $label
     * @param $maxlength
     * @return null|string|string[]
     */
    public function getMaxlengthMessage($field, $label, $maxlength)
    {
        $message = '';

        if ($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['maxlength'])
        {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['maxlength'];
        }
        else
        {
            $label   = preg_replace('/&#92;/', '&#92;&#92;', $label);
            
            $message = $GLOBALS['TL_LANG']['ERR']['maxlength'];
            $message = sprintf($message, $label, $maxlength);
        }

        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }
}
