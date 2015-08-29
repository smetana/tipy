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

```apache
#/public/.htaccess

RewriteRule ^$             /welcome [QSA,L]           # WelcomeController::index()
RewriteRule ^open_source$  /code/open_source [QSA,L]  # CodeController::openSource()
```

## Models
```php
// app/models/User.php
class User extends TipyModel {

    protected $hasMany = [
        'posts' => ['class' => 'BlogPost', 'dependent' => 'nullify']
    );

}

// app/models/BlogPost.php
class BlogPost extends TipyModel {

    protected $belongsTo = [
        'user' => ['class' => 'User', 'foreign_key' => 'user_id']
    ];

}
```

## Controllers
app/controllers/WelcomeController.php
```php
class WelcomeController extends TipyController {

    public function index() {
        $this->renderView('welcome');
    }
}
```

## Views
tipy views are plain php files. Simple and powerful. No heavy template systems. And all your application data is isolated from views.
You pass to view only you what to want to show.

app/controllers/MyController.php
```php
class MyController extends TipyController {

    public function myAction() {
        $this->out->set('myVar1', 'Hello World!');
        $this->out->set('myVar2', 'Welcome to Tipy!');
        $this->renderView('myView');
    }
}
```
app/views/myView.php

```html
<!DOCTYPE html>
<html>
<head>
    <title><?= $myVar1 ></title>
</head>
<body>
    <p><?= $myvar2 ?></p>
</body>
</html>
```
