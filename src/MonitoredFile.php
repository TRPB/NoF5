<?php
namespace NoF5;
class MonitoredFile {
	private $lastMTime;
	private $fileName;
	private $baseDir;

	public function __construct($baseDir, $fileName) {
		$this->baseDir = $baseDir;
		$this->fileName = $fileName;
		$this->lastMTime = filemtime($this->baseDir . '/' . $fileName);
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