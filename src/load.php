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
		$_SERVER['REQUEST_URI'] = $argv[4];
		$injector->noOutput();
	} 
	else $injector->addScript();
}
else { //If there is a script ID set a file is being monitored or registered.
	if (!session_id()) session_start();

	if (isset($_SESSION['_nof5'][$scriptId])) $fileMonitor = unserialize($_SESSION['_nof5'][$scriptId]);
	else $fileMonitor = new FileMonitor($_SERVER['SCRIPT_FILENAME'], $_GET, $_POST, getcwd(), $_SERVER['REQUEST_URI']); 
			
	$output = call_user_func_array([$fileMonitor, $action], isset($_GET['nof5arg']) ? $_GET['nof5arg'] : []);
	header ('Content-Length: ' . strlen($output));
	$_SESSION['_nof5'][$scriptId] = serialize($fileMonitor);
	
	//Clean up unused filemonitors
	foreach ($_SESSION['_nof5'] as $id => $fm) {
		$mon = unserialize($fm);
		if ($mon->getTime() < time()-60) unset($_SESSION['_nof5'][$id]);
	}
	
	session_write_close();	
	echo $output;
	die;	
}