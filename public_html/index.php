<?php
/**
*   glFusion API functions for the Birthdays plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @author     Mike Lynn <mike@mlynn.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2002 Mike Lynn <mike@mlynn.com>
*   @package    birthdays
*   @version    0.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
require_once('../lib-common.php');

if (!BIRTHDAYS_canView()) {
    COM_404();
    exit;
}

USES_lib_admin();

//global $T, $_TABLES, $PHP_SELF, $_CONF, $HTTP_POST_VARS, $_USER, $LANG_STATIC,$_SP_CONF;

/*
* Main Function
*/

$expected = array('list', 'addbday', 'mode');
foreach($expected as $provided) {
    // Get requested action and page from GET or POST variables.
    // Most could come in either way.  They are not sanitized, so they must
    // only be used in switch or other conditions.
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}
if (empty($action)) $action = 'list';
$dt = new \Date('now', $_CONF['timezone']);
$curmonth = $dt->Format('n', true);
$year = $dt->Format('Y', true);     // just to have a value for later
$filter_month = isset($_REQUEST['filter_month']) ? $_REQUEST['filter_month'] : $curmonth;
if ($filter_month == -1) {
    $filter_month = $curmonth;
}

switch ($action) {
case 'addbday':
    if (!COM_isAnonUser()) {
        $bday = \Birthdays\Birthday::getInstance($_USER['uid']);
        $bday->Save(array(
            'month' => $_POST['birthday_month'],
            'day' => $_POST['birthday_day'],
        ) );
        echo COM_refresh($_CONF['site_url'] . '/birthdays/index.php');
    }
    break;
}

$display = COM_siteHeader('menu');
$T = new Template($_CONF['path'] . 'plugins/birthdays/templates');
$T->set_file('header', 'index.thtml');
$T->set_var(array(
    'header'    => $_BD_CONF['pi_display_name'],
    'pi_name'   => $_BD_CONF['pi_name'],
    'logo'      => plugin_geticon_birthdays(),
    'my_form'   => COM_isAnonUser() ? '' : \Birthdays\Birthday::editForm($_USER['uid'], 'edit_index'),
    'month_select' => \Birthdays\Birthday::selectMonth($filter_month),
) );
$T->parse('output','header');
$display .= $T->finish($T->get_var('output'));
$display .= listbirthdays($filter_month);
$display .= COM_siteFooter();
echo $display;
exit;


/**
*   Present the list of birthdays.
*
*   @param  integer $filter_month   Month to show, or "all"
*   @return string      HTML for the list
*/
function listbirthdays($filter_month)
{
    global $T, $_TABLES, $PHP_SELF, $_CONF, $HTTP_POST_VARS, $_USER, $LANG_STATIC,$_SP_CONF;
    global $LANG_BD00, $_BD_CONF, $LANG04, $LANG_ADMIN;

    $retval = '';

    $header_arr = array(
        array('text' => $LANG04[3],
            'field' => 'fullname',
            'sort' => false,
            'align' => '',
        ),
        array('text' => $LANG_BD00['birthday'],
            'field' => 'birthday',
            'sort' => true,
            'align' => 'center',
        ),
    );

/*    $dt = new \Date('now', $_CONF['timezone']);
    $curmonth = $dt->Format('n', true);
    $year = $dt->Format('Y', true);     // just to have a value for later
    if ($filter_month == -1) {
        $filter_month = $curmonth;
    }*/
    $filter = $filter_month == 0 ? '' : " AND month = $filter_month";
    $text_arr = array(
        'form_url' => $_BD_CONF['url'] . '/index.php',
    );
    /*$query_arr = array('table' => 'birthdays',
        'sql' => "SELECT * FROM {$_TABLES['birthdays']}
                WHERE 1=1 $filter",
        'query_fields' => array(),
        'default_filter' => ''
    );*/
    $defsort_arr = array('field' => 'day', 'direction' => 'ASC');
    $data_arr = Birthdays\Birthday::getAll($filter_month);
    $form_arr = array();
    $extra = array(
        'dt'    => new Date('now', $_CONF['timezone']),
    );
    $retval .= ADMIN_listArray('birthdays', 'getField_bday_list', $header_arr,
                $text_arr, $data_arr, $defsort_arr, '', $extra, '', $form_arr);
    return $retval;
}


/**
*   Determine what to display in the admin list for each birthday.
*
*   @param  string  $fieldname  Name of the field, from database
*   @param  mixed   $fieldvalue Value of the current field
*   @param  array   $A          Array of all name/field pairs
*   @param  array   $icon_arr   Array of system icons
*   @param  array   $extra      Extra passthrough items (includes date obj)
*   @return string              HTML for the field cell
*/
function getField_bday_list($fieldname, $fieldvalue, $A, $icon_arr, $extra)
{
    global $_CONF, $_BD_CONF;

    $retval = '';

    switch($fieldname) {
    case 'fullname':
        $retval .= COM_getDisplayName($A['uid']);
        break;

    case 'birthday':
        $extra['dt']->setDate($A['year'], $A['month'], $A['day']);
        $retval .= $extra['dt']->Format($_BD_CONF['format'], true);
        break;
    }
    return $retval;
}

?>
