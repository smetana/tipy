<?php

class TipyTestGroup extends TipyModel {

    protected $hasMany = array(
        'userAndGroupRelations' => array('class' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'group_id')
    );

    protected $hasManyThrough = array(
        'users' => array('class' => 'User', 'through' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'group_id', 'through_key' => 'user_id')
    );
}
