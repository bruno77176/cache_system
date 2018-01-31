<?php
namespace OCFram;

class Cache 
{
	public function __construct($duration= null, $dirname = null)
	{
		$this->setDuration($duration);
	}

	public function read($file)
	{
		return file_get_contents($file);
	}

	public function add($file, $content)
	{
		file_put_contents($file, $content);
	}

	public function delete($file)
	{
		unlink($file);
	}

	public function duration()
	{
		return $this->duration;
	}


	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

}