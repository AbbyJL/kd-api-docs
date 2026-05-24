<?php


class File
{
	public $files = [];
	public $error;
	

	public function getFiles($path)
	{
		if (is_dir($path)) {
			$path = dirname($path) . '/' . basename($path) . '/';
			$file = dir($path);
			while (false !== ($entry = $file->read())) {
				if ($entry !== '.' && $entry !== '..') {
					$cur = $path . $entry;
					if (is_dir($cur)) {
						$subPath = $cur . '/';
						$this->getFiles($subPath);
					}
					$this->files[] = $cur;
				}
			}
			$file->close();
			return $this->files;
		} else {
			$this->error = $path . 'not a dir';
			return $this->error;
		}
	}
	

	public function rmFiles($path)
	{
		$ret = '删除成功';
		$files = $this->getFiles($path);
		if (!is_array($files)) {
			$ret = $files;
		} elseif (empty($files)) {
			$ret = '删除失败,目录下没有文件或目录';
		} else {
			foreach ($files as $item => $file) {
				if (is_dir($file)) {
					rmdir($file);
				} elseif (is_file($file)) {
					unlink($file);
				}
			}
		}
		return $ret;
	}
}