<?php

class BlogPost extends TipyModel {

    protected $hasMany = [
        'comments' => ['class' => 'BlogComment', 'dependent' => 'delete']
    ];

    protected $belongsTo = ['user'];

    public function validate() {
        if (!$this->title) {
            throw new TipyValidationException('Title should not be blank!');
        }
        if (!$this->userId) {
            throw new TipyValidationException('Post should belongs to user');
        }
    }
}
