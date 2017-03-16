<?php

// 1= debug mode 0= production mode
define('DEBUG', 1);
define('PATH', dirname(dirname(__FILE__)));
define('EXTRA_PATH', '');
define('HTTP_URL', 'http://' . @$_SERVER['SERVER_NAME']. EXTRA_PATH);
define('HTTPS_URL', 'http://' . @$_SERVER['SERVER_NAME']. EXTRA_PATH);

/*
	RDBMS PARAMETERS DEFINED HERE
*/
define('DB_HOST', 'localhost');			// database host   (or ip like 192.168.0.1)
define('DB_USERNAME', 'root'); 			// Database user
define('DB_PASSWORD', 'tolexo');		// Database Password
define('DB_DATABASE', 'todo'); 			// Database name

define('IST_TIME_ADJUSTMENT', '+5 hours +30 minutes');

/*
 *   Slack configurations
 */
define('SLACK_TOKEN', 'xX3VIMBe2dUMswPJi8NnyeXK');			// Slack Token for Verification   (or ip like 192.168.0.1)

define('MAX_TODOS', 200);