<?php 
namespace NoF5;
class NoF5 {
	private $scriptName, $scriptUrl, $get, $post, $baseDir;
	private $fileMonitor = [];
	private $extensionsToWatch = ['css', 'js', 'php', 'xml', 'tpl'];
	
	public function __construct($baseDir, $scriptName, $scriptUrl, $get, $post) {
		if (!session_id()) session_start();
		if (!isset($_SESSION['_nof5'])) $_SESSION['_nof5'] = [];
		if (isset($_SESSION['_nof5'])) $this->fileMonitor = unserialize($_SESSION['_nof5']); 
		
		$this->scriptName = $scriptName;
		$this->scriptUrl = $scriptUrl;
		$this->get = $get;
		$this->post = $post;		
		$this->baseDir = $baseDir;
	}
	
	public function registerFiles() {
		$fileMonitor = new FileMonitor($this->scriptName);
		
		//Register .htaccess if it exists
		$fileMonitor->monitorFile($this->baseDir, '.htaccess');
		
		exec('strace -f -t -e trace=open php ' . $this->scriptName . ' nooutput 2>&1', $output);

		foreach ($output as $line) {			
			$firstQuote = strpos($line, '"');
			if ($firstQuote !== false) { 
				$lastQuote = strpos($line, '"', $firstQuote+1);
				$fileName = substr($line, $firstQuote+1, $lastQuote-$firstQuote-1);
				
				$info = pathinfo($fileName);
				
				if (isset($info['extension']) && in_array(strtolower($info['extension']), $this->extensionsToWatch)) $fileMonitor->monitorFile('', $fileName);
			} 
		}		
		
		$this->fileMonitor[$this->scriptName] = $fileMonitor;
	}
	
	public function registerFile($file) {
		$this->fileMonitor[$this->scriptName]->monitorFile($this->baseDir, $file);
	}
	
	public function addScript() {
		ob_start(function($buffer) {
			return str_replace('</head>', '<script src="' . $this->scriptUrl . '?nof5=getScript"></script>', $buffer);
		});
	}
	
	public function getScript() {
		header('Content-type: text/javascript');
		return file_get_contents('./nof5.js');
	}
	
	public function nooutput() {
		ini_set('display_errors', 'off');
		error_reporting(0);
		ob_start(function($buffer) {
			return '';
		});
	}
	public function monitor() {
		ini_set('display_errors', 'off');
		header("Content-Type: text/event-stream\n\n");
		return 'data: ' . json_encode($this->fileMonitor[$this->scriptName]->getChangedFiles()) . "\n\n";
	}
	
	public function __destruct() {
		$_SESSION['_nof5'] = serialize($this->fileMonitor);
		session_write_close();
	}
}