# tipy

Tiny PHP MVC framework.

# Requirements

* PHP 5.5+
* Apache 2.4
* MySQL 5+ (for now tipy supports MySQL database only)

# Installation

See [tipy-example](https://github.com/smetana/tipy-example) for installation and usage details.

# Convention over Configuration

tipy strictly follows "Convention over Configuration" paradigm therefore you will have
to maintain only one small config file for database connection and mailer.

# Models
app/models/User.php
```php
class User extends TipyModel {

    protected $hasMany = [
        'posts' => ['class' => 'BlogPost', 'dependent' => 'nullify']
    );

}
```
app/models/BlogPost.php
```php
class BlogPost extends TipyModel {

    protected $belongsTo = [
        'user' => ['class' => 'User', 'foreign_key' => 'user_id']
    ];

}
```

# Controllers
app/controllers/WelcomeController.php
```php
class WelcomeController extends TipyController {

    public function index() {
        $this->renderView('welcome');
    }
}
```

# Views
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

# Routing

For now tipy uses Apache's .htaccess for routing.
[tipy-example](https://github.com/smetana/tipy-example) has set of predefined routes which make easy to add new route to your application.
