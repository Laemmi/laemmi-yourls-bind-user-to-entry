<?php
/**
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category    Laemmi\Yourls\Bind\User\To\Entry
 * @package     Laemmi\Yourls\Bind\User\To\Entry
 * @author      Michael Lämmlein <ml@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       20.10.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\Bind\User\To\Entry;

use Laemmi\Yourls\Plugin\AbstractDefault;

/**
 * Class Plugin
 *
 * @package Laemmi\Yourls\Bind\User\To\Entry
 */
class Plugin extends AbstractDefault
{
    /**
     * Namespace
     */
    const APP_NAMESPACE = 'laemmi-yourls-bind-user-to-entry';

    /**
     * Settings constants
     */
    const SETTING_URL_USER_CREATE = 'laemmi_user_create';
    const SETTING_URL_USER_UPDATE = 'laemmi_user_update';
    const SETTING_URL_TIMESTAMP_UPDATE = 'laemmi_timestamp_update';

    /**
     * Permission constants
     */
    const PERMISSION_ACTION_EDIT = 'action-edit-other';
    const PERMISSION_ACTION_DELETE = 'action-delete-other';
    const PERMISSION_LIST_SHOW = 'list-show-other';

    /**
     * Settings for url table
     *
     * @var array
     */
    protected $_setting_url = [
        self::SETTING_URL_USER_CREATE => ["field" => "VARCHAR(255) NULL"],
        self::SETTING_URL_USER_UPDATE => ["field" => "VARCHAR(255) NULL"],
        self::SETTING_URL_TIMESTAMP_UPDATE => ["field" => "TIMESTAMP"]
    ];

    /**
     * Options
     *
     * @var array
     */
    protected $_options = [
        'allowed_groups' => []
    ];

    /**
     * Admin permissions
     *
     * @var array
     */
    protected $_adminpermission = [
        self::PERMISSION_ACTION_EDIT, self::PERMISSION_ACTION_DELETE, self::PERMISSION_LIST_SHOW
    ];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->startSession();
        parent::__construct($options);
    }

    ####################################################################################################################

    /**
     * Yourls action plugins_loaded
     */
    public function action_plugins_loaded()
    {
        yourls_load_custom_textdomain(self::APP_NAMESPACE, realpath(dirname( __FILE__ ) . '/../translations'));
    }

    /**
     * Action activated_plugin
     *
     * @param array $args
     * @throws \Exception
     */
    public function action_activated_plugin(array $args)
    {
        list($plugin) = $args;

        if(false === stripos($plugin, self::APP_NAMESPACE)) {
            return;
        }

        foreach($this->_setting_url as $key => $val) {
            $this->addUrlSetting($key, $val);
        }
    }

    /**
     * Action deactivated_plugin
     *
     * @param array $args
     * @throws \Exception
     */
    public function action_deactivated_plugin(array $args)
    {
        list($plugin) = $args;

        if(false === stripos($plugin, self::APP_NAMESPACE)) {
            return;
        }

//        foreach($this->_setting_url as $key => $val) {
//            $this->dropUrlSetting($key, $val);
//        }
    }

    /**
     * Action insert_link
     *
     * @param array $args
     * @throws \Exception
     */
    public function action_insert_link(array $args)
    {
        list($insert, $url, $keyword, $title, $timestamp, $ip) = $args;

        $this->updateUrlSetting([
            self::SETTING_URL_USER_CREATE => YOURLS_USER,
            self::SETTING_URL_USER_UPDATE => YOURLS_USER,
            self::SETTING_URL_TIMESTAMP_UPDATE => $this->getDateTime()->format('c'),
        ], $keyword);

        // Use in table_add_row_cell_array
        global $url_result;
        $url_result = new \stdClass();
        $url_result->{self::SETTING_URL_USER_CREATE} = YOURLS_USER;
        $url_result->{self::SETTING_URL_TIMESTAMP_UPDATE} = $this->getDateTime()->format('c');
        $url_result->{self::SETTING_URL_USER_UPDATE} = YOURLS_USER;
    }

    ####################################################################################################################

    /**
     * Filter edit_link
     *s
     * @return mixed
     * @throws \Exception
     */
    public function filter_edit_link()
    {
        list($return, $url, $keyword, $newkeyword, $title, $new_url_already_there, $keyword_is_ok) = func_get_args();

        if ((! $new_url_already_there || yourls_allow_duplicate_longurls()) && $keyword_is_ok) {
            $this->updateUrlSetting([
                self::SETTING_URL_USER_UPDATE => YOURLS_USER,
                self::SETTING_URL_TIMESTAMP_UPDATE => $this->getDateTime()->format('c'),
            ], ($newkeyword ? $newkeyword : $keyword));
        }

        return $return;
    }

    /**
     * Filter table_add_row_cell_array
     *
     * @return mixed
     */
    public function filter_table_add_row_cell_array()
    {
        global $url_result;

        list($cells, $keyword, $url, $title, $ip, $clicks, $timestamp) = func_get_args();

        if(!isset($url_result)) {
            return $cells;
        }

        $cells['timestamp']['template'] = '<div><span>' . yourls__('Create', self::APP_NAMESPACE) . ':</span> <span>%date%</span> <span>%user_create%</span></div>';
        $cells['timestamp']['date'] = $this->getDateTimeDisplay($timestamp)->format('d.m.Y H:i');

        if($url_result->{self::SETTING_URL_USER_CREATE}) {
            if(YOURLS_USER === $url_result->{self::SETTING_URL_USER_CREATE}) {
                $cells['timestamp']['user_create'] = '<strong>' . $url_result->{self::SETTING_URL_USER_CREATE} . '</strong>';
            } else {
                $cells['timestamp']['user_create'] = $url_result->{self::SETTING_URL_USER_CREATE};
            }
        } else {
            $cells['timestamp']['user_create'] = '';
        }

        if(0 < strtotime($url_result->{self::SETTING_URL_TIMESTAMP_UPDATE})) {
            $cells['timestamp']['template'] .=  '<div><span>' . yourls__('Changed', self::APP_NAMESPACE) .':</span> <span>%date_update%</span> <span>%user_update%</span></div>';
            $cells['timestamp']['date_update'] = $this->getDateTimeDisplay($url_result->{self::SETTING_URL_TIMESTAMP_UPDATE})->format('d.m.Y H:i');
            if(YOURLS_USER === $url_result->{self::SETTING_URL_USER_UPDATE}) {
                $cells['timestamp']['user_update'] = '<strong>' . $url_result->{self::SETTING_URL_USER_UPDATE} . '</strong>';
            } else {
                $cells['timestamp']['user_update'] = $url_result->{self::SETTING_URL_USER_UPDATE};
            }
        }

        return $cells;
    }

    /**
     * Yourls filter table_add_row_action_array
     *
     * @param $data
     * @return array
     */
    public function filter_table_add_row_action_array()
    {
        global $url_result;

        list($actions) = func_get_args();

        if(! isset($url_result)) {
            return array();
        }

        $permissions = $this->helperGetAllowedPermissions();

        if(! isset($permissions[self::PERMISSION_ACTION_EDIT])) {
            if($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                unset($actions['edit']);
            }
        }

        if(! isset($permissions[self::PERMISSION_ACTION_DELETE])) {
            if($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                unset($actions['delete']);
            }
        }

        return $actions;
    }

    /**
     * Yourls filter admin_list_where
     *
     * @return string
     */
    public function filter_admin_list_where()
    {
        list($where) = func_get_args();

        $permissions = $this->helperGetAllowedPermissions();

        if(! isset($permissions[self::PERMISSION_LIST_SHOW])) {
            $or = [
                self::SETTING_URL_USER_CREATE . " IS NULL",
                self::SETTING_URL_USER_CREATE . " = '" . YOURLS_USER . "'"
            ];

            $where .= " AND (" . implode(' OR ', $or) . ")";
        }

        return $where;
    }

    ####################################################################################################################

    /**
     * Get allowed permissions
     *
     * @return array
     */
    private function helperGetAllowedPermissions()
    {
        if($this->getSession('login', 'laemmi-yourls-easy-ldap')) {
            $inter = array_intersect_key($this->_options['allowed_groups'], $this->getSession('groups', 'laemmi-yourls-easy-ldap'));
            $permissions = [];
            foreach ($inter as $val) {
                foreach ($val as $_val) {
                    $permissions[$_val] = $_val;
                }
            }
        } else {
            $permissions = array_combine($this->_adminpermission, $this->_adminpermission);
        }

        return $permissions;
    }
}