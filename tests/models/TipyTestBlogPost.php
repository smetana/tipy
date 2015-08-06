<?php

class TipyTestBlogPost extends TipyModel {

    protected $hasMany = array(
        'comments' => array('class' => 'TipyTestBlogComment', 'foreign_key' => 'blog_post_id', 'dependent' => 'delete')
    );

    protected $belongsTo = array(
        'user' => array('class' => 'TipyTestUser', 'foreign_key' => 'user_id')
    );

    public function validate() {
        if (!$this->title) throw new TipyValidationException('Title should not be blank!');
        if (!$this->userId) throw new TipyValidationException('Post should belongs to user');
    }

}
