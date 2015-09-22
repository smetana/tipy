<?php

class BlogComment extends TipyModel {

    protected $belongsTo = [
        // Test both syntax
        'user',
        'post' => ['class' => 'BlogPost']
    ];
}
