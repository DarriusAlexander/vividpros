=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.4
Tested up to: 5.1.1
Stable tag: 4.50.3
Requires PHP: 5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

User Role Editor WordPress plugin makes user roles and capabilities changing easy. Edit/add/delete WordPress user roles and capabilities.

== Description ==

User Role Editor WordPress plugin allows you to change user roles and capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. Changes made to the roles will stay unchanged.
You will have to configure lost part of the settings at the User Role Editor Pro Settings page again after that.
Right decision in this case is to delete free version folder (user-role-editor) after deactivation via FTP, not via WordPress.

== Changelog ==

= [4.50.3] 16.03.2019 =
* Core version: 4.50.1
* Fix: Admin menu access add-on: 
*   - "return=<...>" argument was not removed properly from "customize.php" URLs linked to "Appearance->Customize" and "Appearance->Header" submenu items. 
  Attention! Reopen your "Admin menu" settings from the restricted roles and check if these submenu items are still blocked after installing this update.
*   - "Forms->System Status" menu item of "Gravity Forms" plugin was not supported properly. PHP Notice: "Undefined index: gf_system_status in \wp-content\plugins\user-role-editor-pro\pro\includes\classes\admin-menu-view.php on line 108" was generated and broke the JSON response.
* Fix: Network Admin->Users->Capabilities->Network Update: Fatal error: Warning: call_user_func_array() expects parameter 1 to be a valid callback, class 'URE_Editor_Pro' not found
* Fix: Network Admin->Users->Capabilities->Network Update: Fatal error: Uncaught Error: Using $this when not in object context in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/editor-ext.php on line 103
* Core version was updated to 4.50.1:
* Fix: WP Multisite: Users->Capabilities->Update: "Fatal error: Uncaught Error: Call to undefined method URE_Editor::check_blog_user() in /wp-content/plugins/user-role-editor-pro/includes/classes/editor.php on line 576" was fixed. 
* Fix: WooCommerce group was not shown under Custom capabilities section.

= [4.50.2] 05.03.2019 =
* Core version: 4.50
* Fix: Meta boxes access add-on: PHP fatal error was fixed: Uncaught Error: Call to undefined method URE_Lib_Pro::set_notification() in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/meta-boxes-access.php:104
* Fix: Posts view access add-on: PHP fatal error was fixed: Uncaught Error: Call to undefined method URE_Lib_Pro::set_notification() in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/posts-view-access.php:99

= [4.50.1] 04.03.2019 =
* Core version: 4.50
* Fix: Role import: Input data control was added to exclude PHP warnings, like "PHP Warning:  array_walk_recursive() expects parameter 1 to be array, null given". Error message was replaced with "Role file is broken or has invalid format".
* Fix: Front-end menu access add-on: Bug prevented this add-on normal loading.
* Fix: Other roles access add-on: PHP fatal error was fixed: Uncaught Error: Call to undefined method URE_Lib_Pro::set_notification() in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/other-roles-access.php:109

= [4.50] 04.03.2019 =
* Core version: 4.50
* New: It's possible to export all user roles from current site to CSV file. Go to "Settings->User Role Editor->Tools" and click "Export" button at "Export user roles to CSV file" section.
* New: Multisite: Plugins list access for activation/deactivation restrictions is possible to replicate from the main site to the whole network. User "Update Network" button from "Network admin->Users->User Role Editor".
* Fix: Content view restrictions add-on: There was a conflict with bbPress 'posts_request' filter 'bbp_has_replies_where', which returned wrong result in       case content view restrictions add-on was active. Description was not shown for the not restricted topics.
* Core version was updated to 4.50:
* PHP version 5.5 was marked as required.
* Update: General code restructure and optimization.
* Update: URE_Base_Lib::get_blog_ids() returns null, if it's called under WordPress single site (not multisite).
* Update: URE_Editor::prepare_capabilities_to_save() : "Invalid argument supplied for foreach()" warning was excluded in case there was no valid data structures initialization.
* Update: 'administrator' role protection was enhanced. URE always does not allow to revoke capability from 'administrator' role. That was possible earlier after the 'administrator' role update.
* Update: 2 new actions 'ure_settings_tools_show' and 'ure_settings_tools_exec' allows to extends the list of sections available at the Settings->User Role Editor->Tools tab.

Full list of changes is available in changelog.txt file.

== Upgrade Notice ==
= [4.50.3] 16.03.2019 =
* Core version: 4.50.1
* Fix: Admin menu access add-on: 
*   - "return=<...>" argument was not removed properly from "customize.php" URLs linked to "Appearance->Customize" and "Appearance->Header" submenu items. 
  Attention! Reopen your "Admin menu" settings from the restricted roles and check if these submenu items are still blocked after installing this update.
*   - "Forms->System Status" menu item of "Gravity Forms" plugin was not supported properly. PHP Notice: "Undefined index: gf_system_status in \wp-content\plugins\user-role-editor-pro\pro\includes\classes\admin-menu-view.php on line 108" was generated and broke the JSON response.
* Fix: Network Admin->Users->Capabilities->Network Update: Fatal error: Warning: call_user_func_array() expects parameter 1 to be a valid callback, class 'URE_Editor_Pro' not found
* Fix: Network Admin->Users->Capabilities->Network Update: Fatal error: Uncaught Error: Using $this when not in object context in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/editor-ext.php on line 103
* Core version was updated to 4.50.1:
* Fix: WP Multisite: Users->Capabilities->Update: "Fatal error: Uncaught Error: Call to undefined method URE_Editor::check_blog_user() in /wp-content/plugins/user-role-editor-pro/includes/classes/editor.php on line 576" was fixed. 
* Fix: WooCommerce group was not shown under Custom capabilities section.


