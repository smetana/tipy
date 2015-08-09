<?php

class TipyTestBlogComment extends TipyModel {

    protected $belongsTo = [
        'post' => ['class' => 'TipyTestBlogPost', 'foreign_key' => 'blog_post_id']
    ];
}
