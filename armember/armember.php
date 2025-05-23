<?php 
/*
  Plugin Name: ARMember - Complete Membership Plugin
  Description: The most powerful membership plugin to handle any complex membership wordpress sites with super ease.
  Version: 6.9.8
  Requires at least: 5.0
  Requires PHP: 5.6
  Plugin URI: https://www.armemberplugin.com
  Author: Repute Infosystems
  Text Domain: ARMember
  Domain Path: /languages
  Author URI: https://www.armemberplugin.com

 */

define('MEMBERSHIP_DIR_NAME', 'armember');
define('MEMBERSHIP_DIR', WP_PLUGIN_DIR . '/' . MEMBERSHIP_DIR_NAME);

require_once MEMBERSHIP_DIR.'/autoload.php';