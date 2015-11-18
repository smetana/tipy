<?php
class User extends TipyModel {

    protected $hasMany = [
        'posts' => ['class' => 'BlogPost', 'dependent' => 'delete'],
        'userAndGroupRelations' => ['dependent' => 'delete'],
        'friendRelations' => ['class' => 'Friend', 'foreign_key' => 'person_id', 'dependent' => 'delete']
    ];

    protected $hasManyThrough = [
        'groups' => ['through' => 'UserAndGroupRelation', 'through_key' => 'group_id'],
        'friends' => ['class' => 'User', 'through' => 'Friend', 'foreign_key' => 'person_id', 'through_key' => 'friend_id']
    ];

    protected $hasOne = ['profile'];

    protected $belongsTo = ['userStatus'];

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
