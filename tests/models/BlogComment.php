<?php

class BlogComment extends TipyModel {

    protected $belongsTo = [
        // Test both syntaxes
        'user',
        'post' => ['class' => 'BlogPost']
    ];
}
