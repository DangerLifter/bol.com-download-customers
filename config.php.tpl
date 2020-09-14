<?php

/*
Array with list of bol.com API clients configurations:

$API_CONFIG = [
	[
		'clientId' => 'xxxxxx-xxxx-xxxxxxxxxxx1',
		'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
		'clientName' => 'MyAccount1',
		'proxy' => '100.100.100.100:80',
	],
	[
		'clientId' => 'xxxxxx-xxxx-xxxxxxxxxxx2',
		'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
		'clientName' => 'MyAccount2',
		'proxy' => '100.100.100.101:80',
	]
];

*/

$API_CONFIG = [
	[
		'clientId' => '',
		'key' => '',
		'clientName' => 'MyAccount',
		'proxy' => '', // use empty string to avoid proxy usage
	],
];

// Email addresses used to send email from and to
define('EMAIL_TO', ['']);
define('EMAIL_FROM', '');

// Email SMTP settings to send emails
define('SMTP_HOST', '');
define('SMTP_PORT', '');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', '');