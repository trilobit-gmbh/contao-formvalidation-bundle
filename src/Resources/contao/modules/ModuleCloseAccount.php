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
 * Class ModuleCloseAccount.
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

    protected function compile()
    {
        $strParentCompile = parent::compile();

        $formId = \strlen($this->formID) ? $this->formID : $this->id;
        $minPasswordLength = Config::get('minPasswordLength');

        $objValidationHelper = new Helper();

        $elements = [];

        $elements['ctrl_password']['type'] = '';
        $elements['ctrl_password']['mandatory'] = 1;
        $elements['ctrl_password']['minlength'] = $minPasswordLength;
        $elements['ctrl_password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0]);
        $elements['ctrl_password']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0], $minPasswordLength);

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_close_account_'.$formId);

        return $strParentCompile;
    }
}
