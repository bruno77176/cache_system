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
    
    // On ajoute la variable $listeNews à la vue.
    $this->page->addVar('listeNews', $listeNews);
  }
 
  public function executeShow(HTTPRequest $request)
  {
    //on définit les fichiers de cache et on vérifie s'ils sont expirés, auquel cas on les supprime : 
    $news_cache = Cache::CACHE_DIR.'/datas/news-'.$request->getData('id');
    $comments_cache = Cache::CACHE_DIR.'/datas/comments-newsId='.$request->getData('id');

    if($this->cache()->isExpired($news_cache))
    {
      $this->cache()->delete($news_cache);
    }

    if($this->cache()->isExpired($comments_cache))
    {
      $this->cache()->delete($comments_cache);
    }

    //si les fichiers cache n'existent pas il faut les créer, leur assigner un timestamp d'expiration et le contenu sérialisé issu de la requête : 
    if(!file_exists($news_cache) || !file_exists($comments_cache))
    {
      $this->cache()->setTimestamp($news_cache);
      $this->cache()->setTimestamp($comments_cache);

      $news = $this->managers->getManagerOf('News')->getUnique($request->getData('id'));

      $serialized_news = base64_encode(serialize($news));
      $this->cache()->add($news_cache, $serialized_news);

      $comments = $this->managers->getManagerOf('Comments')->getListOf($news->id());
      
      $serialized_comments = base64_encode(serialize($comments));
      $this->cache()->add($comments_cache, $serialized_comments);      
    }
    
    //j'ai utilisé ces méthodes pour désérialiser le contenu du cache, hors timestamp en 1ère ligne : 
    $fs = fopen($news_cache, 'r');
    $timestamp = fgets($fs);
    $news = unserialize(base64_decode(fgets($fs)));
    fclose($fs);

    $fs = fopen($comments_cache, 'r');
    $timestamp = fgets($fs);
    $comments = unserialize(base64_decode(fgets($fs)));
    fclose($fs);
    
 
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

    // suppression du cache de la ressource concernée : 
    $comments_cache = Cache::CACHE_DIR.'/datas/comments-newsId='.$request->getData('news');
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