<?php
namespace OCFram;

class Cache 
{
	
	protected $expirationTime;

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
	
	public function expirationTime()
	{
		return $this->expirationTime;
	}

	public function setExpirationTime($expirationTime)
	{
		if(is_int($expirationTime))
		{
			$this->expirationTime = $expirationTime;
		}
		
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