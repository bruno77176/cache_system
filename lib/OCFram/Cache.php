<?php
namespace OCFram;

class Cache 
{

	const EXPIRATION = 300; //en secondes.

	const CACHE_DIR = 'C:/wamp64/www/monsiteenpoo/tmp/cache';

	public function read($file)
	{
		return file_get_contents($file);
	}

	public function add($file, $content)
	{
		file_put_contents($file, $content, FILE_APPEND);
	}

	public function delete($file)
	{
		if(file_exists($file))
		{
			unlink($file);
		}
		
	}

	public function isExpired($file)
	{
	 	if(file_exists($file))
	 	{
	 		$lines = file($file);
	 		$timestamp = (int)$lines[0];

	 		if (time()>=$timestamp)
	 		{
	 			return true;
	 		}
	 	}
	}

	public function setTimestamp($file)
	{
		$fs = fopen($file, 'w');
		$timestamp = time()+self::EXPIRATION;
		fwrite($fs, $timestamp.PHP_EOL);
		fclose($fs);
	}

}

/* Cette fonction m'était utile pour débugger...
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
	*/