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
		update_option('mandrill_mail_api_key', getenv('MANDRILL_API_DEV_KEY'));
		update_option('mandrill_mail_api_test_key', getenv('MANDRILL_API_DEV_KEY'));
		update_option('mandrill_mail_default_from_name', 'test');
		update_option('mandrill_mail_default_from_email', 'test@test.test');
		update_option('mandrill_mail_api_debug_enabled', 1);
	}

	/**
	 * A single example test.
	 */
	public function test_sample()
	{
		$this->assertTrue(get_option('mandrill_mail_api_test_key') === 'W6hVju3p9PLKL-93rlm4RA');

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
