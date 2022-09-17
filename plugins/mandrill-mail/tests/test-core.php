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
	/**
	 * A single example test.
	 */
	public function test_sample()
	{
		$this->assertTrue(22 === 22);
		$this->assertTrue(get_option('mandrill_mail_api_test_key') === 'W6hVju3p9PLKL-93rlm4RA');
		// $mail = $this->send(array(
		// 	'to' => 'sendto@example.com',
		// 	'subject' => 'The subject',
		// 	'message' => 'The email body content',
		// 	'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
		// ));

		// $response = $mail->get_response();

		// $this->assertTrue($response['status'] === 200);
	}

	/**
	 * Sends an email via the Mandrill API
	 *
	 * @param array $atts
	 * @return object|null
	 */
	// private function send($atts)
	// {
	// 	$mail = new Mandrill_Mail($atts);
	// 	$mail->send();

	// 	return $mail;
	// }
}
