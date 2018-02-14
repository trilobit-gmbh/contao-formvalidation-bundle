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
 * Class ModulePassword
 * @package Trilobit\FormvalidationBundle
 */
class ModulePassword extends \ModulePassword
{

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }


    /**
     *
     */
    protected function compile()
    {
        $strParentCompile = parent::compile();

        $elements = array();

        $objValidationHelper = new Helper();

        $elements['ctrl_email']['type'] = 'email';
        $elements['ctrl_email']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_email', 'email');
        $elements['ctrl_email']['mandatory'] = 1;
        $elements['ctrl_email']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_email', $GLOBALS['TL_LANG']['tl_member']['email'][0]);

        $elements['ctrl_username']['type'] = '';
        $elements['ctrl_username']['mandatory'] = 1;
        $elements['ctrl_username']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_username', $GLOBALS['TL_LANG']['tl_member']['username'][0]);

        $elements['ctrl_lost_password']['type'] = 'digit';
        $elements['ctrl_lost_password']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_lost_password', 'digit');
        $elements['ctrl_lost_password']['mandatory'] = 1;
        $elements['ctrl_lost_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_lost_password', $GLOBALS['TL_LANG']['MSC']['securityQuestion']);

        $elements['ctrl_password']['type'] = '';
        $elements['ctrl_password']['mandatory'] = 1;
        $elements['ctrl_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0]);

        $elements['ctrl_password_confirm']['type'] = 'passwordMatch';
        $elements['ctrl_password_confirm']['mandatory'] = 1;
        $elements['ctrl_password_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
        $elements['ctrl_password_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_password_confirm', 'passwordMatch');

        $fileGenerator = new TrilobitJsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_lost_password');

        return $strParentCompile;
    }
}
