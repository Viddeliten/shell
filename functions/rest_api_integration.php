<?php

class rest_api_integration
{
	private $oauth_name;
	private $base_uri;
	private $grant;
	
	function __construct($oauth_name, $fetch_access_token=FALSE)
	{

		$this->oauth_name=$oauth_name;
		$this->base_uri=$this->get_from_oauth_name($oauth_name, "base_uri");
		$this->set_grant($fetch_access_token);
	}
	
	public function get_from_oauth_name($oauth_name, $member)
	{
		$rest_apis=unserialize(REST_APIS);
		if(isset($rest_apis[$oauth_name]))
			return $rest_apis[$oauth_name][$member];
		return FALSE;
	}
	
	public function has_access_token()
	{
		if(isset($this->grant->access_token))
			return TRUE;
		return FALSE;
	}
	
	public function get_error_message()
	{
		return (isset($this->grant->message) ? $this->grant->message : _("Unexpected oauth response"));
	}
	public function has_support($oauth_name)
	{
		$rest_apis=unserialize(REST_APIS);
		if(isset($rest_apis[$oauth_name]))
			return TRUE;
		return FALSE;
	}
	
	public function get($parameters)
	{
		if(!$this->has_access_token())
			return FALSE;
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->base_uri."/".implode("/",$parameters));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$this->grant->access_token
		));
		
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close ($ch);
		
		return json_decode($server_output);		
	}

	public function _delete($parameters)
	{
		if(!$this->has_access_token())
			return FALSE;
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->base_uri."/".implode("/",$parameters));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer ".$this->grant->access_token
		));
		
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close ($ch);
		
		return json_decode($server_output);		
	}
	
	public function get_user_tokens($oauth_name, $logged_in_user_id)
	{
		$db=new db_class();
		$values=array(	"oauth_name" => $oauth_name,
						"user"	=> $logged_in_user_id
					);
		return $db->get_from_array(PREFIX."user_oauth_reff", $values, TRUE);
	}
	
	private function set_grant($fetch_access_token=FALSE)
	{
		if($fetch_access_token)
		{
			$db=new db_class();
			$this->grant=(object) $db->get_from_array(PREFIX."user_oauth_reff", array("user"	=> login_get_user(), "oauth_name" => $this->oauth_name), TRUE);
			return NULL;
		}
		// https://stackoverflow.com/questions/2138527/php-curl-http-post-sample-code 
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $this->get_from_oauth_name($this->oauth_name, "auth_uri"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
			http_build_query($this->get_from_oauth_name($this->oauth_name, "auth_parameters")));

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		
		curl_close ($ch);
		
		$this->grant = json_decode($server_output);
	}
	
	public function connect_user($identifying_id, $suggested_username, $logged_in_user_id=NULL)
	{
		$db=new db_class();
		
		// Check if this information is already in database
		$values=array(	"oauth_name" => $this->oauth_name,
						"identifying_id"	=> $identifying_id
					);
		$existing_token=$db->get_from_array(PREFIX."user_oauth_reff", $values, TRUE);

		$values['refresh_token']=$this->grant->refresh_token;
		$values['access_token']=$this->grant->access_token;

		if(!empty($existing_token))
		{
			// Update db with the new access- and refresh token.
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