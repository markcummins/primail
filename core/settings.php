<?php

namespace Primail_Settings;

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
    $version = get_primail_plugin_version();
    $base_url = get_primail_template_url();

    wp_enqueue_style('primail-admin-style', "{$base_url}scripts/css/style.css", array(), $version);
  }
}

/**
 * Displays an admin message if `Debug Mode` has been enabled
 *
 * @return void
 */
function admin_notice_debug_mode()
{
  $debug_mode = get_option('primail_api_debug_enabled');
  if ($debug_mode !== '1') {
    return;
  }

  $message = sprintf(
    __('Notice! Emails are not sending, as `<a href="%s">%s</a>` is enabled ',  "primail"),
    esc_attr(get_settings_page_link()),
    esc_html__('Debug Mode',  "primail"),
  );

  echo admin_notice($message, 'warning');
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
    'Primail Settings',
    'Primail',
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
    'primail_settings_api',
    '',
    __NAMESPACE__ . '\section_settings_api',
    'primail_primail_settings'
  );

  register_setting(
    get_option_group(),
    'primail_default_from_email',
    array(
      'type' => 'string',
      'description' => 'The Default From EMail Field',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
    ),
  );

  add_settings_field(
    'primail_default_from_email',
    __('From Email Address',  "primail"),
    __NAMESPACE__ . '\render_field_from_email',
    'primail_primail_settings',
    'primail_settings_api'
  );

  register_setting(
    get_option_group(),
    'primail_default_from_name',
    array(
      'type' => 'string',
      'description' => 'The Default From Name Field',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
    ),
  );

  add_settings_field(
    'primail_default_from_name',
    __('From Name',  "primail"),
    __NAMESPACE__ . '\render_field_from_name',
    'primail_primail_settings',
    'primail_settings_api'
  );

  register_setting(
    get_option_group(),
    'primail_api_key',
    array(
      'type' => 'string',
      'description' => 'The Mandrill API Key',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
      'default' => '',
    ),
  );

  add_settings_field(
    'primail_field_api_key',
    __('API Key',  "primail"),
    __NAMESPACE__ .  '\render_field_api_key',
    'primail_primail_settings',
    'primail_settings_api'
  );

  register_setting(
    get_option_group(),
    'primail_api_test_key',
    array(
      'type' => 'string',
      'description' => 'The Mandrill API Test Key',
      'sanitize_callback' => 'sanitize_text_field',
      'show_in_rest' => false,
      'default' => '',
    ),
  );

  add_settings_field(
    'primail_field_api_test_key',
    __('API Test Key',  "primail"),
    __NAMESPACE__ . '\render_field_api_test_key',
    'primail_primail_settings',
    'primail_settings_api'
  );

  register_setting(
    get_option_group(),
    'primail_api_debug_enabled',
    array(
      'type' => 'boolean',
      'description' => 'The Mandrill Mode',
      'show_in_rest' => false,
      'default' => false,
    ),
  );

  add_settings_field(
    'primail_api_debug_enabled',
    __('Debug Mode',  "primail"),
    __NAMESPACE__ .  '\render_field_api_mode',
    'primail_primail_settings',
    'primail_settings_api'
  );
}

/**
 * Renders the Default From EMail Settings Field
 *
 * @return void
 */
function render_field_from_email()
{
  $value = get_option('primail_default_from_email');

  printf(
    '<input type="email" name="%s" value="%s" required/>',
    'primail_default_from_email',
    esc_attr($value)
  );

  echo sprintf("<p class='description'>%s</p>", esc_html__('This email address will be used in the `From` field.',  "primail"));
}

/**
 * Renders the Default From Name Settings Field
 *
 * @return void
 */
function render_field_from_name()
{
  $value = get_option('primail_default_from_name');

  printf(
    '<input type="text" name="%s" value="%s" required/>',
    'primail_default_from_name',
    esc_attr($value)
  );

  echo sprintf("<p class='description'>%s</p>", esc_html__('This text will be used in the `FROM` field',  "primail"));
}

/**
 * Renders the API Key Settings Field
 *
 * @return void
 */
function render_field_api_key()
{
  $api_key = get_option('primail_api_key');

  printf(
    '<input type="text" name="%s" value="%s"/>',
    'primail_api_key',
    esc_attr($api_key)
  );

  echo sprintf("<p class='description'>%s</p>", esc_html__('The Mandrill API Key',  "primail"));
}

/**
 * Renders the API Test Key Settings Field
 *
 * @return void
 */
function render_field_api_test_key()
{
  $api_key = get_option('primail_api_test_key');

  printf(
    '<input type="text" name="%s" value="%s"/>',
    'primail_api_test_key',
    esc_attr($api_key)
  );

  echo sprintf("<p class='description'>%s</p>", esc_html__('The Mandrill Test API Key, which will be used when debug mode is enabled',  "primail"));
}

/**
 * Renders the API Mode Settings Field
 *
 * @return void
 */
function render_field_api_mode()
{
  $debug_mode_enabled = get_option('primail_api_debug_enabled');
  $checked = checked('1', $debug_mode_enabled, false);

  $env_message = "";
  $is_production = wp_get_environment_type() !== 'production';

  if ($is_production) {
    $checked = checked(true, true, false);
    $wp_env_guide = 'https://developer.wordpress.org/reference/functions/wp_get_environment_type/';
    $wp_env_guide_link = sprintf("<a href='%s'>%s</a>", esc_attr($wp_env_guide), esc_html__('Learn more',  "primail"));

    // Debug Mode
    $message = sprintf(
      esc_html__('Debug Mode has been automatically enabled, as this site is not in `Production Mode` (%s)',  "primail"),
      esc_attr($wp_env_guide_link)
    );

    $env_message = "<p class='description'>{$message}</p>";
  }

  $field = sprintf(
    '<input type="checkbox" name="%s" value="%s" %s %s/>',
    'primail_api_debug_enabled',
    '1',
    $checked,
    $is_production ? "disabled" : ""
  );

  $description = __('Debug Mode Enabled',  "primail");
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

  $tab = !empty($_GET['tab'])
    ? sanitize_key($_GET['tab'])
    : null;

  $sent_email = array();
  if (isset($_POST['primail_form_submit']) && check_admin_referer('primail_form_nonce')) {
    $response = send_test_email();
    if (is_wp_error($response)) {
      echo admin_notice($response->get_error_message(), 'error');
    } else {
      $sent_email = array(
        'request' => $response->get_email(),
        'response' => $response->get_response(),
      );

      echo ($sent_email['response']['status'] === 200)
        ? admin_notice(esc_html__('Message Sent.',  "primail"), 'success')
        : admin_notice(esc_html__('Message Failed to send. See the log details below for more information.',  "primail"), 'error');
    }
  }

  echo '<div class="wrap">';
  echo "<h2>Mandrill Mail Settings</h2>";

  echo "<nav class='nav-tab-wrapper'>
          <a href='?page={$slug}' class='nav-tab" . (($tab === null) ? ' nav-tab-active' : '') . "'>" . __('Settings',  "primail") . "</a>
          <a href='?page={$slug}&tab=test' class='nav-tab" . (($tab === 'test') ? ' nav-tab-active' : '') . "'>" . __('Debug',  "primail") . "</a>
        </nav>
        <br/>";

  echo "<div class='primail-admin-settings-tab'>";
  echo "<div class='primail-admin-settings-tab-content'>";
  echo "<div class='primail-admin-card'>";

  if ($tab === null) {
    echo get_tab_settings();
  }

  if ($tab === 'test') {
    echo get_tab_email_test();
  }

  echo "</div>";
  echo "</div>";
  echo "<div>";
  echo "<div class='primail-admin-settings-tab-sidebar'>";
  echo admin_sidebar();
  echo "</div>";
  echo "</div>";
  echo "</div>";

  if (!empty($sent_email)) {

    $api_card_class = $sent_email['response']['status'] === 200
      ? 'success'
      : 'error';

    echo "<div class='primail-admin-card primail-admin-card-{$api_card_class}'>";
    echo '<h3>API Request</h3>';
    echo "<pre>";
    echo json_encode($sent_email['request'], JSON_PRETTY_PRINT);
    echo '</pre>';
    echo '</div>';

    echo "<div class='primail-admin-card primail-admin-card-{$api_card_class}'>";
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

  $mailchimp_guide = 'https://mailchimp.com/developer/transactional/guides/quick-start/';

  $msg = array(
    __('Use the API details provided by Mandrill to configure the following settings',  "primail"),
    sprintf(__("See the <a href='%s'>%s</a> for generating an API Key and for more general information and options.",  "primail"), esc_attr($mailchimp_guide), esc_html__('quick start guide',  "primail")),
  );

  echo "<div style='width: 100%; max-width: 640px;'>";
  echo "<h3>" . __('Configuration Settings',  "primail") . "</h3>";
  echo "<p>" . implode(' ', $msg) . "</p>";
  echo "</div>";

  echo "<form action='options.php' method='post'>";

  // Prints the form nonce, action and option_page 
  settings_fields(get_option_group());

  // Prints the Heading and Settings Page Table
  do_settings_sections('primail_primail_settings');

  echo submit_button(__('Save Settings',  "primail"));
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
  $plugin_name = get_primail_plugin_name();

  $plugin_url = "https://wordpress.org/plugins/{$plugin_name}/";
  $support_url = "https://wordpress.org/support/plugin/{$plugin_name}/";
  $review_post_url = "https://wordpress.org/support/plugin/{$plugin_name}/reviews/#new-post";
  $review_rate_url = "https://wordpress.org/support/plugin/{$plugin_name}/reviews/?filter=5";
  $donate_url = "https://paypal.me/markcummins87?country.x=IE&locale.x=en_US";

  return "<div>
            <div class='primail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-book' style='margin-right: 8px;'></span> " . __('Docs',  "primail") . "
              </h3>
              <div>
                <p>" . sprintf(__('Please visit the <a target="_blank" href="%s">%s</a> plugins documentation page to learn how to use this plugin.',  "primail"), esc_attr($plugin_url), __('Primail',  "primail")) . "</p>
              </div>
            </div>
            <div class='primail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-sos' style='margin-right: 8px;'></span> " . __('Support',  "primail") . "
              </h3>
              <div>
                <p>" . sprintf(__('Having issues or difficulties? You can post your issue on the <a href="%s" target="_blank">%s</a>, or drop your feature requests there if you have them!'), esc_attr($support_url), __('Support Forum',  "primail")) . "</p>
              </div>
            </div>
            <div class='primail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-megaphone' style='margin-right: 8px;'></span> " . __('Feedback',  "primail") . "
              </h3>
              <div>
                " . sprintf(__('Like the plugin? Please give us a <a href="%s" target="_blank">%s</a>', 'primail'), esc_attr($review_post_url), __('rating',  "primail")) . " (" . __('5 Stars would be nice',  "primail") . " 😂) 
                <div>
                 <p>
                  <a href='" . esc_attr($review_rate_url) . "' style='font-size: 0;' target='_blank'>
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
            <div class='primail-admin-card' style='min-width: inherit;'>
              <h3 style='display: flex; align-items: center;'>
                <span class='dashicons dashicons-coffee' style='margin-right: 8px;'></span> " . __('Buy Me a Coffee',  "primail") . "
              </h3>
              <div>
                <p>" . __('Found this plugin useful? As much fun as it was creating it, it did take a rediculous amount of time',  "primail") . "🙈.</p>
                <p>" . sprintf(__('If you would like to support my work, you can <a target="_blank" href="' . esc_attr($donate_url) . '">%s</a>.', 'primail'), __('buy me a coffee',  "primail")) . " " . __('Thank You!',  "primail") . "</p>
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
    __('You can use this section to send a very basic test email.',  "primail"),
    __('You will be able to see the details of the request and the response from Mandrill.',  "primail"),
    __('You can use this section to debug and test that your emails are being sent.',  "primail"),
  );

  echo "<div style='width: 100%; max-width: 640px;'>";
  echo "<h3>" . __('Test Email',  "primail") . "</h3>";
  echo "<p>" . implode(' ', $msg) . "</p>";
  echo "</div>";

  echo "<form  method='post' action=''>";
  echo "<table class='form-table'>
          <tbody>
          <tr valign='top'>
            <th scope='row'>To:</th>
            <td>
              <input type='email' required name='to' style='width: 100%; max-width: 400px;' value='{$to}'><br>
              <p class='description'>" . __("Enter the recipient's email address",  "primail") . "</p>
            </td>
          </tr>
          <tr valign='top'>
            <th scope='row'>Subject:</th>
            <td>
              <input type='text' name='subject' required style='width: 100%; max-width: 400px;' value='{$subject}'><br>
              <p class='description'>" . __('Enter a subject for your message',  "primail") . "</p>
            </td>
          </tr>
          <tr valign='top'>
            <th scope='row'>Message:</th>
            <td>
              <textarea name='message' required style='width: 100%; max-width: 400px;' rows='5'>{$message}</textarea>
              <p class='description'>" . __('Write your email message',  "primail") . "</p>
            </td>
          </tr>
        </tbody>
        </table>";

  wp_nonce_field('primail_form_nonce');

  echo "<input type='hidden' name='primail_form_submit' value='submit' />";
  echo submit_button(__('Send Test',  "primail"));
  echo "</form>";

  $tab_html = ob_get_contents();
  ob_end_clean();

  return $tab_html;
}

/**
 * Sends a Test EMail
 *
 * @return Primail/WP_Error
 */
function send_test_email()
{
  $to = sanitize_text_field($_POST['to']);
  if (!is_email($to)) {
    $error = new \WP_Error();
    $error->add('empty', __('A Valid EMail Address is required',  "primail"));
    return $error;
  }

  $subject = sanitize_text_field($_POST['subject']);
  if (empty($subject)) {
    $error = new \WP_Error();
    $error->add('empty', __('Subject is a required field',  "primail"));
    return $error;
  }

  $message = sanitize_textarea_field($_POST['message']);
  if (empty($message)) {
    $error = new \WP_Error();
    $error->add('empty', __('Message is a required field',  "primail"));
    return $error;
  }

  $mail = new \Primail(array(
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
  return 'primail-settings';
}

/**
 * Returns the Options Page Group
 *
 * @return string
 */
function get_option_group()
{
  return 'primail_settings';
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
  return sprintf(
    "<div class='notice notice-%s is-dismissible'><p>%s</p></div>",
    esc_attr($type),
    esc_html($content)
  );
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
