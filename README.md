# tipy

Tiny PHP MVC framework.

## Prerequisites

* PHP 5.5+
* Apache 2
* MySQL
* Git
* Composer

## Installation

Clone [tipy-project](https://github.com/smetana/tipy-project) to quickly bootstrap your tipy web app:

```shell
git clone https://github.com/smetana/tipy-project myproject
cd myproject
composer.phar install
```
Add virtual host to apache2

```apache
# /etc/apache2/sites-available/tipy-conf

<VirtualHost 127.0.0.1:80>
    ServerName localhost
    DocumentRoot /home/user/projects/tipy-project/public
    <Directory "/home/user/projects/tipy-project">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
You can also use [tipy-example](https://github.com/smetana/tipy-example) as a demo.

## Convention over Configuration

tipy follows "Convention over Configuration" paradigm therefore you have
to maintain only one small config file for database connection but you have
to follow tipy conventions in code.

## Routes

tipy uses Apache's .htaccess for routing.<br/>
Using combination of predefined rewrite rules and tipy conventions it is very
easy to define new routes:

```apache
# public/.htaccess

RewriteRule ^$        /blog [QSA,L]
# => BlogController::index()

RewriteRule ^/code$   /code/open_source [QSA,L]
# => CodeController::openSource()
```

## Models
```php
// app/models/BlogPost.php

class BlogPost extends TipyModel {
    protected $hasMany = [
        'comments' => ['class' => 'Comment', 'dependent' => 'destroy']
    );
}
```
```php
// app/models/Comment.php

class Comment extends TipyModel {
    protected $belongsTo = [
        'post' => ['class' => 'BlogPost', 'foreign_key' => 'blog_post_id']
    ];
}
```

## Controllers
```php
// app/controllers/BlogController.php

class BlogController extends TipyController {

    public function article() {
        $blogPost = BlogPost::load($this->in('id'));
        $this->out('blogPost', $blogPost);
        $this->renderView('blog/article');
    }

}
```

## Views
```html
<!-- app/views/blog/article.php -->

<!DOCTYPE html>
<html>
<head>
    <title><?= $blogPost->title ></title>
</head>
<body>
    <h1><?= $blogPost->title ?></p>
    <p><?= $blogPost->body ?></p>
    <? foreach($blogPost->comments as $comment) ?>
        <p><?= $comment->body ?></p>
    <? } ?>
</body>
</html>
```
