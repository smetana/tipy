<?php

class TipyTestBlogComment extends TipyModel {

    protected $belongsTo = array(
        'post' => array('class' => 'TipyTestBlogPost', 'foreign_key' => 'blog_post_id')
    );

}

