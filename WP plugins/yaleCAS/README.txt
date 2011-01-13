Plugin Name: Yale CAS/LDAP
Description: Authenticate users for Yale-based WordPress systems using phpCAS and LDAP listings
Author: Bay Gross
License: GPLv2

---- Adapted from:
CAS_Authentication plugin
via: http://wiki.schwink.net/index.php?title=Wordpress_CAS_authentication
by: Stephen Schwink


This plugin is designed for small groups and sites at Yale University.  It allows for easy authenticates with the Yale CAS server, and also integrates with the Yale LDAP directory to autocomplete user profile information.

You have the additional option of disabling user profile changes, forcing LDAP authenticated usernames/emails for ALL users of the site.


Installation and Setup:
--------------------------
- Install this plugin to your WP plugins directory, and login with your basic admin account.
- Activate YaleCAS under 'plugins' menu
- At this point, open a second browser (don't close active session!) and navigate to your site to login.
- If setup correctly, you should be redirected to Yale CAS login page, where you can enter your credentials
- At this point the plugin will create a new user account (subscriber status) with your netted, name, email, etc.
- Return to previous window and give new account admin status before logging out.

** This is important because the plugin makes CAS login mandatory, and defaults to subscriber status.  If you don't create atleast one CAS administrator, you will lose control.
**If this happens, you can always disable the plugin by following the directions in yaleCAS.php file

Hereafter, new users are added to your site with CAS/LDAP based credentials.  They are given subscriber status initially, which can be changed by any site admin.


user-edit.php
----------
If you would like to disable user profile customization, and lock individual accounts to the data supplied by LDAP, you can replace user-edit.php (in wp-admin directory) with the adapted file provided.
*Tested with WP3.04



