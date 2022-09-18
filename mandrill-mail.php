<?php

/*
 * Plugin Name: Mandrill Mail
 * Description: Redirects EMails from WP_Mail to Mandrill  
 * Version: 1.0
 * Text Domain: mandrill_mail
 * Author: Mark Cummins
 * Requires PHP:      5.6.20
 * Requires at least: 5.5.1
*/

define('MANDRILL_MAIL_DIR', plugin_dir_path(__FILE__));
define('MANDRILL_MAIL_URL', plugin_dir_url(__FILE__));

/**
 * Returns the Plugin Directory Path
 *
 * @return string
 */
function get_mandrill_mail_template_dir()
{
  return MANDRILL_MAIL_DIR;
}

/**
 * Returns the Plugin Directory Url
 *
 * @return string
 */
function get_mandrill_mail_template_url()
{
  return MANDRILL_MAIL_URL;
}

include_once MANDRILL_MAIL_DIR . "core/request.php";
include_once MANDRILL_MAIL_DIR . "core/api.php";
include_once MANDRILL_MAIL_DIR . "core/options.php";
include_once MANDRILL_MAIL_DIR . "core/settings.php";

/**
 * Intercepts the WP Mail EMail and sends the email to Mandrill EMail
 *
 * @param null|bool Short-circuit return value
 * @param array $atts
 * 
 * @return bool
 */
function mandrill_mail_intercept($null, $atts)
{
  return mandrill_mail($atts);
}
add_filter('pre_wp_mail', 'mandrill_mail_intercept', 10, 2);

/**
 * Add Settings link to plugins area.
 *
 * @param array  $links Links array in which we would prepend our link.
 * @param string $file  Current plugin basename.
 * @return array Processed links.
 */
function modify_mandrill_mail_plugin_action_links($links, $file)
{
  if ($file !== 'mandrill-mail/mandrill-mail.php') {
    return $links;
  }

  $query_args = array('page' => 'mandrill-mail-settings');
  $settings_link = get_admin_url('', 'options-general.php');

  $settings = array(
    'link' => add_query_arg($query_args, $settings_link),
    'label' => __('Settings', 'mandrill_mail')
  );

  return array_merge($links, array(
    'settings' => "<a href='{$settings['link']}'>{$settings['label']}</a>"
  ));
}

add_filter('plugin_action_links', 'modify_mandrill_mail_plugin_action_links', 10, 2);

/**
 * Sets the Default Options for when the Plugin is Activated
 *
 * @return void
 */
function wp_mail_activate()
{
  add_option('mandrill_mail_api_key', '', '', 'yes');
  add_option('mandrill_mail_api_test_key', '', '', 'yes');
  add_option('mandrill_mail_api_debug_enabled', 1, '', 'yes');

  $sitename   = wp_parse_url(network_home_url(), PHP_URL_HOST);
  $from_email = 'wordpress@';

  if (null !== $sitename) {
    if ('www.' === substr($sitename, 0, 4)) {
      $from_email = substr($sitename, 4);
    }

    $from_email .= $sitename;
  }
  $from_email = apply_filters('wp_mail_from', $from_email);
  add_option('mandrill_mail_default_from_email', $from_email, '', 'yes');

  $from_name = apply_filters('wp_mail_from_name', 'WordPress');
  add_option('mandrill_mail_default_from_name', $from_name, '', 'yes');
}
register_activation_hook(__FILE__, 'mandrill_mail_activate');

/**
 * Sends an email via the Mandrill API
 *
 * @param array $atts
 * @return bool Whether the email was sent successfully.
 */
function mandrill_mail($atts)
{
  $mail = new Mandrill_Mail($atts);
  return $mail->send();
}
