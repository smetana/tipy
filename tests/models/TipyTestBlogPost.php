<?php

class TipyTestBlogPost extends TipyModel {

    protected $hasMany = [
        'comments' => ['class' => 'TipyTestBlogComment', 'foreign_key' => 'blog_post_id', 'dependent' => 'delete']
    ];

    protected $belongsTo = [
        'user' => ['class' => 'TipyTestUser', 'foreign_key' => 'user_id']
    ];

    public function validate() {
        if (!$this->title) {
            throw new TipyValidationException('Title should not be blank!');
        }
        if (!$this->userId) {
            throw new TipyValidationException('Post should belongs to user');
        }
    }
}
