<?php
/*
Plugin Name: Yale CAS/LDAP
Description: Authenticate users for Yale-based WordPress systems using phpCAS and LDAP listings
Author: Bay Gross
License: GPLv2
---- Adapted from:
CAS_Authentication plugin
via: http://wiki.schwink.net/index.php?title=Wordpress_CAS_authentication
by: Stephen Schwink
*/
$cpath= ABSPATH . 'wp-content/plugins/yaleCAS/phpCAS/CAS.php';

$cas_authentication_opt = array('include_path' => "/Users/Bay/Dropbox/programming/wordpress/YCC/wp-content/plugins/yaleCAS/phpCAS/CAS.php", 
																	'server_hostname' => 'secure.its.yale.edu', 
																	'server_path' => '/cas',
																	'server_port' => '443',
																	'cas_version' => '2.0'																								
																	);
$cas_configured = true;


if ((include_once $cas_authentication_opt['include_path']) != true)
	die("could not find CASphp files");


  phpCAS::client($cas_authentication_opt['cas_version'], 
		 $cas_authentication_opt['server_hostname'], 
		 intval($cas_authentication_opt['server_port']), 
		 $cas_authentication_opt['server_path']);
  

  // function added in phpCAS v. 0.6.0
  // checking for static method existance is frustrating in php4
  $phpCas = new phpCas();
  if (method_exists($phpCas, 'setNoCasServerValidation'))
    phpCAS::setNoCasServerValidation();
  unset($phpCas);
  // if you want to set a cert, replace the above few lines


// for wp_create_user function on line 120
require_once (ABSPATH . WPINC . '/registration.php');


//NOTE:  comment out hooks below to disable plugin if malfunctioning!!
add_action('wp_authenticate', array('CASAuthentication', 'authenticate'), 10, 2);
add_action('wp_logout', array('CASAuthentication', 'logout'));
add_action('lost_password', array('CASAuthentication', 'disable_function'));
add_action('retrieve_password', array('CASAuthentication', 'disable_function'));
add_action('password_reset', array('CASAuthentication', 'disable_function'));
add_filter('show_password_fields', array('CASAuthentication', 'show_password_fields'));
add_filter('login_url', array('CASAuthentication', 'bypass_reauth'));

if (!class_exists('CASAuthentication')) {
  class CASAuthentication {

    // password used by the plugin
    function passwordRoot() {
      return 'Authenticated through CAS';
    }    
    
    /*
     We call phpCAS to authenticate the user at the appropriate time 
     (the script dies there if login was unsuccessful)
     If the user has not logged in previously, we create an accout for them
    */
    function authenticate(&$username, &$password) {
      global $using_cookie, $cas_authentication_opt, $cas_configured;

      // Reset values from input ($_POST and $_COOKIE)
      $username = $password = '';		
      
      phpCAS::forceAuthentication();
      
      // might as well be paranoid
      if (!phpCAS::isAuthenticated())
	exit();
      
      $username = phpCAS::getUser();
      $password = md5(CASAuthentication::passwordRoot());

      if (!function_exists('get_userdatabylogin'))
					die("Could not load user data");
      		$user = get_userdatabylogin($username);

			// ----------------If user exists, login.  Else, create the user.
      if ($user)
					return true;
      else {   
	
	if (true) {  //defaulting to true, can add logic and option for this back in later. 
								//TODO: either add option to admin panel or remove conditional logic
		
		//allow new users to automatically register
	  // auto-registration is enabled

	  // User is not in the WordPress database
	  // they passed CAS and so are authorized
	  // add them to the database using yale's LDAP directory
		$conn = ldap_connect("directory.yale.edu") or die("Could not connect to Yale LDAP server");  
		$r = ldap_bind($conn) or die("Could not bind to Yale LDAP server to create new user");     
		$result = ldap_search($conn,"ou=People,o=yale.edu", "(uid=" . $username . ")") or die ("Error in search query - netID not recognized in Yale Directory");  
		$info = ldap_get_entries($conn, $result); //array of users with given netid '$username'
		ldap_close($conn);
		
	  
	  $user_info = array();
	  $user_info['user_pass'] = $password;
	  $user_info['user_login'] = $username;
		$user_info['user_email'] = $info[0]["mail"][0];    //info is array of hashes (for each user).  mail is symbol in user-hash representing second array of emails.
	  $user_info['user_first'] = $info[0]["givenname"][0];
	  $user_info['user_last'] = $info[0]["sn"][0];
		$user_info['nice_name'] = $info[0]["givenname"][0] + " " + $info[0]["sn"][0];
	  wp_insert_user($user_info);
	}
	
	else {
	  // auto-registration is disabled
	  
	  $error = sprintf(__('<p><strong>ERROR</strong>: %s is not registered with this blog. Please contact the <a href="mailto:%s">blog administrator</a> to create a new account!</p>'), $username, get_option('admin_email'));
	  $errors['registerfail'] = $error;
	  print($error);
	  print('<p><a href="/wp-login.php?action=logout">Log out</a> of CAS.</p>');
	  exit();
	}
      }
    }
    
    
    /*
     We use the provided logout method
    */
    function logout() {
      global $cas_configured;

      if (!$cas_configured)
	die("cas-authentication not configured");

      phpCAS::logoutWithUrl(get_settings('siteurl'));
      exit();
    }
    
    /*
     * Remove the reauth=1 parameter from the login URL, if applicable. This allows
     * us to transparently bypass the mucking about with cookies that happens in
     * wp-login.php immediately after wp_signon when a user e.g. navigates directly
     * to wp-admin.
     */
    function bypass_reauth($login_url) {
        $login_url = remove_query_arg('reauth', $login_url);
        return $login_url;
    }

    /*
     Don't show password fields on user profile page.
    */
    function show_password_fields($show_password_fields) {
      return false;
    }


    function disable_function() {
      die('Disabled');
    }
    
  }
 }

