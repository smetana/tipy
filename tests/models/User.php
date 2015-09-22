<?php
class User extends TipyModel {

    protected $hasMany = [
        'posts' => ['class' => 'BlogPost', 'dependent' => 'delete'],
        'userAndGroupRelations' => ['class' => 'UserAndGroupRelation', 'dependent' => 'delete'],
        'friendRelations' => ['class' => 'Friend', 'foreign_key' => 'person_id', 'dependent' => 'delete']
    ];

    protected $hasManyThrough = [
        'groups' => ['class' => 'Group', 'through' => 'UserAndGroupRelation', 'through_key' => 'group_id'],
        'friends' => ['class' => 'User', 'through' => 'Friend', 'foreign_key' => 'person_id', 'through_key' => 'friend_id']
    ];

    protected $hasOne = [
        'profile' => ['class' => 'Profile', 'dependent' => 'nullify']
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
