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
    'MY-LDAP-GROUPNAME' => ['action-edit-other', 'action-delete-other', 'list-show-other']
]));

### Permissions
##### Actions
* action-edit-other = Edit other url
* action-delete-other = Delete other url

##### List
* list-show-other = Show other url

