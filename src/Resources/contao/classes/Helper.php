<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Date;

class Helper
{
    public function getMandatoryMessage($field, $label)
    {
        if (\array_key_exists('trilobit_formvalidation', $GLOBALS['TL_LANG'])
            && isset($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['mandatory'])
            && $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['mandatory']
        ) {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['mandatory'];
        } elseif (\array_key_exists('trilobit_formvalidation', $GLOBALS['TL_LANG'])
            && isset($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg']['ctrl_'.$field]['mandatory'])
            && $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg']['ctrl_'.$field]['mandatory']
        ) {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg']['ctrl_'.$field]['mandatory'];
        } else {
            $message = $GLOBALS['TL_LANG']['ERR']['mandatory'];
        }

        $label = preg_replace('/&#92;/', '&#92;&#92;', $label);

        $message = sprintf($message, $label, $message);
        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }

    public function getFailureMessage($field, $type)
    {
        $message = '';

        if (\array_key_exists('trilobit_formvalidation', $GLOBALS['TL_LANG'])
            && isset($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['failure'])
            && $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['failure']
        ) {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['failure'];
        } else {
            if ('datim' === $type) {
                $message = $GLOBALS['TL_LANG']['ERR']['dateTime'];
            } else {
                if (\array_key_exists($type, $GLOBALS['TL_LANG']['ERR'])) {
                    $message = $GLOBALS['TL_LANG']['ERR'][$type];
                }
            }

            $objDate = new Date();

            if ('date' === $type) {
                $message = sprintf($message, $objDate->getInputFormat(Date::getNumericDateFormat()));
            } elseif ('datim' === $type) {
                $message = sprintf($message, $objDate->getInputFormat(Date::getNumericDatimFormat()));
            } elseif ('time' === $type) {
                $message = sprintf($message, $objDate->getInputFormat(Date::getNumericTimeFormat()));
            }
        }

        $message = preg_replace('/""\s/', '', $message);

        /*var_dump([
            $field,
            $type,
            $GLOBALS['TL_LANG']['ERR'][$type],
            $message,
        ]);*/

        return $message;
    }

    public function getMinlengthMessage($field, $label, $minlength)
    {
        if (\array_key_exists('trilobit_formvalidation', $GLOBALS['TL_LANG']) && $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['minlength']) {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['minlength'];
            $message = sprintf($message, $label, $minlength);
        } else {
            $label = preg_replace('/&#92;/', '&#92;&#92;', $label);

            $message = $GLOBALS['TL_LANG']['ERR']['minlength'];
            $message = sprintf($message, $label, $minlength);
        }

        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }

    public function getMaxlengthMessage($field, $label, $maxlength)
    {
        if (isset($GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['maxlength'])) {
            $message = $GLOBALS['TL_LANG']['trilobit_formvalidation']['errormsg'][$field]['maxlength'];
        } else {
            $label = preg_replace('/&#92;/', '&#92;&#92;', $label);

            $message = $GLOBALS['TL_LANG']['ERR']['maxlength'];
            $message = sprintf($message, $label, $maxlength);
        }

        $message = preg_replace('/""\s/', '', $message);

        return $message;
    }
}
