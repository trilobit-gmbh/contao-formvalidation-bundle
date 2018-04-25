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
 * Class ModuleNewsletterSubscribe
 * @package Trilobit\FormvalidationBundle
 */
class ModuleNewsletterSubscribe extends \Contao\ModuleSubscribe
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

        $formId = strlen($this->formID) ? $this->formID : $this->id;

        $elements = array();

        $objValidationHelper = new Helper();

        $elements['ctrl_email_' . $formId]['type'] = 'email';
        $elements['ctrl_email_' . $formId]['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_email_' . $formId, 'email');
        $elements['ctrl_email_' . $formId]['mandatory'] = 1;
        $elements['ctrl_email_' . $formId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_email_' . $formId, $GLOBALS['TL_LANG']['MSC']['emailAddress']);

        if ($this->nl_hideChannels != 1)
        {
            $elements[$formId]['type'] = 'checkbox';
            $elements[$formId]['failureMessage'] = $objValidationHelper->getFailureMessage($formId, 'checkbox');
            $elements[$formId]['mandatory'] = 1;
            $elements[$formId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($formId, $GLOBALS['TL_LANG']['MSC']['nl_channels']);

            foreach ($this->nl_channels as $key =>$value)
            {
                $elements[$formId]['elements'][$key] = $value;
            }
        }

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_subscribe');

        return $strParentCompile;
    }
}
