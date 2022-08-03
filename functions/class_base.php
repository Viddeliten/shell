<?php

class base_class
{
	public $id;
	public $data;
	
	protected $criteria;
	protected $db;
	protected $db_table;
	
	function __construct($db_table_name, $id=NULL, $db_connection=NULL, $criteria=NULL)
	{
		if($db_connection!=NULL)
			$this->db=$db_connection;
		else
			$this->db=new db_class();

		$this->db_table=$db_table_name;
		
		if($id!=NULL)
		{
			$this->id=$id;
		}
		$this->criteria=$criteria;
		$this->reload();
	}
	
	public function create($values)
	{
		$vals=array();
		foreach($values as $key => $val)
		{
			$vals[]=sql_safe($key)."='".sql_safe($val)."'";
		}
		$sql="INSERT INTO ".sql_safe($this->db_table)." SET ".implode(", ",$vals).";";
		$result = $this->db->insert($sql);
		$this->id=$this->db->insert_id;
		$this->reload();
		return $result;
	}

	public function insert_from_arr($values, $success_message=NULL, $fail_message=NULL)
	{
		$result = $this->db->insert_from_array($this->db_table, $values);
		// $vals=array();
		// foreach($values as $key => $val)
		// {
			// $vals[]="`".sql_safe($key)."`='".sql_safe($val)."'";
		// }
		// $sql="INSERT INTO ".sql_safe($this->db_table)." SET ".implode(", ",$vals).";";
		// echo str_replace("\n","<br />",prestr($sql,"base_class->insert_from_arr"));
		// $result = $this->db->insert($sql);
		// echo str_replace("\n","<br />",prestr($result,"base_class->insert_from_arr result"));
		if(!$result)
		{
			if($fail_message!=NULL)
				message_print_error($fail_message."<br />".$this->db->error."<br />".$sql);
			return array("status" => FALSE, "error" => $this->db->error, "query" => $sql);
		}
		if($success_message!=NULL)
			message_print_success($success_message);
		$this->id=$this->db->insert_id;
		$this->reload();
		return array("status" => TRUE, "insert_id" => $this->db->insert_id);
	}
	
	public function update_from_arr($values)
	{
		$result = $this->db->update_from_array($this->db_table, $values, $this->id);
		$this->reload();
		return $result;
	}
	
	public function update($column, $new_value)
	{
		$sql="UPDATE `".sql_safe($this->db_table)."` SET 
				".sql_safe($column)."='".sql_safe($new_value)."'
			WHERE id=".sql_safe($this->id).";";
		$result=$this->db->query($sql);
		$this->reload();
	}
	
	public function set_criteria($criteria=NULL)
	{
		$this->criteria=$criteria;
		$this->reload();
	}
	public function set_id($id=NULL)
	{
		$this->id=$id;
		$this->reload();
	}
	public function unset_id()
	{
		$this->set_id();
	}

	protected function reload()
	{
		$criteria=($this->criteria!=NULL ? $this->criteria : array());
		if($this->id!=NULL)
			$criteria['id']=$this->id;
			
		$this->data=$this->db->get_from_array($this->db_table, $criteria, ($this->id!=NULL ? TRUE : FALSE));
	}
	
	protected function save_uploaded_image($uploaded, $bildurl = NULL, $absolute_path_image = NULL, $absolute_path_thumb = NULL)
	{
        if($absolute_path_image == NULL || $absolute_path_thumb == NULL)
        {
            return false;
        }
        
		if(isset($uploaded['tmp_name']) && is_uploaded_file($uploaded['tmp_name']))
		{
			ini_set('post_max_size', 1024*1024*1024);
			ini_set('upload_max_filesize', 512*1024*1024);
			ini_set('memory_limit', 1024*1024*1024);
			return image::save_uploaded_file($uploaded, $absolute_path_image, $absolute_path_thumb);
		}
		else if($bildurl!=NULL)
		{
			//echo "Bild-url!!!";
			// image_save_url_to_file($bildurl, "desc", PREFIX."story", $id);					
			return image::save_url_to_file($bildurl, $absolute_path_image, $absolute_path_thumb);
		}
	}
	
	public function get_db_error()
	{
		return $this->db->error;
	}
}

?>