<?php 
namespace NoF5;
class Inject {
	private $scriptUrl;
	
	public function __construct($scriptUrl) {
		$this->scriptUrl = $scriptUrl;
	}
		
	public function addScript() {
		ob_start(function($buffer) {
			return str_replace('</head>', '<script id="__nof5" src="' . $this->scriptUrl . '?nof5=getScript"></script></head>', $buffer);
		});
	}

	public function getScript() {
		header('Content-type: text/javascript');
		echo file_get_contents('./nof5.js');
		die;
	}
	
	public function nooutput() {
		ini_set('display_errors', 'off');
		error_reporting(0);
		ob_start(function($buffer) {
			return '';
		});
	}
}
