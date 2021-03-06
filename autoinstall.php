<?php
/**
 * Provides automatic installation of the Birthdays plugin.
 * There is nothing to do except create the plugin record
 * since there are no tables or user interfaces.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     birthdays
 * @version     v0.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/** @global string $_DB_dbms */
global $_DB_dbms;

require_once __DIR__ . '/functions.inc';
require_once __DIR__ . '/birthdays.php';
require_once __DIR__ . "/sql/{$_DB_dbms}_install.php";

//  Plugin installation options
$INSTALL_plugin[$_BD_CONF['pi_name']] = array(
    'installer' => array(
        'type'  => 'installer',
        'version' => '1',
        'mode'  => 'install',
    ),
    'plugin' => array(
        'type' => 'plugin',
        'name'      => $_BD_CONF['pi_name'],
        'ver'       => $_BD_CONF['pi_version'],
        'gl_ver'    => $_BD_CONF['gl_version'],
        'url'       => $_BD_CONF['pi_url'],
        'display'   => $_BD_CONF['pi_display_name']
    ),
    array(
        'type'  => 'table',
        'table' => $_TABLES['birthdays'],
        'sql'   => $_SQL['birthdays'],
    ),
    array(
        'type'  => 'block',
        'name'  => 'birthdays_month',
        'title' => $LANG_BD00['birthdays_month'],
        'phpblockfn' => 'phpblock_birthdays_month',
        'block_type' => 'phpblock',
        'is_enabled' => 0,
        'group_id' => 1,
    ),

    array(
        'type'  => 'block',
        'name'  => 'birthdays_week',
        'title' => $LANG_BD00['birthdays_week'],
        'phpblockfn' => 'phpblock_birthdays_week',
        'block_type' => 'phpblock',
        'is_enabled' => 0,
        'group_id' => 1,
    ),
    array(
        'type' => 'feature',
        'feature' => 'birthdays.admin',
        'desc' => 'Full access to the Birthdays plugin',
        'variable' => 'admin_feature_id',
    ),
    array(
        'type' => 'feature',
        'feature' => 'birthdays.view',
        'desc' => 'View access to the Birthdays plugin',
        'variable' => 'view_feature_id',
    ),
    array(
        'type' => 'feature',
        'feature' => 'birthdays.card',
        'desc' => 'Can receive birthday cards',
        'variable' => 'card_feature_id',
    ),
    array(
        'type' => 'mapping',
        'findgroup' => 'Root',       // Root user gets the feature
        'feature' => 'admin_feature_id',
        'log' => 'Adding admin feature to the admin group',
    ),
    array(
        'type' => 'mapping',
        'findgroup' => 'Logged-In Users',   // all users can receive cards
        'feature' => 'card_feature_id',
        'log' => 'Adding card feature to the users group',
    ),
    array(
        'type' => 'mapping',
        'findgroup' => 'Logged-In Users',   // all users can receive cards
        'feature' => 'view_feature_id',
        'log' => 'Adding view feature to the users group',
    ),
);


/**
 * Puts the datastructures for this plugin into the glFusion database.
 *
 * @return  boolean     True if successful False otherwise
 */
function plugin_install_birthdays()
{
    global $INSTALL_plugin, $_BD_CONF;

    COM_errorLog("Attempting to install the {$_BD_CONF['pi_name']} plugin", 1);
    $ret = INSTALLER_install($INSTALL_plugin[$_BD_CONF['pi_name']]);
    if ($ret > 0) {
        return false;
    } else {
        return true;
    }
}


/**
 * Automatic removal function.
 *
 * @return  array       Array of items to be removed.
 */
function plugin_autouninstall_birthdays()
{
    $out = array (
        'tables'    => array('birthdays'),
        'groups'    => array(),
        'features'  => array(
            'birthdays.admin',
            'birthdays.view',
            'birthdays.card',
        ),
        'php_blocks' => array(
            'phpblock_birthdays_week',
            'phpblock_birthdays_month',
        ),
        'vars'      => array('birthdays_lastrun'),
    );
    PLG_itemDeleted('*', 'birthdays');
    \Birthdays\Birthday::clearCache();
    return $out;
}


/**
 * Loads the configuration records for the Online Config Manager.
 *
 * @return  boolean     True = proceed, False = an error occured
 */
function plugin_load_configuration_birthdays()
{
    require_once __DIR__ . '/install_defaults.php';
    return plugin_initconfig_birthdays();
}

?>
