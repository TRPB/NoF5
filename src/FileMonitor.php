<?php 
namespace NoF5;
class FileMonitor {
	private $files = [];
	private $extensionsToWatch = ['css', 'js', 'php', 'xml', 'tpl'];
	private $scriptName;
	private $baseDir;
	private $requestUri;
	private $time;
	private $session;
	
	public function __construct($scriptName, $get, $post, $session, $baseDir, $requestUri) {
		$this->scriptName = $scriptName;
		$this->get = $get;
		$this->post = $post;
		$this->baseDir = $baseDir;
		$this->session = $session;
		$this->requestUri = $requestUri;
		$this->time = time();
		$this->registerFiles();
	}
	
	public function registerFile($name) {
		$this->monitorFile($this->baseDir, $name);
	}
	
	public function monitorFile($baseDir, $fileName) {
		$this->files[$baseDir . '/' . $fileName] = new MonitoredFile($baseDir, $fileName);
	}

	private function getChangedFiles() {
		$changed = [];
		foreach ($this->files as $file) {
			if ($file->isModified()) $changed[] = $file->getFileName();
		}

		return $changed;
	}
	
	
	public function registerFiles() {
		//Register .htaccess if it exists
		if (file_exists($this->baseDir . '/.htaccess')) $this->monitorFile($this->baseDir, '.htaccess');

		exec('strace -f -t -e trace=open php ' . $this->scriptName . ' nooutput "' . http_build_query($this->get). '" "' . http_build_query($this->post) . '" "' . $this->requestUri . '" "' . http_build_query($this->session) . '"  2>&1', $output);
		header('content-type: text/plain');
		foreach ($output as $line) {
			$firstQuote = strpos($line, '"');
			if ($firstQuote !== false) {
				$lastQuote = strpos($line, '"', $firstQuote+1);
	
				$info = pathinfo(substr($line, $firstQuote+1, $lastQuote-$firstQuote-1));
				if (isset($info['extension']) && in_array(strtolower($info['extension']), $this->extensionsToWatch)) $this->monitorFile($info['dirname'], $info['basename']);
			}
		}
		
	}
	
	public function monitor() {
		$this->time = time();
		ini_set('display_errors', 'off');
		header("Content-Type: text/event-stream\n\n");
		return 'data: ' . json_encode($this->getChangedFiles()) . "\n\n";
	}
	
	public function getTime() {
		return $this->time;
	}

}