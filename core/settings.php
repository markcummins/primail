<?php

namespace Mandrill_Mail_Settings;

add_action('init', function () {
  if (is_admin()) {
    add_action('admin_notices', __NAMESPACE__ . '\admin_notice_debug_mode');
    add_action('admin_menu', __NAMESPACE__ . '\add_settings_page');
    add_action('admin_init', __NAMESPACE__ . '\register_settings');
  }
});

/**
 * Displays an admin message if `Debug Mode` has been enabled
 *
 * @return void
 */
function admin_notice_debug_mode()
{
  $debug_mode = get_option('mandrill_mail_api_debug_enabled');
  if ($debug_mode !== '1') {
    return;
  }

  $settings_page_link = sprintf(
    '<a href="%s">%s</a>',
    get_settings_page_link(),
    __('Debug Mode', 'mandrill_mail'),
  );

  $message = sprintf(
    __('Notice! Emails are not sending, as `%s` is enabled ', 'mandrill_mail'),
    $settings_page_link
  );

  echo "<div class='notice notice-warning is-dismissible'>
            <p>{$message}</p>
          </div>";
}

/**
 * Returns the link of the Mandrill Mail Settings Page
 *
 * @return string
 */
function get_settings_page_link()
{
  return esc_url(add_query_arg(
    'page',
    get_slug(),
    get_admin_url() . 'admin.php'
  ));
}

/**
 * Adds the Options Page to the Admin Area
 *
 * @return void
 */
function add_settings_page()
{
  add_options_page(
    'Mandrill Mail Settings',
    'Mandrill Mail',
    'manage_options',
    get_slug(),
    __NAMESPACE__ . '\settings',
    10
  );
}

/**
 * Registers the Settings Group, Section and Fields
 *
 * @return void
 */
function register_settings()
{
  add_settings_section(
    'mandrill_mail_settings_api',
    '',
    __NAMESPACE__ . '\section_settings_api',
    'mandrill_mail_mandrill_mail_settings'
  );

  register_setting(
    get_option_group(),
    'mandrill_mail_default_from_email',
    array(
      'type' => 'string',
      'description' => 'The Default From EMail Field',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
    ),
  );

  add_settings_field(
    'mandrill_mail_default_from_email',
    __('From Email Address', 'mandrill_mail'),
    __NAMESPACE__ . '\render_field_from_email',
    'mandrill_mail_mandrill_mail_settings',
    'mandrill_mail_settings_api'
  );

  register_setting(
    get_option_group(),
    'mandrill_mail_default_from_name',
    array(
      'type' => 'string',
      'description' => 'The Default From Name Field',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
    ),
  );

  add_settings_field(
    'mandrill_mail_default_from_name',
    __('From Name', 'mandrill_mail'),
    __NAMESPACE__ . '\render_field_from_name',
    'mandrill_mail_mandrill_mail_settings',
    'mandrill_mail_settings_api'
  );

  register_setting(
    get_option_group(),
    'mandrill_mail_api_key',
    array(
      'type' => 'string',
      'description' => 'The Mandrill API Key',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
      'default' => '',
    ),
  );

  add_settings_field(
    'mandrill_mail_field_api_key',
    __('API Key', 'mandrill_mail'),
    __NAMESPACE__ .  '\render_field_api_key',
    'mandrill_mail_mandrill_mail_settings',
    'mandrill_mail_settings_api'
  );

  register_setting(
    get_option_group(),
    'mandrill_mail_api_test_key',
    array(
      'type' => 'string',
      'description' => 'The Mandrill API Test Key',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
      'default' => '',
    ),
  );

  add_settings_field(
    'mandrill_mail_field_api_test_key',
    __('API Test Key', 'mandrill_mail'),
    __NAMESPACE__ . '\render_field_api_test_key',
    'mandrill_mail_mandrill_mail_settings',
    'mandrill_mail_settings_api'
  );

  register_setting(
    get_option_group(),
    'mandrill_mail_api_debug_enabled',
    array(
      'type' => 'boolean',
      'description' => 'The Mandrill Mode',
      'show_in_rest' => false,
      'default' => false,
    ),
  );

  add_settings_field(
    'mandrill_mail_api_debug_enabled',
    __('Debug Mode', 'mandrill_mail'),
    __NAMESPACE__ .  '\render_field_api_mode',
    'mandrill_mail_mandrill_mail_settings',
    'mandrill_mail_settings_api'
  );
}

/**
 * Renders the Default From EMail Settings Field
 *
 * @return void
 */
function render_field_from_email()
{
  $value = get_option('mandrill_mail_default_from_email');

  printf(
    '<input type="text" name="%s" value="%s" />',
    'mandrill_mail_default_from_email',
    $value
  );

  $description = __('This email address will be used in the `From` field.', 'mandrill_mail');
  echo "<p class='description'>{$description}</p>";
}

/**
 * Renders the Default From Name Settings Field
 *
 * @return void
 */
function render_field_from_name()
{
  $value = get_option('mandrill_mail_default_from_name');

  printf(
    '<input type="text" name="%s" value="%s" />',
    'mandrill_mail_default_from_name',
    $value
  );

  $description = __('This text will be used in the `FROM` field', 'mandrill_mail');
  echo "<p class='description'>{$description}</p>";
}

/**
 * Renders the API Key Settings Field
 *
 * @return void
 */
function render_field_api_key()
{
  $api_key = get_option('mandrill_mail_api_key');

  printf(
    '<input type="text" name="%s" value="%s" />',
    'mandrill_mail_api_key',
    $api_key
  );

  $mandrill_guide = 'https://mailchimp.com/developer/transactional/guides/quick-start/';
  $mandrill_guide_link = sprintf("<a href='{$mandrill_guide}'>%s</a>",  __('quick start guide', 'mandrill_mail'));

  $description = sprintf(
    __('The Mandrill API Key (See the %s for information on configuring an API Key)', 'mandrill_mail'),
    $mandrill_guide_link
  );

  echo "<p class='description'>{$description}</p>";
}

/**
 * Renders the API Test Key Settings Field
 *
 * @return void
 */
function render_field_api_test_key()
{
  $api_key = get_option('mandrill_mail_api_test_key');

  printf(
    '<input type="text" name="%s" value="%s" />',
    'mandrill_mail_api_test_key',
    $api_key
  );

  $description = __('The Mandrill Test API Key, which will be used when debug mode is enabled', 'mandrill_mail');
  echo "<p class='description'>{$description}</p>";
}

/**
 * Renders the API Mode Settings Field
 *
 * @return void
 */
function render_field_api_mode()
{
  $debug_mode_enabled = get_option('mandrill_mail_api_debug_enabled');
  $checked = checked('1', $debug_mode_enabled, false);

  $env_message = "";
  $is_production = wp_get_environment_type() !== 'production';

  if ($is_production) {
    $checked = checked(true, true, false);
    $wp_env_guide = 'https://developer.wordpress.org/reference/functions/wp_get_environment_type/';
    $wp_env_guide_link = sprintf("<a href='{$wp_env_guide}'>%s</a>",  __('Learn more', 'mandrill_mail'));

    // Debug Mode
    $message = sprintf(
      __('Debug Mode has been automatically enabled, as this site is not in `Production Mode` (%s)', 'mandrill_mail'),
      $wp_env_guide_link
    );

    $env_message = "<p class='description'>{$message}</p>";
  }

  $field = sprintf(
    '<input type="checkbox" name="%s" value="%s" %s %s/>',
    'mandrill_mail_api_debug_enabled',
    '1',
    $checked,
    $is_production ? "disabled" : ""
  );

  $description = __('Debug Mode Enabled', 'mandrill_mail');
  echo "<fieldset><label>{$field}&nbsp;{$description}</label></fieldset>{$env_message}";
}

/**
 * Renders the Settings API Section Markup Page
 *
 * @return void
 */
function settings()
{
  echo "<h2>Mandrill Mail Settings</h2>";
  echo "<form action='options.php' method='post'>";

  // Prints the form nonce, action and option_page 
  settings_fields(get_option_group());

  // Prints the Heading and Settings Page Table
  do_settings_sections('mandrill_mail_mandrill_mail_settings');

  echo submit_button(__('Save Settings', 'mandrill_mail'));
  echo "</form>";
}

/**
 * Returns the Options Page Slug
 *
 * @return string
 */
function get_slug()
{
  return 'mandrill-mail-settings';
}

/**
 * Returns the Options Page Group
 *
 * @return string
 */
function get_option_group()
{
  return 'mandrill_mail_settings';
}

/**
 * Renders the Settings API Section Markup Section
 *
 * @return void
 */
function section_settings_api()
{
  echo  '';
}
