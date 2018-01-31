<?php
namespace App\Backend\Modules\News;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\News;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \FormBuilder\NewsFormBuilder;
use \OCFram\FormHandler;
use \OCFram\Cache;

class NewsController extends BackController
{

  public function executeIndex(HTTPRequest $request)
  {
    $this->page->addVar('title', 'Gestion des news');

    $manager = $this->managers->getManagerOf('News');

    $this->page->addVar('listeNews', $manager->getList());
    $this->page->addVar('nombreNews', $manager->count());
  }


  public function executeDelete(HTTPRequest $request)
  {
    //suppression du cache des ressources concernées : 
    $news_cache = Cache::CACHE_DIR.'/datas/news-'.$request->getData('id');
    $frontend_index_cache = Cache::CACHE_DIR.'/views/Frontend_News_index';
    
    $this->cache()->delete($news_cache);
    $this->cache()->delete($frontend_index_cache);

    //reprise du code après gestion du cache:
    $newsId = $request->getData('id');
    
    $this->managers->getManagerOf('News')->delete($newsId);
    $this->managers->getManagerOf('Comments')->deleteFromNews($newsId);

    $this->app->user()->setFlash('La news a bien été supprimée !');

    $this->app->httpResponse()->redirect('.');
  }
  

  public function executeInsert(HTTPRequest $request)
  {
    // on supprime du cache l'index obsolète : 
    $frontend_index_cache = Cache::CACHE_DIR.'/views/Frontend_News_index';
    $this->cache()->delete($frontend_index_cache);

    $this->processForm($request);

    $this->page->addVar('title', 'Ajout d\'une news');
  }

  public function executeUpdate(HTTPRequest $request)
  {
    // on supprime du cache les ressources concernées : la vue de l'index et la news:

    $news_cache = Cache::CACHE_DIR.'/datas/news-'.$request->getData('id');
    $frontend_index_cache = Cache::CACHE_DIR.'/views/Frontend_News_index';

    $this->cache()->delete($news_cache);
    $this->cache()->delete($frontend_index_cache);

    // le code reprend ici après la gestion du cache : 
    $this->processForm($request);

    $this->page->addVar('title', 'Modification d\'une news');
  }

  public function executeDeleteComment(HTTPRequest $request)
  {
    //suppression du cache de la ressource concernée
    $comment = $this->managers->getManagerOf('Comments')->get($request->getData('id'));
    $newsId = $comment->news();
    $comments_cache = Cache::CACHE_DIR.'/datas/comments-newsId='.$newsId;
    $this->cache()->delete($comments_cache);


    $this->managers->getManagerOf('Comments')->delete($request->getData('id'));
    
    $this->app->user()->setFlash('Le commentaire a bien été supprimé !');
    
    $this->app->httpResponse()->redirect('.');

  }

  public function executeUpdateComment(HTTPRequest $request)
  {

    $this->page->addVar('title', 'Modification d\'un commentaire');

    if ($request->method() == 'POST')
    {
      $comment = new Comment([
        'id' => $request->getData('id'),
        'auteur' => $request->postData('auteur'),
        'contenu' => $request->postData('contenu')
      ]);
    }
    else
    {
      $comment = $this->managers->getManagerOf('Comments')->get($request->getData('id'));
    }

    // suppression du cache des ressources concernées : 
    $newsId = $comment->news();
    $comments_cache = Cache::CACHE_DIR.'/datas/comments-newsId='.$newsId;
    $this->cache()->delete($comments_cache);

    $formBuilder = new CommentFormBuilder($comment);
    $formBuilder->build();

    $form = $formBuilder->form();

    $formHandler = new FormHandler($form, $this->managers->getManagerOf('Comments'), $request);

    if ($formHandler->process())
    {
      $this->app->user()->setFlash('Le commentaire a bien été modifié');

      $this->app->httpResponse()->redirect('/monsiteenpoo/Web/admin/');
    }

    $this->page->addVar('form', $form->createView());
  }

  public function processForm(HTTPRequest $request)
  {
    if ($request->method() == 'POST')
    {
      $news = new News([
        'auteur' => $request->postData('auteur'),
        'titre' => $request->postData('titre'),
        'contenu' => $request->postData('contenu')
      ]);

      if ($request->getExists('id'))
      {
        $news->setId($request->getData('id'));
      }
    }
    else
    {
      // L'identifiant de la news est transmis si on veut la modifier
      if ($request->getExists('id'))
      {
        $news = $this->managers->getManagerOf('News')->getUnique($request->getData('id'));
      }
      else
      {
        $news = new News;
      }
    }

    $formBuilder = new NewsFormBuilder($news);
    $formBuilder->build();

    $form = $formBuilder->form();

    $formHandler = new FormHandler($form, $this->managers->getManagerOf('News'), $request);

    if ($formHandler->process())
    {
      $this->app->user()->setFlash($news->isNew() ? 'La news a bien été ajoutée !' : 'La news a bien été modifiée !');
      
      $this->app->httpResponse()->redirect('/monsiteenpoo/Web/admin/');
    }

    $this->page->addVar('form', $form->createView());
  }

  public function executeDeconnexion()
  {
    session_destroy();
    $this->app->httpResponse()->redirect('/monsiteenpoo/Web');
  }
}