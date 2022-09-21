## Mandrill Mail

By default, WordPress uses PHP Mail to send its emails. If you are reading this, you probably know that this isn't a perfect solution. Some hosting providers put a limit on the amount of emails that they can process, and there is no log's or records of emails once they are sent.

This plugin automatically handles redirecting emails that are normally sent by WordPress to Mandrill instead. Headers, Attachments, Content Type .etc are all automatically handled. There is minimal setup involved. Simply add your Mandrill API Key and your 'From Name' and 'EMail' and your good to go.

### Why use this plugin

There are many WordPress plugins that use SMTP (Simple Mail Transfer Protocol) to send emails, and this can be a great solution that integrates with many mail providers like Mailgun, SendGrid, PostMark, and many more. This plugin is build specifically to work with Mandrill, and therefore it offers greater 'out of the box' support for Mandrill, with minimal config required to get up and running.

### Built for Developers

This plugin is set up to send emails via Mandrill's JSON API, rather than their SMTP API. This enables a few cool extra's if you are a developer, for example you can pass extra information into the email such as 'Merge Vars', 'Tags', 'Google Analytics', 'Templates' and anything else from the [Mandrill Docs](https://mailchimp.com/developer/transactional/api/messages/send-new-message/). You can even schedule emails to send at a specific time using the 'send_at' paramater.

### Developers 101

In terms of compatability with 'wp_mail', all the regular WordPress Mail hooks ('wp_mail_succeeded', 'wp_mail_failed') and filters (wp_mail_from, wp_mail_from_name, wp_mail_content_type) are supported. 

There is also a `mandrill_mail` filter which you can hook into just before a request is sent to Mandrill. For example if you want to add some merge tags (as per the [Mandrill Docs](https://mailchimp.com/developer/transactional/api/messages/send-new-message/)), you could filter the email like so.

``` php
add_filter('mandrill_mail', function ($email) {
  $email['message']["merge"] = true;
  $email['message']["merge_language"] = 'handlebars';
  $email['message']["global_merge_vars"] = array(
    array(
      "name" => "firstname",
      "content" => ""
    ),
    array(
      "name" => "lastname",
      "content" => "Smyth"
    )
  
  return $email;
});
```

There is a `mandrill_mail_response` filter available, which you can use to filter the api response from mandrill. This could be useful for debugging and to determine that there are no error messages coming back from the api.

If you want to send an email directly and bypass the 'WP_Mail function altogether, you can do so. However you will need to pass in all the data yourself, including the API Key.

``` php
  Mandrill_Request\send(array(
    "key" => '...',
    "message" => array(
      "headers" => '...',
      "subject" => '...',
      "from_email" => '...',
      "from_name" => '...',
      "to" => '...',
    ),\
  ));
```

Finally, the Mandrill Settings are stored as WordPress Options. They can be retrieved as follows:

``` php
get_option('mandrill_mail_api_key');
get_option('mandrill_mail_api_test_key');

get_option('mandrill_mail_default_from_name');
get_option('mandrill_mail_default_from_email');

get_option('mandrill_mail_api_debug_enabled');
```

If you need to change these, you could use the 'pre_option' filter to do so. For example, to change the value of the API key, you could do the following:

``` php
add_filter("pre_option_mandrill_mail_api_key", function ($opt) {
  return 'foo';
});
```
