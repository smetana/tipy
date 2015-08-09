<?php

class TipyTestProfile extends TipyModel {

    protected $belongsTo = [
        'user' => ['class' => 'TipyTestUser', 'foreign_key' => 'user_id']
    ];
}
