<?php
namespace NoF5;

require_once 'FileMonitor.php';
require_once 'MonitoredFile.php';
require_once 'NoF5.php';

$noF5 = new NoF5(getcwd(), $_SERVER['SCRIPT_FILENAME'], $_SERVER['REQUEST_URI'], $_GET, $_POST);
if (isset($_GET['nof5'])) {
	$action = $_GET['nof5'];
	$args = isset($_GET['nof5arg']) ? $_GET['nof5arg'] : []; 
	$output = call_user_func_array([$noF5, $action], $args);
	header ('Content-Length: ' . strlen($output));
	echo $output;
	die;
}
else if (isset($argv[1]) && $argv[1] == 'nooutput') {
	$noF5->nooutput();
}
else {
	$noF5->addScript();
}
