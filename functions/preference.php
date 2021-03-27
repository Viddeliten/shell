<?php

/**
 * for handling user preferences. We care about settings in this order:
 * 1. POST
 * 2. GET
 * 3. SESSION
 * 4. COOKIE
 * 5. Database saved settings
 *
 * Reason for why saved settings are least likely to be respected is because when user want to change their preferred method, it should be easy to do so, and previous settings should not "get in the way"
 */

class preference
{
    private $db;
    
    function  __construct($preference_handle, $user_id)
    {
        this->$db = new db_class();
    }
    
}