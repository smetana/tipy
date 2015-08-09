<?php

class TipyTestGroup extends TipyModel {

    protected $hasMany = [
        'userAndGroupRelations' => ['class' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'group_id']
    ];

    protected $hasManyThrough = [
        'users' => ['class' => 'User', 'through' => 'TipyTestUserAndGroupRelation', 'foreign_key' => 'group_id', 'through_key' => 'user_id']
    ];
}
