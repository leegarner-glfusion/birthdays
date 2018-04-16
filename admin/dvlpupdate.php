<?php
/**
*   Apply updates to Birthdays during development.
*   Calls BIRTHDAYS_upgrade() with "ignore_errors" set so repeated SQL statements
*   won't cause functions to abort.
*
*   Only updates from the previous released version.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @package    birthdays
*   @version    0.0.2
*   @since      0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

require_once '../../../lib-common.php';
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to access the Evlist Development Code Upgrade Routine without proper permissions.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG27[12]);
    $display .= $LANG27[12];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}
require_once $_BD_CONF['pi_path'] . '/upgrade.php';   // needed for set_version()
CACHE_clear();
Birthdays\Birthday::clearCache();

function BIRTHDAYS_dvlp_002()
{
    global $_BD_CONF;

    $_BD_CONF['pi_version'] = '0.0.2';
    BIRTHDAYS_do_set_version('0.0.2');
    plugin_upgrade_birthdays(true);
}

// Call the function for the currently-installed codebase
BIRTHDAYS_dvlp_002();

// need to clear the template cache so do it here
CACHE_clear();
header('Location: '.$_CONF['site_admin_url'].'/plugins.php?msg=600');
exit;

?>