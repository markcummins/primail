<?php

/**
 * Class PluginCI_Mandrill
 *
 * @test
 * @package Mandrill_Mail
 */

/**
 * Sample test case.
 */
class PluginCI_Mandrill extends WP_UnitTestCase
{
	protected function setUp()
	{
		update_option('mandrill_mail_api_key', MANDRILL_API_DEV_KEY);
		update_option('mandrill_mail_api_test_key', MANDRILL_API_DEV_KEY);
	}

	/**
	 * Checks for a valid HTTP response to a Sent EMail
	 */
	public function test_email_send()
	{
		$mail = $this->send(array(
			'to' => 'sendto@example.com',
			'subject' => 'The subject',
			'message' => 'The email body content',
			'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
		));

		$response = $mail->get_response();
		$this->assertTrue($response['status'] === 200);
	}

	/**
	 * Sends an email via the Mandrill API
	 *
	 * @param array $atts
	 * @return object|null
	 */
	private function send($atts)
	{
		$mail = new Mandrill_Mail($atts);
		$mail->send();

		return $mail;
	}
}
