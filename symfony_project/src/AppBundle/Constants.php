<?php

namespace AppBundle;

class Constants
{
    /* Roles */
    const JUDGES_ROLE = 'Judges';
    const TAKES_ROLE = 'Takes';
    const TEACHES_ROLE = 'Teaches';
    public static $ROLE_NAMES = [
        Constants::JUDGES_ROLE,
        Constants::TAKES_ROLE,
        Constants::TEACHES_ROLE
    ];
}

?>