<?php

/**
 * This file contains the class handling a basic data set in shell table "item"
 **/

class item extends base_class
{
	private $type;
	
	function __construct($type="shell_example", $create_new=FALSE, $id=NULL, $db_connection=NULL)
	{
		$this->type=$type;
		parent::__construct(PREFIX."item", $id, $db_connection, array("type" => $type));
		
		if($create_new)
		{
			$this->create();
		}
	}

	/**
	 * creates a new item. 
	 * @parameter values array desired values
	**/
	public function create($values=NULL, $success_message=NULL, $fail_message=NULL)
	{
		$user_id=login_get_user();
		
		if($user_id==NULL)
		{
			message_add_error(_("User id missing for item creation"));
			return FALSE;
		}
		
		if($success_message==NULL)
			$success_message=_("Item successfully created");
		if($fail_message==NULL)
			$fail_message=_("Item could not be created");

		$vals=array();
		if($values!=NULL)
			$vals=$values;

		$vals['type'] = $this->type;
		$vals['user_id'] = $user_id;

		$this->insert_from_arr($vals, $success_message, $fail_message);
	}
	
	public function set_criteria($criteria)
	{
		$criteria['type']=$this->type;
		parent::set_criteria($criteria);
	}
	
	public function set_name($name=NULL)
	{
		if($this->id==NULL)
			return FALSE;
		$this->update_from_arr(array("name" => $name));
	}
	public function set_description($description=NULL)
	{
		if($this->id==NULL)
			return FALSE;
		$this->update_from_arr(array("description" => $description));
	}
	public function set_public()
	{
		if($this->id==NULL)
			return FALSE;
		$this->update_from_arr(array("public" => true));
	}
	public function set_private()
	{
		if($this->id==NULL)
			return FALSE;
		$this->update_from_arr(array("public" => false));
	}
	
	public function del($id=NULL)
	{
		if($id!=NULL)
			$this->set_id($id);
		
		if($this->id==NULL)
			return FALSE;
		
		$this->db->del($this->id, $this->table);
	}
	
	public function load_users($user_id)
	{
		$criteria=array("user_id" => $user_id);
		$this->set_criteria($criteria);
	}
	public function load_public()
	{
		$criteria=array("public" => true);
		$this->set_criteria($criteria);
	}
	
	public function html_form_add($type)
	{
		$input=array();
		
		$input[]=html_form_input("type_hidden", "", "hidden", "type", $type);
		
		$input_id="name_text";
		$label=_("Name");
		$type="text";
		$name="name"; 
		$value="";
		$placeholder=_("Name");
		$input[]=html_form_input($input_id, $label, $type, $name, $value, $placeholder); //, $input_class=NULL, $helptext=NULL, $group_class=NULL, $onchange=NULL, $required=FALSE);
		
		$input[]=html_form_textarea("description_textarea", _("Description"), "description", "", _("Description"));
		
		$input[]=html_form_button("item_create", _("Create"), "success");
		return html_form("post", $input); //, $multipart=FALSE, $all_inline=FALSE);
	}
	
	public function receive()
	{
		if(isset($_POST['item_create']))
		{
			$created = new item($_POST['type'], TRUE);
			$created->set_name($_POST['name']);
			$created->set_description($_POST['description']);
		}
	}
}

?>