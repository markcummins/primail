<?php

namespace Primail_Request;

/**
 * Sends the request to The Mandrill API
 * See `https://mailchimp.com/developer/transactional/api/messages/send-new-message/` for more info
 *
 * @param array $email
 * @return object
 */
function send($email)
{
  $email = apply_filters('primail_request', $email);
  $api = 'https://mandrillapp.com/api/1.0/messages/send';

  $response = wp_remote_post($api, array(
    'method' => 'POST',
    'timeout' => 30,
    'redirection' => 5,
    'headers' => array(
      'Content-Type: application/json'
    ),
    'body' => $email,
  ));

  if (is_wp_error($response)) {
    return apply_filters('primail_response', array(
      'status' => 500,
      'response' => $response->get_error_message()
    ));
  }

  return apply_filters('primail_response', array(
    'status' => absint($response['response']['code']),
    'response' => json_decode($response['body'])
  ));
}
