# laemmi-yourls-user-to-entry
Plugin for YOURLS 1.7

##Description
Bind login username to url entry. It generates an user create and user update entry. Localization for german.
Use this plugin with "laemmi-yourls-easy-ldap" plugin.
You must install "laemmi-yourls-default-tools" fist.

## Installation
* In /user/plugins, create a new folder named laemmi-yourls-user-to-entry.
* Drop these files in that directory.
* Via git goto /users/plugins and type git clone https://github.com/Laemmi/laemmi-yourls-user-to-entry.git
* Add config values to config file
* Go to the YOURLS Plugins administration page and activate the plugin.

### Available config values
#### Allowed ldap groupsnames with yourls action and list permissions
define('LAEMMI_EASY_LDAP_ALLOWED_GROUPS', json_encode([
    'MY-LDAP-GROUPNAME' => ['action-edit-other', 'action-delete-other', 'action-add-ldapgroup', 'action-edit-ldapgroup', 'action-add-other-ldapgroup', 'action-edit-other-ldapgroup'
     'list-show-other', 'list-show-other-group', 'list-show-own-in-other-group', 'list-show-other-in-own-group']
]));
#### ldap groupsnames to set 
define('LAEMMI_BIND_USER_TO_ENTRY_GROUPLIST', json_encode([
	'MY-LDAP-GROUPNAME' => 'MY-LDAP-GROUPNAME',
	'MY-LDAP-GROUPNAME2' => 'MY-LDAP-GROUPNAME2'
]));

### Permissions
##### Actions
* action-edit-other = Edit other url
* action-delete-other = Delete other url
* action-add-ldapgroup = Add selected ldap group
* action-edit-ldapgroup = Edit selected ldap group
* action-add-other-ldapgroup = Add other selected ldap group
* action-edit-other-ldapgroup = Edit other selected ldap group

##### List
* list-show-other = Show other url
* list-show-other-group = Show URLs from groups in which the user is not
* list-show-own-in-other-group = Show own URLs are assigned to the groups in which the user is not
* list-show-other-in-own-group = Show other URLs are assigned to the groups in which the user is in