<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-formvalidation-bundle
 */

use Trilobit\FormvalidationBundle\ContentComments;
use Trilobit\FormvalidationBundle\ModuleChangePassword;
use Trilobit\FormvalidationBundle\ModuleCloseAccount;
use Trilobit\FormvalidationBundle\ModuleComments;
use Trilobit\FormvalidationBundle\ModuleFormGenerator;
use Trilobit\FormvalidationBundle\ModuleLogin;
use Trilobit\FormvalidationBundle\ModuleNewsletterSubscribe;
use Trilobit\FormvalidationBundle\ModuleNewsletterUnsubscribe;
use Trilobit\FormvalidationBundle\ModulePassword;
use Trilobit\FormvalidationBundle\ModulePersonalData;
use Trilobit\FormvalidationBundle\ModuleRegistration;

$GLOBALS['TL_CTE']['includes']['comments'] = ContentComments::class;
$GLOBALS['TL_CTE']['includes']['form'] = ModuleFormGenerator::class;

$GLOBALS['FE_MOD']['application']['form'] = ModuleFormGenerator::class;
$GLOBALS['FE_MOD']['application']['comments'] = ModuleComments::class;

$GLOBALS['FE_MOD']['user']['registration'] = ModuleRegistration::class;
$GLOBALS['FE_MOD']['user']['login'] = ModuleLogin::class;
$GLOBALS['FE_MOD']['user']['changePassword'] = ModuleChangePassword::class;
$GLOBALS['FE_MOD']['user']['lostPassword'] = ModulePassword::class;
$GLOBALS['FE_MOD']['user']['closeAccount'] = ModuleCloseAccount::class;
$GLOBALS['FE_MOD']['user']['personalData'] = ModulePersonalData::class;

$GLOBALS['FE_MOD']['newsletter']['subscribe'] = ModuleNewsletterSubscribe::class;
$GLOBALS['FE_MOD']['newsletter']['unsubscribe'] = ModuleNewsletterUnsubscribe::class;
