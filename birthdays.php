<?php
/**
 * Global configuration items for the Birthdays plugin.
 * These are either static items, such as the plugin name and table
 * definitions, or are items that don't lend themselves well to the
 * glFusion configuration system, such as allowed file types.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017-2019 Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2002 Mike Lynn <mike@mlynn.com>
 * @package     birthdays
 * @version     v1.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_table_prefix, $_TABLES;
global $_BD_CONF;

$_BD_CONF['pi_name']            = 'birthdays';
$_BD_CONF['pi_display_name']    = 'Birthdays';
$_BD_CONF['pi_version']         = '1.0.1';
$_BD_CONF['gl_version']         = '1.7.8';
$_BD_CONF['pi_url']             = 'http://www.glfusion.org';

$_TABLES['birthdays']      = $_DB_table_prefix . 'birthdays';

?>
