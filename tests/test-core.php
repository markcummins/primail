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
	// public function test_sample()
	// {
	// 	// $this->assertTrue(getenv("MANDRILL_API_DEV_KEY") === 'W6hVju3p9PLKL-93rlm4RA');

	// 	// $mail = $this->send(array(
	// 	// 	'to' => 'sendto@example.com',
	// 	// 	'subject' => 'The subject',
	// 	// 	'message' => 'The email body content',
	// 	// 	'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
	// 	// ));

	// 	// $response = $mail->get_response();

	// 	// $this->assertTrue($response['status'] === 200);
	// }

	public function test_a()
	{
		$this->assertTrue(defined('MANDRILL_API_DEV_KEY') && MANDRILL_API_DEV_KEY === 'W6hVju3p9PLKL-93rlm4RA');
	}

	public function test_b()
	{
		$this->assertTrue(defined('WP_DEBUG') && WP_DEBUG === true);
	}

	public function test_c()
	{
		$this->assertTrue(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG === true);
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
