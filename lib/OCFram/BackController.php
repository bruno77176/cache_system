<?php
namespace OCFram;

use \OCFram\Cache;

abstract class BackController extends ApplicationComponent
{
  protected $action = '';
  protected $module = '';
  protected $page = null;
  protected $view = '';
  protected $managers = null;
  protected $cache;

  public function __construct(Application $app, $module, $action)
  {
    parent::__construct($app);

    $this->managers = new Managers('PDO', PDOFactory::getMysqlConnexion());
    $this->page = new Page($app);
    $this->cache = new Cache();

    $this->setModule($module);
    $this->setAction($action);
    $this->setView($action);
  }

  public function execute()
  {
    $method = 'execute'.ucfirst($this->action);

    if (!is_callable([$this, $method]))
    {
      throw new \RuntimeException('L\'action "'.$this->action.'" n\'est pas définie sur ce module');
    }

    $this->$method($this->app->httpRequest());
  }

  public function page()
  {
    return $this->page;
  }
  public function module(){ return $this->module; }
  public function action(){ return $this->action; }

  public function setModule($module)
  {
    if (!is_string($module) || empty($module))
    {
      throw new \InvalidArgumentException('Le module doit être une chaine de caractères valide');
    }

    $this->module = $module;
  }

  public function setAction($action)
  {
    if (!is_string($action) || empty($action))
    {
      throw new \InvalidArgumentException('L\'action doit être une chaine de caractères valide');
    }

    $this->action = $action;
  }

  public function setView($view)
  {
    if (!is_string($view) || empty($view))
    {
      throw new \InvalidArgumentException('La vue doit être une chaine de caractères valide');
    }

    $this->view = $view;

    // le contenu de la vue envoyé à l'objet Page sera issu du cache si celui ci est présent et pas expiré : 
    $cache_view = Cache::CACHE_DIR.'/views/'.$this->app->name().'_'.$this->module().'_'.$this->action;

    if(file_exists($cache_view) && !$this->cache()->isExpired($cache_view))
    {
      $this->page->setContentFile($cache_view);
    }
    else 
    {  
      // s'il est présent mais expiré, on le supprime : 
      if($this->cache()->isExpired($cache_view))
      {
        $this->cache()->delete($cache_view);
      }
      //on fait appel à la vue s'il n'y a pas de cache ou si celui-ci est expiré : 
      $this->page->setContentFile(__DIR__.'/../../App/'.$this->app->name().'/Modules/'.$this->module.'/Views/'.$this->view.'.php');
    }
    
  }

  public function cache()
  {
    return $this->cache;
  }
}
