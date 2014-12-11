<?php 
namespace NoF5;
class FileMonitor {
	private $files = [];
	
	public function monitorFile($baseDir, $fileName) {
		if (isset($this->files[$fileName])) return;
		$this->files[$fileName] = new MonitoredFile($baseDir, $fileName);
	}

	public function getChangedFiles() {
		$changed = [];
		foreach ($this->files as $file) {
			if ($file->isModified()) $changed[] = $file->getFileName();
		}

		return $changed;
	}
}