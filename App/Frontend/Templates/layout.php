<!DOCTYPE html>
<html>
  <head>
    <title>
      <?= isset($title) ? $title : 'Mon super site' ?>
    </title>
    
    <meta charset="utf-8" />
    
    <link rel="stylesheet" href="/monsiteenpoo/Web/css/Envision.css" type="text/css" />
  </head>
  
  <body>
    <div id="wrap">
      <header>
        <h1><a href="/monsiteenpoo/Web/">Mon super site</a></h1>
        <p>Comment ça, il n'y a presque rien ?</p>
      </header>
      
      <nav>
        <ul>
          <li><a href="/monsiteenpoo/Web/">Accueil</a></li>
          
          <?php if ($user->isAuthenticated()) { ?>
          <li><a href="/monsiteenpoo/Web/admin/deconnexion">Déconnexion</a></li>
          <li><a href="/monsiteenpoo/Web/admin/">Admin</a></li>
          <li><a href="/monsiteenpoo/Web/admin/news-insert.html">Ajouter une news</a></li>
          <?php }  ?>
        </ul>
      </nav>
      
      <div id="content-wrap">
        <section id="main">
          <?php if ($user->hasFlash()) echo '<p style="text-align: center;">', $user->getFlash(), '</p>'; ?>
          
          <?= $content ?>
        </section>
      </div>
    
      <footer></footer>
    </div>
  </body>
</html>