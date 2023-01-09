<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;

/**
 * Class ModulePassword.
 */
class ModulePassword extends \Contao\ModulePassword
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
        $minPasswordLength = Config::get('minPasswordLength');

        $objValidationHelper = new Helper();

        $elements = [];

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
        $elements['ctrl_password']['minlength'] = $minPasswordLength;
        $elements['ctrl_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0]);
        $elements['ctrl_password']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);

        $elements['ctrl_password_confirm']['type'] = 'passwordMatch';
        $elements['ctrl_password_confirm']['mandatory'] = 1;
        $elements['ctrl_password_confirm']['minlength'] = $minPasswordLength;
        $elements['ctrl_password_confirm']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password_confirm', $GLOBALS['TL_LANG']['MSC']['confirmation']);
        $elements['ctrl_password_confirm']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);
        $elements['ctrl_password_confirm']['failureMessage'] = $objValidationHelper->getFailureMessage('ctrl_password_confirm', 'passwordMatch');

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_password_'.$formId);

        return $strParentCompile;
    }
}
