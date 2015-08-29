# tipy

Tiny PHP MVC framework.

## Prerequisites

* PHP 5.5+
* Apache 2
* MySQL
* Git
* Composer

## Installation

Clone [tipy-project](https://github.com/smetana/tipy-project) to quickly bootstrap your tipy web app.
This is an application skeleton for a typical tipy project.

```shell
git clone https://github.com/smetana/tipy-project myproject
cd myproject
composer.phar install
```
You can also use [tipy-example](https://github.com/smetana/tipy-example) as a demo.

## Convention over Configuration

tipy follows "Convention over Configuration" paradigm therefore you have
to maintain only one small config file for database connection but you have
to follow tipy conventions in code.

## Routes

tipy uses Apache's .htaccess for routing.<br/>
Using combination of predefined rewrite rules and tipy conventions it is very
easy to define new routes.

/public/.htaccess
```apache
RewriteRule ^$        /blog [QSA,L]
# => BlogController::index()

RewriteRule ^/code$   /code/open_source [QSA,L]
# => CodeController::openSource()
```

## Models
app/models/BlogPost.php
```php
class BlogPost extends TipyModel {
    protected $hasMany = [
        'comments' => ['class' => 'Comment', 'dependent' => 'destroy']
    );
}
```
app/models/Comment.php
```php
class Comment extends TipyModel {
    protected $belongsTo = [
        'post' => ['class' => 'BlogPost', 'foreign_key' => 'blog_post_id']
    ];
}
```

## Controllers
app/controllers/BlogController.php

```php
class BlogController extends TipyController {

    public function article() {
        $blogPost = BlogPost::load($this->in('id'));
        $this->out('blogPost', $blogPost);
        $this->out('comments', $blogPost->comments);
        $this->renderView('blog/article');
    }

}
```

## Views
tipy views are plain php files. Simple and powerful. No heavy template systems. And all your application data is isolated from views.
You pass to view only you what to want to show.

app/controllers/BlogController.php
```php
class BlogController extends TipyController {

    public function article() {
        $post = BlogPost::load($this-in('id'));
        $this->out->set('title', $post->title);
        $this->out->set('message', $post->message);
        $this->renderView('blog/article');
    }
}
```
app/views/blog/article.php

```html
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ></title>
</head>
<body>
    <h1><?= $title ?></p>
    <p><?= $message ?></p>
</body>
</html>
```
