<?php

class rest_api_integration
{
	private $base_uri;
	private $token;
	
	function __construct($base_uri, $token)
	{
		$this->base_uri=$base_uri;
		$this->token=$token;
	}
	
	public function get($parameters)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->base_uri."/".implode("/",$parameters));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$this->token
		));
		
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close ($ch);
		
		return json_decode($server_output);		
	}
	
	public function connect_user($oauth_name, $access_token, $refresh_token, $identifying_id, $suggested_username, $logged_in_user_id=NULL)
	{
		$db=new db_class();
		
		// Check if this information is already in database
		$values=array(	"oauth_name" => $oauth_name,
						"identifying_id"	=> $identifying_id
					);
		$existing_token=$db->get_from_array(PREFIX."user_oauth_reff", $values, TRUE);
		if(!empty($existing_token))
		{
			// Update db with the new access- and refresh token.
			$values['refresh_token']=$refresh_token;
			$values['access_token']=$access_token;
			$db->update_from_array(PREFIX."user_oauth_reff", $values, $existing_token['id']);
			
			// return the user id
			return $existing_token['user'];
		}

		$db->start_transaction();
		
        if(login_check())
        {
            $user_id=login_get_user();
        }
        else
        {
            // If there is no logged in user, create one!
            $user_id=user_insert($suggested_username, $oauth_name.$access_token."@".$suggested_username);
            if(!$user_id)
            {
                add_error(_("User insert failed"));
                $db->rollback();
                return FALSE;
            }
        }
		
		// Insert this token into reff table
		$values['user']=$user_id;
		$values['refresh_token']=$refresh_token;
		$values['access_token']=$access_token;

		if($db->insert_from_array(PREFIX."user_oauth_reff", $values))
		{
			message_add_success_message(sprintf(_("%s connection created"), ucfirst($oauth_name)));
			$db->commit();
			return $user_id;
		}
		else
			add_error(sprintf(_("%s connection failed"), ucfirst($oauth_name)));
		
		$db->rollback();
		return FALSE;
	}

}

?>