<?php
namespace NoF5;
class MonitoredFile {
	private $lastMTime;
	private $fileName;
	private $baseDir;

	public function __construct($baseDir, $fileName) {
		$this->baseDir = $baseDir;
		$this->fileName = ltrim($fileName, '/');
		$this->lastMTime = is_file($this->baseDir . '/' . $this->fileName) ? filemtime($this->baseDir . '/' . $this->fileName) : 0;
	}

	public function isModified() {
		if (filemtime($this->baseDir . '/' . $this->fileName) > $this->lastMTime) {
			$this->lastMTime = filemtime($this->baseDir . '/' . $this->fileName);
			return true;
		}
		else return false;
	}

	public function getFileName() {
		return $this->fileName;
	}
}