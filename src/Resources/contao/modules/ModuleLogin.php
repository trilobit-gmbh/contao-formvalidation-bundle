<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\Config;

class ModuleLogin extends \Contao\ModuleLogin
{
    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        parent::compile();

        $formId = null !== $this->formID && \strlen($this->formID) ? $this->formID : $this->id;
        $minPasswordLength = Config::get('minPasswordLength');

        $objValidationHelper = new Helper();

        $elements = [];

        $elements['username']['type'] = '';
        $elements['username']['mandatory'] = 1;
        $elements['username']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('username', $GLOBALS['TL_LANG']['MSC']['username']);

        $elements['password']['type'] = '';
        $elements['password']['mandatory'] = 1;
        $elements['password']['minlength'] = $minPasswordLength;
        $elements['password']['minlengthMessage'] = $objValidationHelper->getMinlengthMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0], $minPasswordLength);
        $elements['password']['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_password', $GLOBALS['TL_LANG']['MSC']['password'][0]);

        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_login_'.$formId);
    }
}
