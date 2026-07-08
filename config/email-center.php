<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Amazon SES Email Center
    |--------------------------------------------------------------------------
    |
    | All campaign, automation and transactional mail is sent through the
    | existing Amazon SES account. The configuration set below must have an
    | SNS event destination pointing at the topic ARN so delivery, open,
    | click, bounce and complaint events flow back into the application via
    | POST /webhooks/aws/ses/events.
    |
    */

    'configuration_set' => env('SES_CONFIGURATION_SET', 'harmoniva-email-events'),

    // Only SNS notifications whose TopicArn is in this list are accepted.
    'allowed_topic_arns' => array_filter([
        env('SES_EVENT_TOPIC_ARN'),
        env('SES_SUPPORT_TOPIC_ARN'),
    ]),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@harmoniva.app'),
        'name' => env('MAIL_FROM_NAME', 'Harmoniva'),
    ],

    'reply_to' => env('MAIL_REPLY_TO_ADDRESS', 'support@harmoniva.app'),

    'support_address' => env('SUPPORT_EMAIL_ADDRESS', 'support@harmoniva.app'),

    // How support@harmoniva.app inbound mail is integrated:
    // "imap"     — poll the local Dovecot mailbox over IMAP (MX stays on this server)
    // "external" — show a link to the external webmail, no in-app inbox
    // "disabled" — hide the support inbox module
    'support_inbound_mode' => env('SUPPORT_INBOUND_MODE', 'imap'),

    'support_imap' => [
        'host' => env('SUPPORT_IMAP_HOST', '127.0.0.1'),
        'port' => (int) env('SUPPORT_IMAP_PORT', 993),
        'encryption' => env('SUPPORT_IMAP_ENCRYPTION', 'ssl'),
        'validate_cert' => (bool) env('SUPPORT_IMAP_VALIDATE_CERT', false),
        'username' => env('SUPPORT_IMAP_USERNAME', 'support@harmoniva.app'),
        'password' => env('SUPPORT_IMAP_PASSWORD'),
        'folder' => env('SUPPORT_IMAP_FOLDER', 'INBOX'),
    ],

    'webmail_url' => env('SUPPORT_WEBMAIL_URL', 'https://server.harmoniva.app:20000/'),

    // Defaults; the live values are read from SystemSetting and fall back here.
    // frequency_cap: max marketing emails per user per rolling 7 days.
    'frequency_cap' => (int) env('EMAIL_FREQUENCY_CAP', 3),

    // Emails per second pushed to SES (account MaxSendRate is 14/s).
    'send_rate_per_second' => (int) env('EMAIL_SEND_RATE', 10),

    // Soft bounces on one address within 30 days before it is flagged in admin.
    'soft_bounce_threshold' => 3,

    'test_recipient' => env('EMAIL_TEST_RECIPIENT'),
];
