<?php
namespace App\Frontend;
 
use \OCFram\Application;
use \OCFram\Cache;
 
class FrontendApplication extends Application
{
  public function __construct()
  {
    parent::__construct();
 
    $this->name = 'Frontend';
  }
 
  public function run()
  {
    $controller = $this->getController();

    if(!file_exists(Cache::CACHE_DIR.'/views/Frontend_News_index'))
    {
      $controller->execute();
 
      $this->httpResponse->setPage($controller->page());
      $this->httpResponse->send();
    }
    else
    {
      //on fera quelque chose de différent ici...
      $controller->execute();
 
      $this->httpResponse->setPage($controller->page());
      $this->httpResponse->send();
    }
    
  }
}