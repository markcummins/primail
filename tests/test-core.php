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
	private $attachment_ids;

	protected function setUp()
	{
		parent::setUp();

		update_option('mandrill_mail_api_key', MANDRILL_API_DEV_KEY);
		update_option('mandrill_mail_api_test_key', MANDRILL_API_DEV_KEY);
		update_option('mandrill_mail_default_from_email', 'wordpress@localhost.com');

		$this->attachment_ids = array(
			'img' => $this->factory->attachment->create_upload_object(DIR_TESTDATA . '/images/test-image.png', 0),
			'csv' => $this->factory->attachment->create_upload_object(DIR_TESTDATA . '/uploads/test.csv', 0)
		);
	}

	/**
	 * Checks a Basic EMail can be sent
	 */
	public function test_email_send_to_single()
	{
		$mail = $this->send(array(
			'to' => 'mandrill-mail@proton.me',
			'subject' => 'The subject',
			'message' => 'The email body content',
			'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
		));

		$response = $mail->get_response();
		$this->assertEquals($response['status'], 200, "Single Email gots sended");
	}

	/**
	 * Checks that an email can be sent to multiple participants
	 */
	public function test_email_send_to_multiple()
	{
		$mail = $this->send(array(
			'to' => array(
				'recipient@foo.com',
				'Bar <recipient@bar.com>'
			),
			'subject' => 'The subject',
			'message' => 'The email body content',
			'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
		));

		$response = $mail->get_response();

		$this->assertEquals($response['status'], 200, "Single Email gots sended");

		$this->assertEquals(sizeof($response['response']), 2, "EMail was sent to multiple participants");

		$this->assertTrue(in_array('recipient@foo.com', array_column($response['response'], 'email')), "foo was a recipient");
		$this->assertTrue(in_array('recipient@bar.com', array_column($response['response'], 'email')), "bar was a recipient");

		$this->assertEquals(count(array_keys(array_column($response['response'], 'status'), 'queued')), 2, "All emails are queued");
	}

	/**
	 * Checks that EMails can be sent with CC Headers
	 *
	 * @return void
	 */
	public function test_email_send_headers_cc()
	{
		$mail = $this->send(array(
			'to' => 'recipient@foo.com',
			'subject' => 'The subject',
			'message' => 'The email body content',
			'headers' =>  array(
				'Content-Type: text/html; charset=UTF-8',
				'Cc: Johnny Smyth Jr. <jsmyth@foo.com>',
				'Cc: billygates@bar.com',
			),
		));

		$response = $mail->get_response();

		$this->assertEquals($response['status'], 200, "Single Email gots sended");

		$this->assertEquals(sizeof($response['response']), 3, "EMail was sent to multiple participants");

		$this->assertTrue(in_array('recipient@foo.com', array_column($response['response'], 'email')), "recipient was a recipient");
		$this->assertTrue(in_array('jsmyth@foo.com', array_column($response['response'], 'email')), "jsmyth was a recipient");
		$this->assertTrue(in_array('billygates@bar.com', array_column($response['response'], 'email')), "billygates was a recipient");

		$this->assertEquals(count(array_keys(array_column($response['response'], 'status'), 'queued')), 3, "All emails are queued");
	}

	/**
	 * Checks that attachments can be sent
	 *
	 * @return void
	 */
	public function test_email_send_with_attachments()
	{
		$mail = $this->send(array(
			'to' => 'mandrill-mail@proton.me',
			'subject' => 'The subject',
			'message' => 'The email body content',
			'headers' =>  array('Content-Type: text/html; charset=UTF-8'),
			'attachments' => array(
				get_attached_file($this->attachment_ids['img']),
				get_attached_file($this->attachment_ids['csv'])
			),
		));

		$email = $mail->get_email();
		$this->assertEquals(count($email['message']['attachments']), 2, "EMail has two attachments");

		$response = $mail->get_response();
		$this->assertEquals($response['status'], 200, "Single Email gots sended");

		$this->assertEquals(count(array_keys(array_column($response['response'], 'status'), 'queued')), 1, "The EMail is Queued");
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
