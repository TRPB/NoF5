<?php
namespace NoF5;

require_once 'FileMonitor.php';
require_once 'MonitoredFile.php';
require_once 'Inject.php';


$scriptId = isset($_GET['nof5id']) ? $_GET['nof5id'] : null;
$action = isset($_GET['nof5']) ? $_GET['nof5'] : null;

//If no script ID is set, then the orignal script needs to run in am modified way
if (!$scriptId) {
	$injector = new Inject($_SERVER['REQUEST_URI']);	
	if ($action) $injector->$action();
	else if (isset($argv[1]) && $argv[1] == 'nooutput') {
		parse_str($argv[2], $_GET);
		parse_str($argv[3], $_POST);
		session_start(); //Start the session so it has a valid ID & attributes, then populate it with $argv[5]
		parse_str($argv[5], $_SESSION);		
		$_SERVER['REQUEST_URI'] = $argv[4];
		$injector->noOutput();
	} 
	else $injector->addScript();
}
else { //If there is a script ID set a file is being monitored or registered.
	$args = isset($_GET['nof5arg']) ? $_GET['nof5arg'] : [];

	if (!session_id()) session_start();

	if (isset($_SESSION['_nof5'][$scriptId])) $fileMonitor = unserialize($_SESSION['_nof5'][$scriptId]);
	else {
		//Copy the session so that pages behind logins can be straced
		$nSession = $_SESSION;
		//Don't pass the nof5 sesison information to the child script
		unset($nSession['_nof5']);		
		$fileMonitor = new FileMonitor($_SERVER['SCRIPT_FILENAME'], $_GET, $_POST, $nSession, getcwd(), $_SERVER['REQUEST_URI']); 
	}

	
	$output = call_user_func_array([$fileMonitor, $action], $args);
	header ('Content-Length: ' . strlen($output));
	$_SESSION['_nof5'][$scriptId] = serialize($fileMonitor);
	
	//Clean up unused filemonitors. Only do this 10% of the time to improve performance	
	if (rand(1, 10) == 5) { 
		foreach ($_SESSION['_nof5'] as $id => $fm) {
			$mon = unserialize($fm);
			if ($mon->getTime() < time()-60) unset($_SESSION['_nof5'][$id]);
		}
	}
		
	echo $output;
	die;	
}