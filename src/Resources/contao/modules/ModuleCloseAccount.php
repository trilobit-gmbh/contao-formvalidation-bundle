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
 * Class ModuleCloseAccount
 * @package Trilobit\FormvalidationBundle
 */
class ModuleCloseAccount extends \Contao\ModuleCloseAccount
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

        $objValidationHelper = new Helper();

        $elements = array();

        $elements['ctrl_password'] = '';
        $elements['ctrl_password']['mandatory'] = 1;
        $elements['ctrl_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0]);

        $fileGenerator = new TrilobitJsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_close_account');

        return $strParentCompile;
    }
}
