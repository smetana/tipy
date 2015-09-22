<?php

class Profile extends TipyModel {

    protected $belongsTo = [
        'user' => ['class' => 'User']
    ];
}
