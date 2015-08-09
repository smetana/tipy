<?php

class TipyTestProfile extends TipyModel {

    protected $belongsTo = array(
        'user' => array('class' => 'TipyTestUser', 'foreign_key' => 'user_id')
    );
}
