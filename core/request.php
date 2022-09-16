<?php

namespace Mandrill_Request;

/**
 * Sends the request to Mandrill Via cURL
 * See `https://mailchimp.com/developer/transactional/api/messages/send-new-message/` for more info
 *
 * @param array $email
 * @return object
 */
function send($email)
{
  $email = apply_filters('mandrill_mail', $email);

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://mandrillapp.com/api/1.0/messages/send',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($email),
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  return array(
    'status' => absint($status),
    'response' => json_decode($response)
  );
}
