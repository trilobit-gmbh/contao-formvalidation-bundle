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

        $fileGenerator = new TrilobitJsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_login');

        return $strParentCompile;
    }
}
