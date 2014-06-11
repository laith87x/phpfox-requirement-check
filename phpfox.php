<?php

error_reporting(0);

// Make sure we are running PHP5
if (version_compare(phpversion(), '5', '<') === true) {
	exit('PHPfox 2.x requires PHP 5 or newer.');
}

if (function_exists('session_start')) {
	session_start();
}

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('GMT');	
}

@ini_set('memory_limit', '64M');

class PHPfox {
	private static $_sHome = 'http://moxi9.com/phpfox';
	
	private static $_aErrors = array();
	
	private static $_bPostCheck = false;
	
	public static function getHome() {
		return self::$_sHome;
	}
	
	public static function checkPostData() {
		$aVals = $_POST['val'];
		
		if (empty($aVals['host'])) {
			self::setError('Provide a database host.');
		}	
		
		if (empty($aVals['name'])) {
			self::setError('Provide a database name.');
		}
		
		if (empty($aVals['user_name']))
		{
			self::setError('Provide a database user name.');
		}

		if (!self::hasErrors()) {
		 	$hDb = @mysql_connect($aVals['host'], $aVals['user_name'], $aVals['password']);
	
		 	if (!$hDb) {
		 	 	self::setError('Unable to connect to your database with the information provided.');
		 		
		 		return;
		 	}
	
		 	$bSelectDb = @mysql_select_db($aVals['name'], $hDb);
	
		 	if (!$bSelectDb) {
		 	 	self::setError('Unable to connect to the database table with the information provided.');
		 		
		 		return;
		 	}		 	
		}
		
		self::$_bPostCheck = true;
	}
	
	public static function showDatabaseForm() {
		if (self::$_bPostCheck === true && !self::hasErrors()) {
			return false;
		}
		
		return true;
	}
	
	public static function hasErrors() {
		return (count(self::$_aErrors) ? true : false);
	}
	
	public static function getErrors() {
		return self::$_aErrors;
	}
	
	public static function runCheck() {
		$aChecks = array();
    	$aChecks[] = array(
    	    'name'   => 'PHP5 or more',
    	    'passed' => version_compare(PHP_VERSION, '5', '>'),
    	    'type' => 'e_error',
    	    'help' => 'Your PHP Version is too low to support PHPfox, you must at least upgrade to PHP5.',
    	);
    	$aChecks[] = array(
    	    'name'   => 'PHP session support',
    	    'passed' => self::_checkSession(),
    	    'type' => 'e_error',
    	    'help' => 'PHP sessions support is needed for the user account login module.',
    	);    	
    	$aChecks[] = array(
    	    'name'   => 'PHP GD extension',
    	    'passed' => extension_loaded('gd'),
    	    'type' => 'e_error',
    	    'help' => 'GD functions are used to process images.',
    	);
    	$aChecks[] = array(
    	    'name'   => 'PHP XML support',
    	    'passed' => function_exists('xml_set_element_handler'),
    	    'type' => 'e_error',
    	    'help' => 'XML functions are used to parse XML files that are used to install products.',
    	);    
    	$aChecks[] = array(
    	    'name'   => 'PHP CURL support',
    	    'passed' => function_exists('curl_init'),
    	    'type' => 'error',
    	    'help' => 'CURL functions are used if you plan to use payment gateways.',
    	);     
    	$aChecks[] = array(
    	    'name'   => 'PHP Safe Mode OFF',
    	    'passed' => !ini_get('safe_mode'),
    	    'type' => 'e_error',
    	    'help' => 'Safe Mode must be turned OFF.',
    	);   
    	$aChecks[] = array(
    	    'name'   => 'PHP Memory Limit',
    	    'passed' => ((int) ini_get('memory_limit') >= 32 ? true : false),
    	    'type' => 'error',
    	    'help' => 'PHP memory limit should be greater than or eqauling 32M.',
    	);    	 	
    	$aChecks[] = array(
    	    'name'   => 'MySQL Connection Support',
    	    'passed' => function_exists('mysql_connect'),
    	    'type' => 'e_error',
    	    'help' => 'You will need database support to run your site.',
    	);
    	$aChecks[] = array(
    	    'name'   => 'MySQL 4.1 or more',
    	    'passed' => preg_match('#^(4\.1|5\.).*#i', mysql_get_server_info()),
    	    'type' => 'e_error',
    	    'help' => 'You will need MySQL 4.1 or more running.',
    	);
    	
		$sSubFail = '';
    	$iCount = 0;
    	$sOutput = '<table cellpadding="0" cellspacing="0">';
        foreach ($aChecks as $iKey => $aVar) {
   	 	 	$iCount++;
   	 		$sOutput .= '<tr>';
			$sOutput .= '<td class="left">'. $aVar['name'] .':</td>';
			$sOutput .= '<td>';
     	   	if (!$aVar['passed']) {
     	   	 	if ($aVar['type'] == 'error') {
   	   	 			
   	   	 			$sSubFail = " but not positive.";
   	   	 		} else {
   	   	 		 	
   	   	 		}
				$sOutput .= '<span style="color:red;">Failed'. $sSubFail .' - <a href="javascript:void(null);" onclick="document.getElementById(\'help_'. $iCount .'\').style.display=\'\';">Why?</a></span>';
    	    } else {
			 	$sOutput .= '<span style="color:green;">Passed</span>';
    	        
			 	unset($aChecks[$iKey]);
    	    }
    	    $sOutput .= '<div class="help" style="display:none;" id="help_'. $iCount .'">'. $aVar['help'] .'</div>';
			$sOutput .= '</td>';
   	 		$sOutput .= '</tr>';
    	}
		$sOutput .= '</table>';
		
		echo $sOutput;
	}
	
	private static function setError($sError) {
		self::$_aErrors[] = $sError;	
	}
	
	private static function _checkSession() {
		if (!function_exists('session_start'))
		{
			return false;
		}

		if (!isset($_SESSION['phpfox_test']))
		{
			return false;
		}

		if ($_SESSION['phpfox_test'] != 'i_am_a_session')
		{
			return false;
		}

		return true;
	}
}

if (function_exists('session_start')) {
	$_SESSION['phpfox_test'] = 'i_am_a_session';
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
	<head>
		<title>PHPfox - Social Networking Script - Moxi9</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />		
		<meta http-equiv="imagetoolbar" content="no" />
		<style type="text/css">
			body {
				background:#fff;
				color:#222;
				font-family:verdana;
				font-size:90%;
				margin:0px;
				padding:0px;
			}

			#header {
				background:#0C0C0C;
				color:#fff;
				position:relative;
				height:50px;
				line-height:50px;
			}

			#logo a,
			#logo a:hover {
				margin-left:20px;
				display:inline-block;
				color:#fff;
				font-size:1.2em;
				text-decoration:none;
				font-weight:300;
				text-decoration:none;
				letter-spacing:2px;
			}

			#header_menu {
				position:absolute;
				right:20px;
				top:0px;
			}

			#header_menu ul {
				margin:0px;
				padding:0px;
				list-style-type:none;
			}

			#header_menu ul li {
				display:inline-block;
				margin-left:20px;
			}

			#header_menu ul li a,
			#header_menu ul li a:hover {
				color:#fff;
				text-transform:uppercase;
				opacity:0.6;
				text-decoration:none;
				letter-spacing:1.5px;
				font-size:0.8em;
			}

			#header_menu ul li a:hover {
				opacity:1;
			}

			#content {
				width:500px;
				margin:auto;
			}

			.form input {
				padding:0px;
				margin:0px;
				border:0px;
				background:transparent;
				width:100%;
				font-size:1.2em;
				opacity:0.7;
			}

			.form {
				background:#fff;
				padding:10px;
				border:1px #dfdfdf solid;
				margin-bottom:10px;
			}

			h1 {
				font-size:1.6em;
				font-weight:300;
				color:#222;
			}

			p {
				font-size:0.9em;
				color:#999999;
			}

			#copyright {
				text-align:center;
				color:#999999;
				font-size:0.8em;
				padding-top:10px;
				border-top:1px #dfdfdf solid;
				margin-top:15px;
				padding-top:10px;
			}

			.form_submit input {
				background:#71B33D;
				color:#fff;
				border:0px;
				border-radius:3px;
				padding:10px 20px 10px 20px;
				font-size:1em;
				text-transform:uppercase;
				text-align:center;
				letter-spacing:2px;
				cursor:pointer;
			}

			.error {
				background:#EA5859;
				color:#fff;
				padding:8px;
				margin-bottom:10px;
				border-radius:3px;
			}

			table {
				width:100%;
			}

			td {
				border-top:1px #dfdfdf solid;
				padding:10px 0px 10px 0px;
				vertical-align:top;
			}

			td.left {
				text-align:left;
				width:60%;
			}

			a,
			a:hover {
				color:#71B33D;
			}

			.help {
				background:#f6f6f6;
				padding:10px;
				color:#999999;
				font-size:0.9em;
				margin-top:5px;
			}
		</style>
	</head>
	<body>
		<div id="header">
			<div class="holder_logo">
				<div id="header_menu">
					<ul>						
						<li><a href="http://moxi9.com/contact">Contact Us</a></li>
						<li><a href="<?php echo Phpfox::getHome(); ?>/pricing" class="buy_now no_border">Buy Now</a></li>
					</ul>
				</div>
				<div id="logo">
					<a href="<?php echo Phpfox::getHome(); ?>" title="PHPfox">PHPfox</a>
				</div>
			</div>
		</div>

		<div id="content">
			<h1>PHPfox Requirement Check</h1>

			<?php if (isset($_POST['val'])): ?>
			<?php Phpfox::checkPostData(); ?>
			<?php endif; ?>
			<?php if (Phpfox::showDatabaseForm()): ?>
			<?php if (Phpfox::hasErrors()): ?>
			<?php foreach (Phpfox::getErrors() as $sError): ?>
			<div class="error"><?php echo $sError; ?></div>
			<?php endforeach; ?>
			<?php else: ?>
			<p>In order for us to test your server you will have to enter your MySQL details below.</p>
			<?php endif; ?>

			<form method="post" action="phpfox.php">
				<div class="form">
					<input placeholder="Database Host" type="text" name="val[host]" value="<?php echo (isset($_POST['val']['host']) ? htmlspecialchars($_POST['val']['host']) : ''); ?>" size="40" />
				</div>

				<div class="form">
					<input placeholder="Database Name" type="text" name="val[name]" value="<?php echo (isset($_POST['val']['name']) ? htmlspecialchars($_POST['val']['name']) : ''); ?>" size="40" />
				</div>

				<div class="form">
					<input placeholder="Database User Name" type="text" name="val[user_name]" value="<?php echo (isset($_POST['val']['user_name']) ? htmlspecialchars($_POST['val']['user_name']) : ''); ?>" size="40" />
				</div>

				<div class="form">
					<input placeholder="Database Password" type="password" name="val[password]" value="<?php echo (isset($_POST['val']['password']) ? htmlspecialchars($_POST['val']['password']) : ''); ?>" size="40" />
				</div>

				<div class="form_submit">
					<input type="submit" value="Submit" class="button" />
				</div>
			</form>

			<?php else: ?>
			<?php Phpfox::runCheck(); ?>
			<?php endif; ?>

			<div id="copyright">
				Moxi9 &copy; <?php echo date('Y', time()); ?>
			</div>
		</div>
	</body>
</html>
