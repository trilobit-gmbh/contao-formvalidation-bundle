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

$GLOBALS['TL_CTE']['includes']['comments'] = 'Trilobit\FormvalidationBundle\ModuleFormGenerator';
$GLOBALS['TL_CTE']['includes']['form']     = 'Trilobit\FormvalidationBundle\ModuleFormGenerator';

$GLOBALS['FE_MOD']['application']['form']       = 'Trilobit\FormvalidationBundle\ModuleFormGenerator';
$GLOBALS['FE_MOD']['application']['comments']   = 'Trilobit\FormvalidationBundle\ModuleComments';
$GLOBALS['FE_MOD']['user']['registration']      = 'Trilobit\FormvalidationBundle\ModuleMember';
$GLOBALS['FE_MOD']['user']['login']             = 'Trilobit\FormvalidationBundle\ModuleLogin';
$GLOBALS['FE_MOD']['user']['lostPassword']      = 'Trilobit\FormvalidationBundle\ModulePassword';
$GLOBALS['FE_MOD']['user']['closeAccount']      = 'Trilobit\FormvalidationBundle\ModuleCloseAccount';
$GLOBALS['FE_MOD']['user']['personalData']      = 'Trilobit\FormvalidationBundle\ModulePersonalData';
$GLOBALS['FE_MOD']['newsletter']['subscribe']   = 'Trilobit\FormvalidationBundle\ModuleNewsletterSubscribe';
$GLOBALS['FE_MOD']['newsletter']['unsubscribe'] = 'Trilobit\FormvalidationBundle\ModuleNewsletterUnsubscribe';

