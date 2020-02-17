# laemmi-yourls-user-to-entry
Plugin for YOURLS 1.7

## Description
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
        'MY-LDAP-GROUPNAME' => ['action-edit-other', 'action-delete-other', 'action-add-project', 'action-edit-project', 'action-add-other-project', 'action-edit-other-project'
         'list-show-other', 'list-show-other-project', 'list-show-own-in-other-project', 'list-show-other-in-own-project']
    ]));
#### projects to set 
    define('LAEMMI_BIND_USER_TO_ENTRY_PROJECTLIST', json_encode([
        'Project 1' => [
            'LDAP-GROUPNAME' => 'permissions .....'
        ],
        'Project 2' => [
            'LDAP-GROUPNAME' => 'permissions .....'
        ]
    ]));

### Permissions
##### Actions
* action-edit-other = Edit other url
* action-delete-other = Delete other url
* action-add-project = Add selected project
* action-edit-project = Edit selected project
* action-add-other-project = Add other selected project
* action-edit-other-project = Edit other selected project

##### List
* list-show-other = Show other url
* list-show-other-project = Show URLs from projects in which the user is not
* list-show-own-in-other-project = Show own URLs are assigned to the projects in which the user is not
* list-show-other-in-own-project = Show other URLs are assigned to the projects in which the user is in