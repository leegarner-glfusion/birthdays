<?php
/**
 * Class to manage birthdays.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     birthdays
 * @version     v0.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Birthdays;

/**
 * Class for birthdays.
 * @package birthdays
 */
class Birthday
{
    /** Internal properties accessed via `__set()` and `__get()`.
     * @var array */
    var $properties;

    /** Base cache tag added to all items.
     * @const string */
    const TAG = 'birthdays';

    /** Minimum glFusion version that supports caching.
     * @const string */
    const CACHE_GVERSION = '2.0.0';

    /**
     * Instantiate an object for the specified user, or a new entry.
     *
     * @param   integer $uid    Optional type ID, zero indicates a new record
     */
    public function __construct($uid=0)
    {
        $uid = (int)$uid;
        if ($uid < 1) {
            $this->uid = 0;
            $this->month = 0;
            $this->day = 0;
            $this->year = 0;
        } else {
            $this->uid = $uid;
            $this->Read();
        }
    }


    /**
     * Get an instance of a birthday object.
     * Saves objects in a static variable for subsequent use.
     *
     * @param   integer $uid    User ID
     * @return  object          Birthday object for the user
     */
    public static function getInstance($uid)
    {
        static $bdays = array();
        $key = 'uid_' . $uid;
        if (!array_key_exists($uid, $bdays)) {
            $bdays[$uid] = self::getCache($key);
            if (!$bdays[$uid]) {
                $bdays[$uid] = new self($uid);
                self::setCache($key, $bdays[$uid]);
            }
        }
        return $bdays[$uid];
    }


    /**
     * Set a value into the property array
     *
     * @param   string  $key    Property name
     * @param   mixed           Property value
     */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'uid':
        case 'year':
        case 'month':
        case 'day':
            $this->properties[$key] = (int)$value;
            break;
        }
    }


    /**
     * Getter function.
     *
     * @param   string  $key    Name of property
     * @return  mixed           Value of property, NULL if not set
     */
    public function __get($key)
    {
        if (isset($this->properties[$key]))
            return $this->properties[$key];
        else
            return NULL;
    }


    /**
     * Sets all variables to the matching values from `$rows`.
     *
     * @param   array   $row    Array of values, from DB or $_POST
     */
    public function SetVars($row)
    {
        if (!is_array($row)) return;

        foreach (array('uid', 'month', 'day') as $key) {
            if (isset($row[$key])) {
                $this->$key = $row[$key];
            }
        }
    }


    /**
     * Read one as type from the database and populate the local values.
     *
     * @param   integer $uid    Optional user ID.  Current user if zero.
     */
    public function Read($uid = 0)
    {
        global $_TABLES;

        $uid = (int)$uid;
        if ($uid == 0) $uid = $this->uid;
        if ($uid == 0) {
            return false;
        }

        $result = DB_query("SELECT * from {$_TABLES['birthdays']}
                            WHERE uid = $uid");
        $row = DB_fetchArray($result, false);
        $this->SetVars($row);
    }


    /**
     * Adds the current values to the databae as a new record.
     *
     * @param   array   $vals   Optional array of values to set
     * @return  boolean     True on success, False on error
     */
    public function Save($vals = NULL)
    {
        global $_TABLES, $_BD_CONF;;

        if (is_array($vals)) {
            $this->SetVars($vals);
        }

        // If "0" is entered for either month or day, consider this
        // as a deletion request
        if ($this->month == 0 || $this->day == 0) {
            self::Delete($this->uid);
            PLG_itemDeleted($this->uid, $_BD_CONF['pi_name']);
            return true;
        }

        $sql = "INSERT INTO {$_TABLES['birthdays']} SET
                    uid = {$this->uid},
                    month = {$this->month},
                    day = {$this->day}
                ON DUPLICATE KEY UPDATE
                    month = {$this->month},
                    day = {$this->day}";
        //echo $sql;die;
        $res = DB_query($sql);
        if (!DB_error()) {
            self::clearCache('range');
            // Put this in cache to save a lookup in plugin_getiteminfo
            self::setCache('uid_' . $this->uid, $this);
            PLG_itemSaved($this->uid, $_BD_CONF['pi_name']);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get all birthdays for one month, one day, or all..
     *
     * @param   mixed   $month  Month number, or "all"
     * @param   mixed   $day    Optional day number
     * @return  array           Array of birthday records
     */
    public static function getAll($month = 0, $day = 0)
    {
        global $_TABLES, $_CONF;

        $month = (int)$month;
        $day = (int)$day;
        $cache_key = $month . '_' . $day;
        $retval = self::getCache($cache_key);
        if ($retval !== NULL) {
            return $retval;
        }

        $where = '';
        if ($month == 0) {
            $where .= ' AND b.month > 0';
        } else {
            $where .= ' AND b.month = ' . $month;
        }
        if ($day == 0) {
            $where .= ' AND b.day > 0';
        } else {
            $where .= ' AND b.day = ' . $day;
        }
        // The year isn't stored, so use a bogus leap year.
        $sql = "SELECT 2016 as year, CONCAT(
                    LPAD(b.month,2,0),LPAD(b.day,2,0)
                ) as birthday, b.*, u.username, u.fullname
                FROM {$_TABLES['birthdays']} b
                LEFT JOIN  {$_TABLES['users']} u
                    ON u.uid = b.uid
                WHERE 1=1 $where
                ORDER BY b.month, b.day";
        //echo $sql;die;
        $res = DB_query($sql);
        // Some issues observed with PHP not supporting mysqli_result::fetch_all()
        //$retval = DB_fetchAll($res, false);
        $retval = array();
        while ($A = DB_fetchArray($res, false)) {
            $retval[] = $A;
        }
        self::setCache($cache_key, $retval, 'range');
        return $retval;
    }


    /**
     * Get a range of birthdays.
     * This is intended to be used with other plugins and returns only user
     * IDs indexed by date.
     *
     * @param   string  $start  Starting date, YYYY-MM-DD
     * @param   string  $end    Ending date, YYYY-MM-DD
     * @return  array           Array of date =>array(userids)
     */
    public static function getRange($start, $end)
    {
        global $_TABLES, $_CONF;

        $cache_key = $start . '_' . $end;
        $retval = self::getCache($cache_key);
        if ($retval !== NULL) {
            return $retval;
        }

        $dt_s = new \Date($start, $_CONF['timezone']);
        $dt_e = new \Date($end, $_CONF['timezone']);
        // Find the months to retrieve
        $s_year = $dt_s->Format('Y');
        $s_month = $dt_s->Format('n');
        //$s_day = $dt_s->Format('d');
        $e_year = $dt_e->Format('Y');
        $e_month = $dt_e->Format('n');
        //$e_day = $dt_e->Format('d');
        $months = array();

        if ($e_month < $s_month) {
            // End month is less than start, must be wrapping the year
            // First get ending months up to the end of the year
            for ($i = $e_month; $i < 13; $i++) {
                $months[] = $i;
            }
            // Then get ending months in the next year, up to starting month
            for ($i = 1; $i < $s_month; $i++) {
                $months[] = $i;
            }
        } else {
            // Ending > starting, just get the months between them (inclusive)
            for ($i = $s_month; $i <= $e_month; $i++) {
                $months[] = (int)$i;
            }
        }
        $retval = array();
        $month_str = implode(',', $months);
        $sql = "SELECT * FROM {$_TABLES['birthdays']}
                WHERE month IN ($month_str)
                ORDER BY month, day";
        //echo $sql;die;
        $res = DB_query($sql);
        while ($A = DB_fetchArray($res, false)) {
            $year = $A['month'] < $s_month ? $e_year : $s_year;
            $key1 = sprintf('%d-%02d-%02d', $year, $A['month'], $A['day']);
            // Return only dates within the range
            if ($key1 < $dt_s->format('Y-m-d') || $key1 > $dt_e->format('Y-m-d')) {
                continue;
            }
            if (!isset($retval[$key1])) $retval[$key1] = array();
            $retval[$key1][] = $A['uid'];
        }
        self::setCache($cache_key, $retval, 'range');
        return $retval;
    }


    /**
     * Get a specific user's birthday for the profile page.
     *
     * @param   integer $uid    User ID
     * @return  array       Array of fields, NULL if not found.
     */
    public static function getUser($uid)
    {
        global $_TABLES;

        $uid = (int)$uid;
        $retval = self::getCache($uid);
        if ($retval === NULL) {
            $sql = "SELECT * FROM {$_TABLES['birthdays']}
                    WHERE uid = $uid";
            $res = DB_query($sql);
            if (!DB_error()) {
                $retval = DB_fetchArray($res, false);
            }
        }
        return $retval;
    }


    /**
     * Display the edit form within the profile editing screen.
     *
     * @param  integer $uid    User ID
     * @param  string  $tpl    Template name, default="edit"
     * @return string      HTML for edit form
     */
    public static function editForm($uid, $tpl = 'edit')
    {
        global $LANG_MONTH, $LANG_BD00;

        $bday = self::getInstance($uid);
        $T = new \Template(__DIR__ . '/../templates');
        $T->set_file('edit', $tpl . '.thtml');
        $opt = self::selectMonth($bday->month, $LANG_BD00['none']);
        $T->set_var('month_select', $opt);
        $opt = '';
        for ($i = 0; $i < 32; $i++) {
            $sel = $bday->day == $i ? 'selected="selected"' : '';
            if ($i > 0) {
                $opt .= "<option id=\"bday_day_$i\" $sel value=\"$i\">$i</option>";
            } else {
                $opt .= "<option $sel value=\"$i\">{$LANG_BD00['none']}</option>";
            }
        }
        $T->set_var('day_select', $opt);
        $T->set_var('month', $bday->month);
        //$T->set_var('year', $bday->year);
        $T->parse('output', 'edit');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Delete a birthday record.
     * Used if the user submits "0" for month or day
     *
     * @param   integer $uid    User ID
     */
    public static function Delete($uid)
    {
        global $_TABLES;

        DB_delete($_TABLES['birthdays'], 'uid', $uid);
        PLG_itemDeleted($uid, 'birthdays');
        self::clearCache('range');
        self::deleteCache('uid_' . $uid);
    }


    /**
     * Create the option elements for a month selection.
     * This allows for a common function used for the month selection on the
     * home page and for the user-supplied birthday.
     *
     * @param   integer $thismonth      Currently-selected month
     * @param   string  $all_prompt     String to show for "all" or "none"
     * @return  string                  Option elements
     */
    public static function selectMonth($thismonth = 0, $all_prompt = '')
    {
        global $LANG_BD00, $LANG_MONTH;

        if ($all_prompt == '') $all_prompt = $LANG_BD00['all'];
        $opt = '';
        for ($i = 0; $i < 13; $i++) {
            $sel = $thismonth == $i ? 'selected="selected"' : '';
            if ($i > 0) {
                $opt .= "<option $sel value=\"$i\">{$LANG_MONTH[$i]}</option>";
            } else {
                $opt .= "<option $sel value=\"$i\">{$all_prompt}</option>";
            }
        }
        return $opt;
    }


    /**
     * Update the cache.
     *
     * @param   string  $key    Item key
     * @param   mixed   $data   Data, typically an array
     * @param   mixed   $tag    Single or array of tags to apply
     * @return  boolean     True on success, False on error
     */
    public static function setCache($key, $data, $tag='')
    {
        if (version_compare(GVERSION, self::CACHE_GVERSION, '<')) return NULL;

        if ($tag == '')
            $tag = array(self::TAG);
        else
            $tag = array($tag, self::TAG);
        $key = self::_makeKey($key);
        return \glFusion\Cache\Cache::getInstance()->set($key, $data, $tag);
    }


    /**
     * Clear the cache by tag. By default all plugin entries are removed.
     * Entries matching all tags, including default tag, are removed.
     *
     * @param   mixed   $tag    Single or array of tags
     * @return  boolean     True on success, False on error
     */
    public static function clearCache($tag = '')
    {
        if (version_compare(GVERSION, self::CACHE_GVERSION, '<')) return;

        $tags = array(self::TAG);
        if (!empty($tag)) {
            if (!is_array($tag)) $tag = array($tag);
            $tags = array_merge($tags, $tag);
        }
        return \glFusion\Cache\Cache::getInstance()->deleteItemsByTagsAll($tags);
    }


    /**
     * Delete a single item from the cache
     *
     * @param   string  $key    Item key to delete
     * @return  boolean     True on success, False on error
     */
    public static function deleteCache($key)
    {
        $key = self::_makeKey($key);
        return \glFusion\Cache\Cache::getInstance()->delete($key);
    }


    /**
     * Create a unique cache key.
     *
     * @param   string  $key    Unique cache key
     * @return  string          Encoded key string to use as a cache ID
     */
    private static function _makeKey($key)
    {
        return self::TAG . '_' . $key;
    }


    /**
     * Get an entry from cache, if available.
     *
     * @param   string  $key    Cache key
     * @return  mixed           Cache contents, NULL if not found
     */
    public static function getCache($key)
    {
        if (version_compare(GVERSION, self::CACHE_GVERSION, '<')) return;
        $key = self::_makeKey($key);
        if (\glFusion\Cache\Cache::getInstance()->has($key)) {
            return \glFusion\Cache\Cache::getInstance()->get($key);
        } else {
            return NULL;
        }
    }

}

?>
