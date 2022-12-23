<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle;

use Contao\System;

/**
 * Class ModuleComments.
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

    protected function compile()
    {
        /* @var PageModel $objPage */
        global $objPage;

        $strParentCompile = parent::compile();

        $formId = $objPage->id;

        $objValidationHelper = new Helper();

        $elements = [];

        // Jira CONTAO-478
        // 2019-02-19
        $fieldId = '';

        if (version_compare(
                System::getContainer()->getParameter('kernel.packages')['contao/core-bundle'],
                '4.6.0'
            ) >= 0
        ) {
            // [Core] Append the module ID to the form field IDs to prevent duplicate IDs (see #1493), 25 Jun 2018
            $fieldId = '_'.$formId;
        }

        $elements['ctrl_name'.$fieldId]['type'] = '';
        $elements['ctrl_name'.$fieldId]['mandatory'] = 1;
        $elements['ctrl_name'.$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_name', $GLOBALS['TL_LANG']['MSC']['com_name']);

        $elements['ctrl_email'.$fieldId]['type'] = 'email';
        $elements['ctrl_email'.$fieldId]['mandatory'] = 1;
        $elements['ctrl_email'.$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_email', $GLOBALS['TL_LANG']['MSC']['com_email']);

        $elements['ctrl_websites'.$fieldId]['type'] = '';
        $elements['ctrl_websites'.$fieldId]['mandatory'] = '';
        $elements['ctrl_websites'.$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_websites', $GLOBALS['TL_LANG']['MSC']['com_website']);

        $elements['ctrl_comment'.$fieldId]['type'] = '';
        $elements['ctrl_comment'.$fieldId]['mandatory'] = 1;
        $elements['ctrl_comment'.$fieldId]['mandatoryMessage'] = $objValidationHelper->getMandatoryMessage('ctrl_comment', $GLOBALS['TL_LANG']['MSC']['com_comment']);

        // creates new object of FileGenerator
        // submits config
        $fileGenerator = new JsonFileGenerator();
        $fileGenerator->createJsonFile($elements, 'tl_comments_'.$formId);

        return $strParentCompile;
    }
}
