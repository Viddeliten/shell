<?php

/**
 * for handling user preferences. We care about settings in this order:
 * 1. REQUEST (post/get)
 * 2. SESSION
 * 3. COOKIE
 * 4. Database saved settings
 *
 * Reason for why saved settings are least likely to be respected is because when user want to change their preferred method, it should be easy to do so, and previous settings should not "get in the way"
 */

/**
 * Class preference for all operations
 */
class preference
{
    private $db;
    private $preference_handle;
    private $db_table;
    private $user_id;
    
    function  __construct($preference_handle, $user_id = FALSE)
    {
        $this->db = new db_class();
        $this->preference_handle = $preference_handle;
        $this->user_id = $user_id;
        $this->db_table = PREFIX."user_preference";
    }
    
    /**
     * get the current preference, and save it everywhere
     */
    public function get($set_cookie = FALSE)
    {
        $value = $this->fetch();
        $this->set($value, $set_cookie);
        return $value;
    }
    
    /**
     * private function that returns the setting found. Only to be called from get, that will also make sure all underlying places has this returned value saved 
     */
    private function fetch()
    {
        // request data?
        if(isset($_REQUEST['preference_'.$this->preference_handle]))
        {
            return $_REQUEST['preference_'.$this->preference_handle];
        }
        
        // saved in session?
        if(isset($_SESSION[PREFIX.'preference_'.$this->preference_handle]))
        {
            return $_SESSION[PREFIX.'preference_'.$this->preference_handle];
        }
        
        // Saved in cookie?
        if(isset($_COOKIE[PREFIX.'preference_'.$this->preference_handle]))
        {
            return $_COOKIE[PREFIX.'preference_'.$this->preference_handle];
        }
        
        // Saved in db? Only applicable if there is a user id present
        if($this->user_id)
        {
            $result = $this->db->select_from_array($this->db_table, array("handle" => $this->preference_handle, "user_id" => $this->user_id));
			if($result->num_rows > 0 )
			{
				preprint($result, "preference in db HANDLE THIS!");
			}
        }
        
        return FALSE; // We didn't found any ;D
    }
    
    /**
     * Sets the preference in session, cookie if it's before rendering and db if there is a user present
     */
    public function set($value, $set_cookie = FALSE)
    {
        // Save in session
        $_SESSION['preference_'.$this->preference_handle]=$value;
        
        // Save in cookie if flag is true
        if($set_cookie)
            setcookie('preference_'.$this->preference_handle, $value, time()+3600*24*7); // This wo
        
        // Save in db if there is a user present
        if($this->user_id)
            $this->db->upsert_from_array($this->db_table, array("handle" => $this->preference_handle, "user_id" => $this->user_id, "value" => $value));
    }    

}

?>