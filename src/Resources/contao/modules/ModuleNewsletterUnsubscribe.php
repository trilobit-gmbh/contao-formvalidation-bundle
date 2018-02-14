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
 * Class ModuleNewsletterUnsubscribe
 * @package Trilobit\FormvalidationBundle
 */
class ModuleNewsletterUnsubscribe extends \Contao\ModuleUnsubscribe
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
        $elements['ctrl_email_' . $formId]['label'] =  $GLOBALS['TL_LANG']['MSC']['emailAddress'];
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

        $fileGenerator = new TrilobitJsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_unsubscribe');

        return $strParentCompile;
    }
}
