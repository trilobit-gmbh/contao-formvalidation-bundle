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

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_close_account');

        return $strParentCompile;
    }
}
