# tipy

Tiny PHP MVC framework.

## Installation

Prerequisites:

* PHP 5.5+
* Apache 2 + mod_php5
* MySQL
* Git
* Composer

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

Tipy follows "Convention over Configuration" paradigm therefore you have
to maintain only one small config file for database connection

## API and Documentation

http://smetana.me/tipy-api - a reference to tipy classes with annotations.

## tipy is MVC

### Models
```php
// app/models/BlogPost.php

class BlogPost extends TipyModel {

    protected $hasMany = [
        'comments' => ['class' => 'Comment', 'dependent' => 'delete']
    );

}
```
### Controllers
```php
// app/controllers/BlogController.php

class BlogController extends TipyController {

    public function article() {
        $blogPost = BlogPost::load($this->in('id'));
        $this->out('blogPost', $blogPost);
        $this->renderView('blog/post');
    }

}
```

### Views
```html
<!-- app/views/blog/post.php -->

<!DOCTYPE html>
<html>
<head>
    <title><?= $blogPost->title ></title>
</head>
<body>
    <h1><?= $blogPost->title ?></p>
    <p><?= $blogPost->body ?></p>
</body>
</html>
```
## With Testing Framework

```php
class BlogPostTest extends TipyTestCase {

    public function testBlogPost() {
        $post = BlogPost::load(1);
        assertNotNull($post->title);
        assertEqual($post->id, 1);
    }

}
```

See http://smetana.me/tipy-api for more details..
