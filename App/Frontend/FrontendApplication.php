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

    //si on veut génerer l'index et que sa vue est en cache, on récupère le cache à ce moment là : 
    if($controller->action() == 'index' && file_exists(Cache::CACHE_DIR.'/views/Frontend_News_index'))
    {
      // j'ai mis la commande $controller->execute() en commentaire car on n'a pas besoin d'executer le controlleur dans ce cas là!
      // (et c'est spécifié dans l'énoncé...)
      //$controller->execute();
      $this->httpResponse->setPage($controller->page());
      $this->httpResponse->send();
      
    }
    else
    {
      //dans le cas général, on doit executer le controleur.
      $controller->execute();
      $this->httpResponse->setPage($controller->page());
      $this->httpResponse->send();

    }
    
  }
}