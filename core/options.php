<?php

namespace Mandrill_Options;

add_filter('option_mandrill_mail_default_from_email', __NAMESPACE__ . '\filter_default_from_email');
add_filter('option_mandrill_mail_default_from_name', __NAMESPACE__ . '\filter_default_from_name');
add_filter('option_mandrill_mail_api_debug_enabled', __NAMESPACE__ . '\filter_api_debug_enabled');

/**
 * Filters the default value of the Mandrill Debug option
 *
 * @param string $val
 * @return string
 */
function filter_default_from_email($from_email)
{
  $from_email = apply_filters('wp_mail_from', $from_email);

  if (empty($from_email)) {
    $sitename   = wp_parse_url(network_home_url(), PHP_URL_HOST);
    $from_email = 'wordpress@';

    if (null !== $sitename) {
      if ('www.' === substr($sitename, 0, 4)) {
        $from_email = substr($sitename, 4);
      }

      $from_email .= $sitename;
    }
  }

  return $from_email;
}

/**
 * Filters the default value of the Mandrill Debug option
 *
 * @param bool $val
 * @return bool
 */
function filter_default_from_name($from_name)
{
  $from_name = apply_filters('wp_mail_from_name', $from_name);
  if (empty($from_name)) {
    $from_name = 'WordPress';
  }

  return $from_name;
}

/**
 * Filters the default value of the Mandrill Debug option
 *
 * @param bool $val
 * @return bool
 */
function filter_api_debug_enabled($val)
{
  if (wp_get_environment_type() !== 'production') {
    return true;
  }

  return $val;
}
