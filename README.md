# tipy

Tiny PHP MVC + xUnit framework

## Installation

Prerequisites:

* PHP 5.5+
* Apache 2 + mod_php5
* MySQL
* Git
* Composer

Clone [tipy-project](https://github.com/smetana/tipy-project)

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

Tipy follows "Convention over Configuration" software design paradigm so there is
only one small config file for database connection.

## API and Documentation

http://smetana.me/tipy-api - a reference to tipy classes with annotations.

## Models
```php
// app/models/BlogPost.php

class BlogPost extends TipyModel {
    protected $hasMany = ['comments'];
}

// app/models/Comment.php

class Comment extends TipyModel {
    protected $belongsTo = [
        'post' => ['class' => 'BlogPost', 'dependent' => 'delete']
    ];
}

```
## Controllers
```php
// app/controllers/BlogController.php

class BlogController extends TipyController {

    public function post() {
        $post = BlogPost::load($this->in('id'));
        $this->out('post', $post);
    }

}
```

## Views
```html
<!-- app/views/Blog/post.php -->

<!DOCTYPE html>
<html>
<head>
    <title><?= $post->title ></title>
</head>
<body>
    <h1><?= $post->title ?></p>
    <p><?= $post->body ?></p>
</body>
</html>
```
## Tests

```php
class BlogPostTest extends TipyTestCase {

    public function testBlogPost() {
        $post = BlogPost::load(1);
        assertNotNull($post->title);
        assertEqual($post->id, 1);
    }

}
```

See http://smetana.me/tipy-api for more details.
