<?php

namespace Mandrill_Mail_Settings;

add_action('init', function () {
  if (is_admin()) {
    add_action('admin_menu', __NAMESPACE__ . '\add_settings_page');
    add_action('admin_init', __NAMESPACE__ . '\register_settings');
    add_action('admin_notices', __NAMESPACE__ . '\admin_notice_debug_mode');
    add_action('admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts');
  }
});

/**
 * Enqueues the Admin Scripts
 *
 * @param string $hook
 * @return void
 */
function enqueue_scripts($hook)
{
  $admin_slug = get_slug();
  if ($hook === "settings_page_{$admin_slug}") {
    $version = get_mandrill_mail_plugin_version();
    $base_url = get_mandrill_mail_template_url();

    wp_enqueue_style('mandrill-mail-admin-style', "{$base_url}scripts/css/style.css", array(), $version);
  }
}

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

  echo admin_notice("<p>{$message}</p>", 'warning');
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
    '<input type="email" name="%s" value="%s" required/>',
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
    '<input type="text" name="%s" value="%s" required/>',
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
    '<input type="text" name="%s" value="%s"/>',
    'mandrill_mail_api_key',
    $api_key
  );

  $description = __('The Mandrill API Key', 'mandrill_mail');

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
    '<input type="text" name="%s" value="%s"/>',
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
  $slug = get_slug();

  $tab = isset($_GET['tab'])
    ? $_GET['tab']
    : null;

  $sent_email = array();
  if (isset($_POST['mandrill_mail_form_submit']) && check_admin_referer('mandrill_mail_form_nonce')) {
    $response = send_test_email();
    if (is_wp_error($response)) {
      echo admin_notice("<p>{$response->get_error_message()}</p>", 'error');
    } else {
      $sent_email = array(
        'request' => $response->get_email(),
        'response' => $response->get_response(),
      );

      echo ($sent_email['response']['status'] === 200)
        ? admin_notice("<p>" . __('Message Sent.', 'mandrill_mail') . "</p>", 'success')
        : admin_notice("<p>" . __('Message Failed to send. See the log details below for more information.', 'mandrill_mail') . "</p>", 'error');
    }
  }

  echo '<div class="wrap">';
  echo "<h2>Mandrill Mail Settings</h2>";

  echo "<nav class='nav-tab-wrapper'>
          <a href='?page={$slug}' class='nav-tab" . (($tab === null) ? ' nav-tab-active' : '') . "'>" . __('Settings', 'mandrill_mail') . "</a>
          <a href='?page={$slug}&tab=test' class='nav-tab" . (($tab === 'test') ? ' nav-tab-active' : '') . "'>" . __('Debug', 'mandrill_mail') . "</a>
        </nav>
        <br/>";

  echo "<div class='mandrill-mail-admin-settings-tab'>";
  echo "<div class='mandrill-mail-admin-settings-tab-content'>";
  echo "<div class='mandrill-mail-admin-card'>";

  if ($tab === null) {
    echo get_tab_settings();
  }

  if ($tab === 'test') {
    echo get_tab_email_test();
  }

  echo "</div>";
  echo "</div>";
  echo "<div>";
  echo "<div class='mandrill-mail-admin-settings-tab-sidebar'>";
  echo admin_sidebar();
  echo "</div>";
  echo "</div>";
  echo "</div>";

  if (!empty($sent_email)) {

    $api_card_class = $sent_email['response']['status'] === 200
      ? 'success'
      : 'error';

    echo "<div class='mandrill-mail-admin-card mandrill-mail-admin-card-{$api_card_class}'>";
    echo '<h3>API Request</h3>';
    echo "<pre>";
    echo json_encode($sent_email['request'], JSON_PRETTY_PRINT);
    echo '</pre>';
    echo '</div>';

    echo "<div class='mandrill-mail-admin-card mandrill-mail-admin-card-{$api_card_class}'>";
    echo '<h3>API Response</h3>';
    echo "<pre>";
    echo json_encode($sent_email['response'], JSON_PRETTY_PRINT);
    echo '</pre>';
    echo '</div>';
  }
}

/**
 * Returns the content for the Settings Tab
 *
 * @return string
 */
function get_tab_settings()
{
  ob_start();

  $mandrill_guide = 'https://mailchimp.com/developer/transactional/guides/quick-start/';
  $mandrill_guide_link = sprintf("<a href='{$mandrill_guide}'>%s</a>",  __('quick start guide', 'mandrill_mail'));

  $msg = array(
    __('Use the API details provided by Mandrill to configure the following settings', 'mandrill_mail'),
    sprintf(__('See the %s for generating an API Key and for more general information and options.', 'mandrill_mail'), $mandrill_guide_link),
  );

  echo "<div style='width: 100%; max-width: 640px;'>";
  echo "<h3>" . __('Configuration Settings', 'mandrill_mail') . "</h3>";
  echo "<p>" . implode(' ', $msg) . "</p>";
  echo "</div>";

  echo "<form action='options.php' method='post'>";

  // Prints the form nonce, action and option_page 
  settings_fields(get_option_group());

  // Prints the Heading and Settings Page Table
  do_settings_sections('mandrill_mail_mandrill_mail_settings');

  echo submit_button(__('Save Settings', 'mandrill_mail'));
  echo "</form>";

  $tab_html = ob_get_contents();
  ob_end_clean();

  return $tab_html;
}

/**
 * Renders the Sidebar in the Admin Page
 *
 * @return string
 */
function admin_sidebar()
{
  $plugin_name = get_mandrill_mail_plugin_name();

  $links = array(
    'docs' => sprintf("<a target='_blank' href='https://wordpress.org/support/plugin/mandrill-mail/'>%s</a>", __('Mandrill Mail', 'mandrill_mail')),
    'rate' => sprintf("<a href='https://wordpress.org/support/plugin/{$plugin_name}/reviews/#new-post' target='_blank'>%s</a>", __('rating', 'mandrill_mail')),
    'forum' => sprintf("<a href='https://wordpress.org/support/plugin/{$plugin_name}/' target='_blank'>%s</a>", __('Support Forum', 'mandrill_mail')),
    'coffee' => sprintf("<a target='_blank' href='https://paypal.me/markcummins87?country.x=IE&locale.x=en_US'>%s</a>", __('buy me a coffee', 'mandrill_mail')),
  );

  return "<div>
            <div class='mandrill-mail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-book' style='margin-right: 8px;'></span> " . __('Docs', 'mandrill_mail') . "
              </h3>
              <div>
                <p>" . sprintf(__('Please visit the %s plugins documentation page to learn how to use this plugin.', 'mandrill_mail'), $links['docs']) . "</p>
              </div>
            </div>
            <div class='mandrill-mail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-sos' style='margin-right: 8px;'></span> " . __('Support', 'mandrill_mail') . "
              </h3>
              <div>
                <p>" . sprintf(__('Having issues or difficulties? You can post your issue on the %s, or drop your feature requests there if you have them!'), $links['forum']) . "</p>
              </div>
            </div>
            <div class='mandrill-mail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-megaphone' style='margin-right: 8px;'></span> " . __('Feedback', 'mandrill_mail') . "
              </h3>
              <div>
                " . sprintf(__('Like the plugin? Please give us a %s', 'mandrill_mail'), $links['rate']) . " (" . __('5 Stars would be nice', 'mandrill_mail') . " 😂) 
                <div>
                 <p>
                  <a href='https://wordpress.org/support/plugin/mandrill-mail/reviews/?filter=5' style='font-size: 0;' target='_blank'>
                    <span class='dashicons dashicons-star-filled'></span>
                    <span class='dashicons dashicons-star-filled'></span>
                    <span class='dashicons dashicons-star-filled'></span>
                    <span class='dashicons dashicons-star-filled'></span>
                    <span class='dashicons dashicons-star-filled'></span>
                  </a>
                 </p>
                </div>
              </div>
            </div>
            <div class='mandrill-mail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-coffee' style='margin-right: 8px;'></span> " . __('Buy Me a Coffee', 'mandrill_mail') . "
              </h3>
              <div>
                <p>" . __('Found this plugin useful? As much fun as it was creating it, it did take a rediculous amount of time', 'mandrill_mail') . "🙈.</p>
                <p>" . sprintf(__('If you would like to support my work, you can %s.', 'mandrill_mail'), $links['coffee']) . " " . __('Thank You!', 'mandrill_mail') . "</p>
              </div>
            </div>
          </div>";
}

/**
 * Returns the content for the EMail Test Tab
 *
 * @return string
 */
function get_tab_email_test()
{
  $to = isset($_POST, $_POST['to'])
    ? sanitize_text_field($_POST['to'])
    : '';

  $subject = isset($_POST, $_POST['subject'])
    ? sanitize_text_field($_POST['subject'])
    : '';

  $message = isset($_POST, $_POST['message'])
    ? sanitize_textarea_field($_POST['message'])
    : '';

  ob_start();

  $msg = array(
    __('You can use this section to send a very basic test email.', 'mandrill_mail'),
    __('You will be able to see the details of the request and the response from Mandrill.', 'mandrill_mail'),
    __('You can use this section to debug and test that your emails are being sent.', 'mandrill_mail'),
  );

  echo "<div style='width: 100%; max-width: 640px;'>";
  echo "<h3>" . __('Test Email', 'mandrill_mail') . "</h3>";
  echo "<p>" . implode(' ', $msg) . "</p>";
  echo "</div>";

  echo "<form  method='post' action=''>";
  echo "<table class='form-table'>
          <tbody>
          <tr valign='top'>
            <th scope='row'>To:</th>
            <td>
              <input type='email' required name='to' style='width: 100%; max-width: 400px;' value='{$to}'><br>
              <p class='description'>" . __("Enter the recipient's email address", 'mandrill_mail') . "</p>
            </td>
          </tr>
          <tr valign='top'>
            <th scope='row'>Subject:</th>
            <td>
              <input type='text' name='subject' required style='width: 100%; max-width: 400px;' value='{$subject}'><br>
              <p class='description'>" . __('Enter a subject for your message', 'mandrill_mail') . "</p>
            </td>
          </tr>
          <tr valign='top'>
            <th scope='row'>Message:</th>
            <td>
              <textarea name='message' required style='width: 100%; max-width: 400px;' rows='5'>{$message}</textarea>
              <p class='description'>" . __('Write your email message', 'mandrill_mail') . "</p>
            </td>
          </tr>
        </tbody>
        </table>";

  wp_nonce_field('mandrill_mail_form_nonce');

  echo "<input type='hidden' name='mandrill_mail_form_submit' value='submit' />";
  echo submit_button(__('Send Test', 'mandrill_mail'));
  echo "</form>";

  $tab_html = ob_get_contents();
  ob_end_clean();

  return $tab_html;
}

/**
 * Sends a Test EMail
 *
 * @return Mandrill_Mail/WP_Error
 */
function send_test_email()
{
  $to = sanitize_text_field($_POST['to']);
  if (!is_email($to)) {
    $error = new \WP_Error();
    $error->add('empty', __('A Valid EMail Address is required', 'mandrill_mail'));
    return $error;
  }

  $subject = sanitize_text_field($_POST['subject']);
  if (empty($subject)) {
    $error = new \WP_Error();
    $error->add('empty', __('Subject is a required field', 'mandrill_mail'));
    return $error;
  }

  $message = sanitize_textarea_field($_POST['message']);
  if (empty($message)) {
    $error = new \WP_Error();
    $error->add('empty', __('Message is a required field', 'mandrill_mail'));
    return $error;
  }

  $mail = new \Mandrill_Mail(array(
    'to' => $to,
    'subject' => $subject,
    'message' => $message,
    'headers' => array(),
  ));

  $mail->send();
  return $mail;
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
 * Returns the html for an admin notice
 *
 * @param string $content
 * @param string $type
 * @return string
 */
function admin_notice($content, $type)
{
  return "<div class='notice notice-{$type} is-dismissible'>{$content}</div>";
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
