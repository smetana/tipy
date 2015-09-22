<?php

class Group extends TipyModel {

    protected $hasMany = [
        'userAndGroupRelations' => ['class' => 'UserAndGroupRelation']
    ];

    protected $hasManyThrough = [
        'users' => ['class' => 'User', 'through' => 'UserAndGroupRelation', 'foreign_key' => 'group_id', 'through_key' => 'user_id']
    ];
}
