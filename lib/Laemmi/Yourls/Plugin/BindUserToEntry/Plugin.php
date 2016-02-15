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
 * @category    laemmi-yourls-bind-user-to-entry
 * @package     Plugin.php
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2015-2016 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       20.10.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\Plugin\BindUserToEntry;

use Laemmi\Yourls\Plugin\AbstractDefault;

/**
 * Class Plugin
 *
 * @package Laemmi\Yourls\Plugin\BindUserToEntry
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
    const SETTING_URL_PROJECTS = 'laemmi_projects';

    /**
     * Permission constants
     */
    const PERMISSION_ACTION_EDIT_OTHER = 'action-edit-other';
    const PERMISSION_ACTION_DELETE_OTHER = 'action-delete-other';
    const PERMISSION_LIST_SHOW_OTHER = 'list-show-other';
    const PERMISSION_LIST_SHOW_OTHER_PROJECT = 'list-show-other-project';
    const PERMISSION_LIST_SHOW_OWN_IN_OTHER_PROJECT = 'list-show-own-in-other-project';
    const PERMISSION_LIST_SHOW_OTHER_IN_OWN_PROJECT = 'list-show-other-in-own-project';
    const PERMISSION_ACTION_ADD_PROJECT = 'action-add-project';
    const PERMISSION_ACTION_EDIT_PROJECT = 'action-edit-project';
    const PERMISSION_ACTION_ADD_OTHER_PROJECT = 'action-add-other-project';
    const PERMISSION_ACTION_EDIT_OTHER_PROJECT = 'action-edit-other-project';

    const PERMISSION_ACTION_EDIT = 'action-edit';
    const PERMISSION_ACTION_DELETE = 'action-delete';

    /**
     * Settings for url table
     *
     * @var array
     */
    protected $_setting_url = [
        self::SETTING_URL_USER_CREATE => ["field" => "VARCHAR(255) NULL"],
        self::SETTING_URL_USER_UPDATE => ["field" => "VARCHAR(255) NULL"],
        self::SETTING_URL_TIMESTAMP_UPDATE => ["field" => "TIMESTAMP"],
        self::SETTING_URL_PROJECTS => ["field" => "TEXT"]
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
        self::PERMISSION_ACTION_EDIT_OTHER,
        self::PERMISSION_ACTION_DELETE_OTHER,
        self::PERMISSION_LIST_SHOW_OTHER,
        self::PERMISSION_LIST_SHOW_OTHER_PROJECT,
        self::PERMISSION_LIST_SHOW_OWN_IN_OTHER_PROJECT,
        self::PERMISSION_ACTION_ADD_PROJECT,
        self::PERMISSION_ACTION_EDIT_PROJECT,
        self::PERMISSION_ACTION_ADD_OTHER_PROJECT,
        self::PERMISSION_ACTION_EDIT_OTHER_PROJECT,

        self::PERMISSION_ACTION_EDIT,
        self::PERMISSION_ACTION_DELETE,
    ];

    /**
     * Init
     */
    public function init()
    {
        $this->startSession();
        $this->initTemplate();
    }

    ####################################################################################################################

    /**
     * Yourls action plugins_loaded
     */
    public function action_plugins_loaded()
    {
        $this->loadTextdomain();
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
     * Action: html_head
     *
     * @param array $args
     */
    public function action_html_head(array $args)
    {
        list($context) = $args;

        if('index' === $context) {
            echo $this->getJsScript('assets/admin.js');
            echo $this->getCssStyle();
        }
    }

    ####################################################################################################################

    /**
     * Action insert_link
     *
     * @param array $args
     * @throws \Exception
     */
    public function action_insert_link(array $args)
    {
        list($insert, $url, $keyword, $title, $timestamp, $ip) = $args;

        if($this->_hasPermission(self::PERMISSION_ACTION_ADD_PROJECT)) {
            $project = $this->getRequest('project');
            $data = is_array($project)?$project:[];
        } else {
            $groups = $this->_getOwnGroups();
            $data = [key($groups)];
        }

        $infos = yourls_get_keyword_infos($keyword);
        if($infos) {
            $projectlist = array_flip((array) @json_decode($infos[self::SETTING_URL_PROJECTS], true));
            $owngroups = $this->_getOwnGroups();
            $diff = array_diff_key($projectlist, $owngroups);
            $data = array_merge($data, array_flip($diff));
        }

        $this->updateUrlSetting([
            self::SETTING_URL_USER_CREATE => YOURLS_USER,
            self::SETTING_URL_USER_UPDATE => YOURLS_USER,
            self::SETTING_URL_TIMESTAMP_UPDATE => $this->getDateTime()->format('c'),
            self::SETTING_URL_PROJECTS => $data ? json_encode($data) : null,
        ], $keyword);

        // Use in table_add_row_cell_array
        global $url_result;
        $url_result = new \stdClass();
        $url_result->{self::SETTING_URL_USER_CREATE} = YOURLS_USER;
        $url_result->{self::SETTING_URL_TIMESTAMP_UPDATE} = $this->getDateTime()->format('c');
        $url_result->{self::SETTING_URL_USER_UPDATE} = YOURLS_USER;
        $url_result->{self::SETTING_URL_PROJECTS} = json_encode($data);
    }

    ####################################################################################################################

    /**
     * Filter edit_link
     *
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

        $cells['timestamp']['template'] = '<div class="display-large"><div><span>' . yourls__('Create', self::APP_NAMESPACE) . ':</span> <span>%date%</span> <span>%user_create%</span></div>';
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
            $cells['timestamp']['template'] .= '<div><span>' . yourls__('Changed', self::APP_NAMESPACE) .':</span> <span>%date_update%</span> <span>%user_update%</span></div>';
            $cells['timestamp']['date_update'] = $this->getDateTimeDisplay($url_result->{self::SETTING_URL_TIMESTAMP_UPDATE})->format('d.m.Y H:i');
            if(YOURLS_USER === $url_result->{self::SETTING_URL_USER_UPDATE}) {
                $cells['timestamp']['user_update'] = '<strong>' . $url_result->{self::SETTING_URL_USER_UPDATE} . '</strong>';
            } else {
                $cells['timestamp']['user_update'] = $url_result->{self::SETTING_URL_USER_UPDATE};
            }
        }

        if($url_result->{self::SETTING_URL_PROJECTS}) {
            $cells['timestamp']['template'] .= '<div><span>' . yourls__('Projects', self::APP_NAMESPACE) . ':</span> <span>%ldap_groups%</span></div>';
            $cells['timestamp']['ldap_groups'] = '';
            $arr = json_decode($url_result->{self::SETTING_URL_PROJECTS}, true);
            if (is_array($arr)) {
                $ldap_groups = array_map(function ($val) {
                    if (isset($this->_options['projectlist'][$val])) {
                        return $val;
                        return $this->_options['projectlist'][$val];
                    }
                }, $arr);
                $cells['timestamp']['ldap_groups'] = implode(', ', $ldap_groups);
            }
        }

        $cells['timestamp']['template'] .= '</div>';

        $title = [];
        $cells['timestamp']['date_small'] = $this->getDateTimeDisplay($timestamp)->format('d.m.Y');
        if($cells['timestamp']['user_create']) {
            $title[] = yourls__('Create', self::APP_NAMESPACE) . ': %date% (%user_create_small%)';
        } else {
            $title[] = yourls__('Create', self::APP_NAMESPACE) . ': %date%';
        }

        if(isset($cells['timestamp']['date_update'])) {
            $cells['timestamp']['date_small'] = $this->getDateTimeDisplay($url_result->{self::SETTING_URL_TIMESTAMP_UPDATE})->format('d.m.Y');
            $cells['timestamp']['user_update_small'] = strip_tags($cells['timestamp']['user_update']);
            $title[] = yourls__('Changed', self::APP_NAMESPACE) . ': %date_update% (%user_update_small%)';
        }

        if(isset($cells['timestamp']['ldap_groups'])) {
            $cells['timestamp']['ldap_groups_small'] = $cells['timestamp']['ldap_groups'];
            $title[] = yourls__('Projects', self::APP_NAMESPACE) . ': %ldap_groups_small%';
        }

        $title[] = yourls__('IP') . ': ' . $cells['ip']['ip'];

        $cells['timestamp']['template'] .= '<div title="' . implode('&#13;', $title) .'" class="display-small">%date_small%</div>';
        $cells['timestamp']['user_create_small'] = strip_tags($cells['timestamp']['user_create']);

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
        global $url_result, $keyword;

        list($actions) = func_get_args();

        if(! isset($url_result)) {
            return array();
        }

        if(! $this->_hasPermission(self::PERMISSION_ACTION_EDIT_OTHER)) {
            if (!$this->_hasPermission(self::PERMISSION_ACTION_EDIT, $url_result->{self::SETTING_URL_PROJECTS})) {
                if ($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                    unset($actions['edit']);
                }
            }
        }

        if(! $this->_hasPermission(self::PERMISSION_ACTION_DELETE_OTHER)) {
            if (!$this->_hasPermission(self::PERMISSION_ACTION_DELETE, $url_result->{self::SETTING_URL_PROJECTS})) {
                if ($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                    unset($actions['delete']);
                }
            }
        }

        if(! $this->_hasPermission(self::PERMISSION_ACTION_EDIT_OTHER)) {
            if (!$this->_hasPermission(self::PERMISSION_ACTION_EDIT_PROJECT, $url_result->{self::SETTING_URL_PROJECTS})) {
                if ($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                    return $actions;
                }
            }
        }

        $id = yourls_string2htmlid($keyword);

        $href = yourls_nonce_url(
            'laemmi_edit_ldapgroup_' . $id,
            yourls_add_query_arg(['action' => 'laemmi_edit_ldapgroup', 'keyword' => $keyword], yourls_admin_url('admin-ajax.php'))
        );

        $actions['laemmi_edit_ldapgroup'] = [
            'href' => $href,
            'id' => '',
            'title' => yourls__('Edit Projects', self::APP_NAMESPACE),
            'anchor' => 'edit_ldapgroup',
            'onclick' => ''
        ];

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

        $where = $this->_getQuery($where);

        return $where;
    }

    /**
     * Yourls filter get_db_stats
     *
     * @return array
     */
    public function filter_get_db_stats()
    {
        list($return, $where) = func_get_args();

        $where = $this->_getQuery($where);

        if($where) {
            $where = 'WHERE 1=1 ' . $where;
        }

        $sql = 'SELECT
          COUNT(keyword) AS count_keyword,
          SUM(clicks) AS sum_clicks
          FROM ' . YOURLS_DB_TABLE_URL . '
          %s';

        $result = $this->db()->get_row(sprintf($sql, $where));
        $return = array('total_links' => $result->count_keyword, 'total_clicks' => $result->sum_clicks);

        return $return;
    }

    ####################################################################################################################

    /**
     * Action: yourls_ajax_laemmi_edit_ldapgroup
     */
    public function action_yourls_ajax_laemmi_edit_ldapgroup()
    {
        $keyword = yourls_sanitize_string($this->getRequest('keyword'));
        $nonce = $this->getRequest('nonce');
        $id = yourls_string2htmlid($keyword);

        yourls_verify_nonce('laemmi_edit_ldapgroup_' . $id, $nonce, false, 'omg error');

        $nonce = yourls_create_nonce('laemmi_edit_ldapgroup_save_' . $id);

        $infos = yourls_get_keyword_infos($keyword);
        $projectlist_value = (array) @json_decode($infos[self::SETTING_URL_PROJECTS], true);

        $projectlist = [];
        foreach($this->_options['projectlist'] as $key => $val) {
            if($this->_hasPermission(self::PERMISSION_ACTION_ADD_OTHER_PROJECT) || $this->_hasPermission('action-edit', [$key])) {
                $projectlist[$key] = $key;
            }
        }

        $html = $this->getTemplate()->render('edit_row_project', [
            'keyword' => $keyword,
            'nonce' => $nonce,
            'id' => $id,
            'projectlist' => $projectlist,
            'projectlist_value' => $projectlist_value,
        ]);

        echo json_encode(['html' => $html]);
    }

    /**
     * Action: yourls_ajax_laemmi_edit_ldapgroup_save
     */
    public function action_yourls_ajax_laemmi_edit_ldapgroup_save()
    {
        $keyword = yourls_sanitize_string($this->getRequest('keyword'));
        $nonce = $this->getRequest('nonce');
        $id = yourls_string2htmlid($keyword);

        yourls_verify_nonce('laemmi_edit_ldapgroup_save_' . $id, $nonce, false, 'omg error');

        $this->action_insert_link(['', '', $keyword, '', '', '']);

        $return = [];
        $return['status']  = 'success';
        $return['message'] = yourls__('Link updated in database', self::APP_NAMESPACE);

        echo json_encode($return);
    }

    ####################################################################################################################

    /**
     * Get sql query
     *
     * @param $where
     * @return string
     */
    private function _getQuery($where)
    {
        $or_owngroups = $this->_getOwnGroups();
        array_walk($or_owngroups, function (&$val) {
            $val = self::SETTING_URL_PROJECTS . " RLIKE '\"" . $val . "\"'";
        });

        if(! $this->_hasPermission(self::PERMISSION_LIST_SHOW_OTHER)) {
            $or = [];
            if($this->_hasPermission(self::PERMISSION_LIST_SHOW_OTHER_IN_OWN_PROJECT)) {
                $or = $or_owngroups;
            }

            $or[] = self::SETTING_URL_USER_CREATE . " IS NULL";
            $or[] = self::SETTING_URL_USER_CREATE . " = '" . YOURLS_USER . "'";

            $where .= " AND (" . implode(' OR ', $or) . ")";
        }

        if(! $this->_hasPermission(self::PERMISSION_LIST_SHOW_OTHER_PROJECT)) {
            $or = $or_owngroups;
            $or[] = self::SETTING_URL_PROJECTS . " IS NULL";

            if($this->_hasPermission(self::PERMISSION_LIST_SHOW_OWN_IN_OTHER_PROJECT)) {
                $or[] = self::SETTING_URL_USER_CREATE . " = '" . YOURLS_USER . "'";
            }

            $where .= " AND (" . implode(' OR ', $or) . ")";
        }

        $projectlist = $this->getRequest('projectlist');
        if($projectlist) {
            $projectlist = array_filter($projectlist);
            array_walk($projectlist, function (&$val) {
                $val = self::SETTING_URL_PROJECTS . " RLIKE '\"" . $val . "\"'";
            });
            if($projectlist) {
                $where .= " AND (" . implode(' OR ', $projectlist) . ")";
            }
        }

        return $where;
    }

    /**
     * Get own groups
     *
     * @return array
     */
    private function _getOwnGroups()
    {
        return $this->_getAreaByPermission('admin');
    }

    private function _getAreaByPermission($permission)
    {
        $arr = array();
        foreach ($this->_options['projectlist'] as $key => $val) {
            $inter = array_intersect_key($val, $this->getSession('groups', 'laemmi-yourls-easy-ldap'));
            if($inter) {
                foreach($inter as $_val) {
                    if (in_array($permission, $_val)) {
                        $arr[] = $key;
                    }
                }
            }
        }
        return $arr;
    }

    ####################################################################################################################

    /**
     * Get allowed permissions
     *
     * @param array $projects
     * @return array
     */
    protected function helperGetAllowedPermissions(array $projects = array())
    {
        if($this->getSession('login', 'laemmi-yourls-easy-ldap')) {
            $inter = array_intersect_key($this->_options['allowed_groups'], $this->getSession('groups', 'laemmi-yourls-easy-ldap'));

            if($projects) {
                $inter2 = array();
                foreach ($projects as $val) {
                    foreach ($this->_options['projectlist'] as $_key => $_val) {
                        if ($val === $_key) {
                            $interX = array_intersect_key($_val, $this->getSession('groups', 'laemmi-yourls-easy-ldap'));
                            $inter2 = array_merge($inter2, $interX);
                        }
                    }
                }
                $inter = $inter2;
            }

            $permissions = [];
            foreach ($inter as $key => $val) {
                foreach ($val as $_val) {
                    $permissions[$_val] = $_val;
                }
            }
        } else {
            $permissions = array_combine($this->_adminpermission, $this->_adminpermission);
        }

        return $permissions;
    }

    /**
     * Has permission to right
     *
     * @param $permission
     * @param string $projects
     * @return bool
     */
    protected function _hasPermission($permission, $projects = '')
    {
        if(!is_array($projects)) {
            $projects = (array)@json_decode($projects, true);
        }

        $permissions = $this->helperGetAllowedPermissions($projects);

        return isset($permissions[$permission]);
    }
}