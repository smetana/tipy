<?php
class TipyTestUser extends TipyModel {

    protected $hasMany = [
        'posts' => ['class' => 'TipyTestBlogPost', 'foreign_key' => 'user_id', 'dependent' => 'delete'],
        'userAndGroupRelations' => ['class' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'user_id', 'dependent' => 'delete'],
        'friendRelations' => ['class' => 'TipyTestFriend', 'foreign_key' => 'person_id', 'dependent' => 'delete']
    ];

    protected $hasManyThrough = [
        'groups' => ['class' => 'TipyTestGroup', 'through' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'user_id', 'through_key' => 'group_id'],
        'friends' => ['class' => 'TipyTestUser', 'through' => 'TipyTestFriend', 'foreign_key' => 'person_id', 'through_key' => 'friend_id']
    ];

    protected $hasOne = [
        'profile' => ['class' => 'TipyTestProfile', 'foreign_key' => 'user_id', 'dependent' => 'nullify']
    ];

    public function validate() {
        if (!$this->login) {
            throw new TipyValidationException('Login should not be blank!');
        }
        if (!$this->password) {
            throw new TipyValidationException('Password should not be blank!');
        }
        if (!$this->email) {
            throw new TipyValidationException('Email should not be blank!');
        }
    }
}
