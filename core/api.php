<?php

/**
 * Converts WP Mail data to Mandrill Mail Data and 
 * sends the EMail via the Mandrill_Request\send() Method
 * 
 * @since 1.0.0
 */
class Primail
{
  private $key;
  private $atts;
  private $content_type;

  private $to;
  private $headers;
  private $subject;
  private $message;
  private $attachments;

  private $email = null;
  private $response = null;

  public function __construct($atts)
  {
    $this->atts = $atts;

    $debug_mode = get_option('primail_api_debug_enabled');
    $this->key = ($debug_mode === '1')
      ? get_option('primail_api_test_key')
      : get_option('primail_api_key');

    $headers = (is_array($atts['headers']))
      ? $atts['headers']
      : explode("\n", str_replace("\r\n", "\n", $atts['headers']));

    $this->headers = array();
    foreach ($headers as $header) {
      if (false === strpos($header, ':')) {
        continue;
      }

      list($name, $content) = explode(':', trim($header), 2);
      $this->headers[] = array(
        'name' => trim($name),
        'content' => trim($content)
      );
    }

    $this->content_type = 'html';
    foreach ($this->headers as $header) {
      if (strtolower($header['name']) === 'content-type') {
        $header_content = apply_filters('wp_mail_content_type', $header['content']);
        if (str_contains(strtolower(trim($header_content)), 'text/plain')) {
          $this->content_type = 'text';
        }
      }
    }

    $this->to = $atts['to'];
    $this->subject = $atts['subject'];
    $this->message = $atts['message'];

    $this->attachments = array();
    if (isset($atts['attachments'])) {
      $this->attachments = is_array($atts['attachments'])
        ? $atts['attachments']
        : explode("\n", str_replace("\r\n", "\n", $atts['attachments']));
    }
  }

  /**
   * Sends the EMail to the Mandrill API
   *
   * @return bool Whether the email was sent successfully.
   */
  public function send()
  {
    $this->email = array(
      "key" => $this->key,
      "message" => array(
        "headers" => $this->get_headers(),
        "subject" => $this->subject,
        "from_email" => $this->get_from_email(),
        "from_name" => $this->get_from_name(),
        "to" => $this->get_recipients(),
        "important" => $this->is_important(),
      ),
      "async" => true
    );

    $attachments = $this->get_attachments();
    if (!empty($attachments)) {
      $this->email['message']['attachments'] = $this->get_attachments();
    }

    $this->email['message'][$this->content_type] = $this->message;
    if ($this->content_type === 'html') {
      $this->email['message']['auto_text'] = true;
    }

    $this->response = Primail_Request\send($this->email);

    if ($this->response['status'] !== 200) {
      do_action('wp_mail_failed', new WP_Error('wp_mail_failed', "failed with http status code {$this->response['status']}", $this->atts));
      return false;
    }

    if (!is_array($this->response) || empty($response['response'])) {
      do_action('wp_mail_failed', new WP_Error('wp_mail_failed', "failed with no response from the API", $this->atts));
      return false;
    }

    do_action('wp_mail_succeeded', $this->atts);
    return true;
  }

  /**
   * Returns the EMail Object
   *
   * @return object|null
   */
  public function get_email()
  {
    return $this->email;
  }

  /**
   * Returns the API Response
   *
   * @return object|null
   */
  public function get_response()
  {
    return $this->response;
  }

  /**
   * Formats the Attachments as an Array
   *
   * @return array
   */
  private function get_attachments()
  {
    $attachments = array();

    foreach ($this->attachments as $attachment) {
      $path_info = pathinfo($attachment);
      $filetype = wp_check_filetype($attachment);

      if ($filetype['type'] === false) {
        continue;
      }

      $attachments[] = array(
        "type" => $filetype['type'],
        "name" => $path_info['basename'],
        "content" => $this->encodeFile($attachment),
      );
    }

    return $attachments;
  }

  /**
   * Return the EMail `To` field, along with the 'CC' and 'BCC' email list
   *
   * @param string|array $to
   * @return array
   */
  private function get_recipients()
  {
    $to = $this->to;
    if (!is_array($this->to)) {
      $to = explode(',', $this->to);
    }

    $processed_to = array();

    foreach ($to as $email) {
      $processed_to[] = array(
        'email' => $this->extract_rfc_value($email),
        'name' => $this->extract_rfc_key($email),
        'type' => "to"
      );
    }

    foreach ($this->headers as $header) {
      switch (strtolower($header['name'])) {
        case 'cc':
          $cc = explode(',', $header['content']);
          foreach ($cc as $cc_email) {
            $processed_to[] = array(
              'email' => $this->extract_rfc_value($cc_email),
              'name' => $this->extract_rfc_key($cc_email),
              'type'  => 'cc',
            );
          }
          break;

        case 'bcc':
          $bcc = explode(',', $header['content']);
          foreach ($bcc as $bcc_email) {
            $processed_to[] = array(
              'email' => $this->extract_rfc_value($bcc_email),
              'name' => $this->extract_rfc_key($bcc_email),
              'type'  => 'bcc',
            );
          }
          break;
      }
    }


    return $processed_to;
  }

  /**
   * Returns the `From` name for the EMail
   *
   * @return string
   */
  private function get_from_name()
  {
    $from_name = get_option('primail_default_from_name');
    $from_name = apply_filters('wp_mail_from_name', $from_name);

    foreach ($this->headers as $header) {
      switch (strtolower($header['name'])) {
        case 'from':
          $header_from_name = $this->extract_rfc_key($header['content']);
          if (!empty($header_from_name)) {
            $from_name = $header_from_name;
          }
          break;
      }
    }

    return $from_name;
  }

  /**
   * Returns the `From` email for the EMail
   *
   * @return string
   */
  private function get_from_email()
  {
    $from_email = get_option('primail_default_from_email');
    $from_email = apply_filters('wp_mail_from', $from_email);

    foreach ($this->headers as $header) {
      switch (strtolower($header['name'])) {
        case 'from':
          $header_from_email = $this->extract_rfc_value($header['content']);
          if (!empty($header_from_email)) {
            $from_email = $header_from_email;
          }
          break;
      }
    }

    return $from_email;
  }

  /**
   * Returns the email headers
   *
   * @return array
   */
  private function get_headers()
  {
    $headers = array();
    foreach ($this->headers as $header) {
      switch (strtolower($header['name'])) {
        case 'reply-to':
          $headers[trim($header['name'])] = trim($header['content']);
          break;

        default:
          if ('x-' === substr($header['name'], 0, 2)) {
            $headers[trim($header['name'])] = trim($header['content']);
          }
          break;
      }
    }

    return $headers;
  }

  /**
   * Returns true if the email has been flagged as Important
   *
   * @return boolean
   */
  private function is_important()
  {
    $is_important = false;

    foreach ($this->headers as $header) {
      switch (strtolower($header['name'])) {
        case 'importance':
        case 'x-priority':
        case 'x-msmail-priority':
          $is_important = (strpos(strtolower($header['content']), 'high') !== false) ? true : false;
      }
      break;
    }

    return $is_important;
  }

  /**
   * Returns the key of a string formatted as RFC 2822
   * e.g. `String <string@example.com>` > `String`
   *
   * @param string $rfc_string
   * @return string
   */
  private function extract_rfc_key($rfc_string)
  {
    preg_match('/<[\s\S]+?>/', $rfc_string, $matches);
    if (sizeof($matches) === 1) {
      return trim(preg_replace('/<[\s\S]+?>/', '', $rfc_string));
    }

    return "";
  }

  /**
   * Returns the value of a string formatted as RFC 2822
   * e.g. `String <string@example.com>` > `string@example.com`
   * 
   * @param string $rfc_string
   * @return string
   */
  private function extract_rfc_value($rfc_string)
  {
    preg_match('/<[\s\S]+?>/', $rfc_string, $matches);
    if (sizeof($matches) === 1) {
      return substr($matches[0], 1, -1);
    }

    return trim($rfc_string);
  }

  /**
   * Encode a file attachment to base64
   *
   * @param string $path     The full path to the file
   * @return string
   */
  protected function encodeFile($path)
  {
    $file_buffer = file_get_contents($path);
    return chunk_split(base64_encode($file_buffer), 76, "\r\n");
  }
}
