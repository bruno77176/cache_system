<?php
namespace App\Frontend\Modules\News;
 
use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \OCFram\FormHandler;
use \OCFram\Cache;
 
class NewsController extends BackController
{
  public function executeIndex(HTTPRequest $request)
  {

    $frontend_index_cache = Cache::CACHE_DIR.'/views/Frontend_News_index';

    if(!file_exists($frontend_index_cache))
    {
      $nombreNews = $this->app->config()->get('nombre_news');
      $nombreCaracteres = $this->app->config()->get('nombre_caracteres');
   
      // On ajoute une définition pour le titre.
      $this->page->addVar('title', 'Liste des '.$nombreNews.' dernières news');
   
      // On récupère le manager des news.
      $manager = $this->managers->getManagerOf('News');
   
      $listeNews = $manager->getList(0, $nombreNews);
      
      foreach ($listeNews as $news)
      {
        if (strlen($news->contenu()) > $nombreCaracteres)
        {
          $debut = substr($news->contenu(), 0, $nombreCaracteres);
          $debut = substr($debut, 0, strrpos($debut, ' ')) . '...';
   
          $news->setContenu($debut);
        }
      }
      $serialized_listeNews =serialize($listeNews);
      $this->cache()->add($frontend_index_cache, $serialized_listeNews);
    }

    $listeNews = unserialize($this->cache()->read($frontend_index_cache));   
    
    // On ajoute la variable $listeNews à la vue.
    $this->page->addVar('listeNews', $listeNews);
  }
 
  public function executeShow(HTTPRequest $request)
  {
    $news_cache = Cache::CACHE_DIR.'/datas/news-'.$request->getData('id');
    $comments_cache = Cache::CACHE_DIR.'/datas/comments-newsId='.$request->getData('id');

    if(!file_exists($news_cache) || !file_exists($comments_cache))
    {
      $news = $this->managers->getManagerOf('News')->getUnique($request->getData('id'));
      $serialized_news = serialize($news);
      $this->cache()->add($news_cache, $serialized_news);

      $comments = $this->managers->getManagerOf('Comments')->getListOf($news->id());
      $serialized_comments = serialize($comments);
      $this->cache()->add($comments_cache, $serialized_comments);
    }
    
    $news = unserialize($this->cache()->read($news_cache));
    $comments = unserialize($this->cache()->read($comments_cache));
 
    if (empty($news))
    {
      $this->app->httpResponse()->redirect404();
    }
 
    $this->page->addVar('title', $news->titre());
    $this->page->addVar('news', $news);
    $this->page->addVar('comments', $comments);
  }
 
  public function executeInsertComment(HTTPRequest $request)
  {
    $comments_cache = Cache::CACHE_DIR.'datas/comments-newsId='.$request->getData('news');
    
    $this->cache()->delete($comments_cache);
      // Si le formulaire a été envoyé.
    if ($request->method() == 'POST')
    {
      $comment = new Comment([
        'news' => $request->getData('news'),
        'auteur' => $request->postData('auteur'),
        'contenu' => $request->postData('contenu')
      ]);
    }
    else
    {
      $comment = new Comment;
    }
      
 
    $formBuilder = new CommentFormBuilder($comment);
    $formBuilder->build();
 
    $form = $formBuilder->form();
 
    $formHandler = new FormHandler($form, $this->managers->getManagerOf('Comments'), $request);
 
    if ($formHandler->process())
    {
      $this->app->user()->setFlash('Le commentaire a bien été ajouté, merci !');
 
      $this->app->httpResponse()->redirect('news-'.$request->getData('news').'.html');
    }
 
    $this->page->addVar('comment', $comment);
    $this->page->addVar('form', $form->createView());
    $this->page->addVar('title', 'Ajout d\'un commentaire');
  }
}