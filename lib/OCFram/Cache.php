<?php
namespace OCFram;

class Cache 
{
	const CACHE_DIR = 'C:/wamp64/www/monsiteenpoo/tmp/cache';

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
		if(file_exists($file))
		{
			unlink($file);
		}
		
	}

	public function clear() 
	{

		$views_cache = self::CACHE_DIR.'/views';
		$datas_cache = self::CACHE_DIR.'/datas';

		$datas = glob($datas_cache.'/*');
		foreach($datas as $data)
		{
			unlink($data);
		}

		$views = glob($views_cache.'/*');
		foreach($views as $view)
		{
			unlink($view);
		}

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

