<?php
namespace OCFram;
use \OCFram\Cache;

class Page extends ApplicationComponent
{
  protected $contentFile;
  public $vars = [];

  public function addVar($var, $value)
  {
    if (!is_string($var) || is_numeric($var) || empty($var))
    {
      throw new \InvalidArgumentException('Le nom de la variable doit être une chaine de caractères non nulle');
    }

    $this->vars[$var] = $value;
  }

  public function getGeneratedPage()
  {
    if (!file_exists($this->contentFile))
    {
      throw new \RuntimeException('La vue spécifiée n\'existe pas');
    }

    $user = $this->app->user();

    extract($this->vars);

    ob_start();
      require $this->contentFile;
    $content = ob_get_clean();

    // comme spécifié dans l'énoncé, je ne vais mettre en cache que la vue de l'index : 
    if($this->app->name() == 'Frontend' && $this->app->getController()->action() == 'index')
    {
      $cache = $this->app->getController()->cache();

      $cache_view = Cache::CACHE_DIR.'/views/'.$this->app->name().'_'.$this->app->getController()->module().'_'.$this->app->getController()->action();
    
      //si le cache existe alors le contenu de la variable $content renvoyé lors de l'initialisation du controleur est le contenu du fichier cache, il faut donc lui retirer le timestamp pour pouvoir l'ajouter à la page : 
      if(file_exists($cache_view))
      {
        $content = substr($content, 10);
      }
      // si le cache n'existe pas, il faut le créer, lui ajouter son timestamp d'expiration et le contenu de la vue : 
      else
      {
        $cache->setTimestamp($cache_view);
        $cache->add($cache_view, $content);
      }
    }
    
    ob_start();
      require __DIR__.'/../../App/'.$this->app->name().'/Templates/layout.php';
    return ob_get_clean();
    
    
  }

  public function setContentFile($contentFile)
  {
    if (!is_string($contentFile) || empty($contentFile))
    {
      throw new \InvalidArgumentException('La vue spécifiée est invalide');
    }

    $this->contentFile = $contentFile;
  }

}

// on vérifie si l'index est en cache et si le cache est expirée, s'il l'est on supprime le fichier cache : 
    /*
    $frontend_index_cache = Cache::CACHE_DIR.'/views/Frontend_News_index';

    if($this->cache()->isExpired($frontend_index_cache))
    {
      $this->cache()->delete($frontend_index_cache);
    }*/
    

   // if(!file_exists($frontend_index_cache))
   // {
      //s'il n'y a pas de fichier en cache, on en créé un et on définit le timestamp d'expiration : 

      //$this->cache()->setTimestamp($frontend_index_cache);

    
    //}
    //on récupère l'index du cache en prenant soin de ne pas récupérer la première ligne occupée par le timestamp : 
    //$lines = file($frontend_index_cache);
    //$listeNews = unserialize($lines[1]);