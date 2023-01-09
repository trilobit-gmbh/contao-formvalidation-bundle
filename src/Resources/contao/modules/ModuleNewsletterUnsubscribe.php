<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

/**
 * Class ModuleNewsletterUnsubscribe.
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

    protected function compile()
    {
        $strParentCompile = parent::compile();

        $formId = \strlen($this->formID) ? $this->formID : $this->id;

        $objValidationHelper = new Helper();

        $elements = [];

        $elements['ctrl_email_'.$formId]['type'] = 'email';
        $elements['ctrl_email_'.$formId]['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_email_'.$formId, 'email');
        $elements['ctrl_email_'.$formId]['mandatory'] = 1;
        $elements['ctrl_email_'.$formId]['label'] = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        $elements['ctrl_email_'.$formId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_email_'.$formId, $GLOBALS['TL_LANG']['MSC']['emailAddress']);

        if (1 !== $this->nl_hideChannels) {
            $elements[$formId]['type'] = 'checkbox';
            $elements[$formId]['failureMessage'] = $objValidationHelper->getFailureMessage($formId, 'checkbox');
            $elements[$formId]['mandatory'] = 1;
            $elements[$formId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage($formId, $GLOBALS['TL_LANG']['MSC']['nl_channels']);

            foreach ($this->nl_channels as $key => $value) {
                $elements[$formId]['elements'][$key] = $value;
            }
        }

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_unsubscribe_'.$formId);

        return $strParentCompile;
    }
}
