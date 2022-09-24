<?php

/*
 * Plugin Name: Primail - Mandrill Email Connector for WordPress
 * Description: Sends EMails from WP_Mail to Mandrill 
 * Description: The Primail plugin allows you to connect your WordPress site with Mandrill for improved email delivery and reliability
 * Version: 1.0
 * Text Domain: primail
 * Author: Mark Cummins
 * Requires PHP: 5.6.20
 * Requires at least: 5.5.1
 * Tested up to: 6.0.2
 * License: GPLv2 or later (or compatible)
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define('PRIMAIL_DIR', plugin_dir_path(__FILE__));
define('PRIMAIL_URL', plugin_dir_url(__FILE__));

/**
 * Returns the Plugin Directory Path
 *
 * @return string
 */
function get_primail_template_dir()
{
  return PRIMAIL_DIR;
}

/**
 * Returns the Plugin Directory Url
 *
 * @return string
 */
function get_primail_template_url()
{
  return PRIMAIL_URL;
}

/**
 * Returns the Plugin Name
 *
 * @return string
 */
function get_primail_plugin_name()
{
  return 'primail';
}

/**
 * Returns the Plugin Version SemVer
 *
 * @return string
 */
function get_primail_plugin_version()
{
  return '1.0.0';
}

include_once PRIMAIL_DIR . "core/request.php";
include_once PRIMAIL_DIR . "core/api.php";
include_once PRIMAIL_DIR . "core/options.php";
include_once PRIMAIL_DIR . "core/settings.php";

/**
 * Intercepts the WP Mail EMail and sends the email to Mandrill EMail
 *
 * @param null|bool Short-circuit return value
 * @param array $atts
 * 
 * @return bool
 */
function primail_intercept($null, $atts)
{
  return primail($atts);
}
add_filter('pre_wp_mail', 'primail_intercept', 10, 2);

/**
 * Add Settings link to plugins area.
 *
 * @param array  $links Links array in which we would prepend our link.
 * @param string $file  Current plugin basename.
 * @return array Processed links.
 */
function modify_primail_plugin_action_links($links, $file)
{
  if ($file !== 'primail/primail.php') {
    return $links;
  }

  $query_args = array('page' => 'primail-settings');
  $settings_link = get_admin_url('', 'options-general.php');

  $settings = array(
    'link' => add_query_arg($query_args, $settings_link),
    'label' => __('Settings',  "primail")
  );

  return array_merge($links, array(
    'settings' => "<a href='{$settings['link']}'>{$settings['label']}</a>"
  ));
}

add_filter('plugin_action_links', 'modify_primail_plugin_action_links', 10, 2);

/**
 * Sets the Default Options for when the Plugin is Activated
 *
 * @return void
 */
function primail_activate()
{
  add_option('primail_api_key', '', '', 'yes');
  add_option('primail_api_test_key', '', '', 'yes');
  add_option('primail_api_debug_enabled', 1, '', 'yes');

  $sitename   = wp_parse_url(network_home_url(), PHP_URL_HOST);
  $from_email = 'wordpress@';

  if (null !== $sitename) {
    if ('www.' === substr($sitename, 0, 4)) {
      $from_email = substr($sitename, 4);
    }

    $from_email .= $sitename;
  }
  $from_email = apply_filters('wp_mail_from', $from_email);
  add_option('primail_default_from_email', $from_email, '', 'yes');

  $from_name = apply_filters('wp_mail_from_name', 'WordPress');
  add_option('primail_default_from_name', $from_name, '', 'yes');
}
register_activation_hook(__FILE__, 'primail_activate');

/**
 * Sends an email via the Mandrill API
 *
 * @param array $atts
 * @return bool Whether the email was sent successfully.
 */
function primail($atts)
{
  $mail = new Primail($atts);
  return $mail->send();
}
