<?php

class BlogComment extends TipyModel {

    protected $belongsTo = [
        'post' => ['class' => 'BlogPost']
    ];
}
