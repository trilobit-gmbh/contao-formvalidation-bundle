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
class ModuleChangePassword extends \Contao\ModuleChangePassword
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

        $formId = null !== $this->formID && \strlen($this->formID) ? $this->formID : $this->id;
        $minPasswordLength = Config::get('minPasswordLength');

        $objValidationHelper = new Helper();

        $elements = [];

        $elements['ctrl_oldpassword']['type'] = '';
        $elements['ctrl_oldpassword']['mandatory'] = 1;
        $elements['ctrl_oldpassword']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['oldPassword']);

        $elements['ctrl_password']['type'] = '';
        $elements['ctrl_password']['mandatory'] = 1;
        $elements['ctrl_password']['minlength'] = $minPasswordLength;
        $elements['ctrl_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword']);
        $elements['ctrl_password']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['newPassword'], $minPasswordLength);
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
