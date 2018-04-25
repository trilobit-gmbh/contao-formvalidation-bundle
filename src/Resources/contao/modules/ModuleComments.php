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
 * Class ModuleComments
 * @package Trilobit\FormvalidationBundle
 */
class ModuleComments extends \Contao\ModuleComments
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

        $elements['ctrl_name']['type'] = '';
        $elements['ctrl_name']['mandatory'] = 1;
        $elements['ctrl_name']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_name', $GLOBALS['TL_LANG']['MSC']['com_name']);

        $elements['ctrl_email']['type'] = 'email';
        $elements['ctrl_email']['mandatory'] = 1;
        $elements['ctrl_email']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_email', $GLOBALS['TL_LANG']['MSC']['com_email'][0]);

        $elements['ctrl_websites']['type'] = '';
        $elements['ctrl_websites']['mandatory'] = '';
        $elements['ctrl_websites']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_websites', $GLOBALS['TL_LANG']['MSC']['com_websites'][0]);

        $elements['ctrl_comment']['type'] = '';
        $elements['ctrl_comment']['mandatory'] = 1;
        $elements['ctrl_comment']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_comment', $GLOBALS['TL_LANG']['MSC']['com_comment'][0]);

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'com_tl_page_' . $this->pid);

        return $strParentCompile;
    }
}
