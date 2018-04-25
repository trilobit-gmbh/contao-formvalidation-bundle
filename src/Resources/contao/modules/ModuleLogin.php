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
 * Class ModuleLogin
 * @package Trilobit\FormvalidationBundle
 */
class ModuleLogin extends \Contao\ModuleLogin
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

        $elements['username']['type'] = '';
        $elements['username']['mandatory'] = 1;
        $elements['username']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('username', $GLOBALS['TL_LANG']['MSC']['username']);

        $elements['password']['type'] = '';
        $elements['password']['mandatory'] = 1;
        $elements['password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('password', $GLOBALS['TL_LANG']['MSC']['password'][0]);

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_login');

        return $strParentCompile;
    }
}
