<?php
class TipyTestUser extends TipyModel {

    protected $hasMany = array(
        'posts' => array('class' => 'TipyTestBlogPost', 'foreign_key' => 'user_id', 'dependent' => 'delete'),
        'userAndGroupRelations' => array('class' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'user_id', 'dependent' => 'delete'),
        'friendRelations' => array('class' => 'TipyTestFriend', 'foreign_key' => 'person_id', 'dependent' => 'delete')
    );

    protected $hasManyThrough = array(
        'groups' => array('class' => 'TipyTestGroup', 'through' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'user_id', 'through_key' => 'group_id'),
        'friends' => array('class' => 'TipyTestUser', 'through' => 'TipyTestFriend', 'foreign_key' => 'person_id', 'through_key' => 'friend_id')
    );

    protected $hasOne = array(
        'profile' => array('class' => 'TipyTestProfile', 'foreign_key' => 'user_id', 'dependent' => 'nullify')
    );

    public function validate() {
        if (!$this->login) throw new TipyValidationException('Login should not be blank!');
        if (!$this->password) throw new TipyValidationException('Password should not be blank!');
        if (!$this->email) throw new TipyValidationException('Email should not be blank!');
    }

}

