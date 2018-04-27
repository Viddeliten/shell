<?php

class base_class
{
	public $id;
	public $data;
	
	protected $db;
	protected $db_table;
	
	function __construct($db_table_name, $id=NULL, $db_connection=NULL)
	{
		if($db_connection!=NULL)
			$this->db=$db_connection;
		else
			$this->db=new db_class();

		$this->db_table=$db_table_name;
		
		if($id!=NULL)
		{
			$this->id=$id;
			$this->reload();
		}
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

	public function insert_from_arr($values)
	{
		$vals=array();
		foreach($values as $key => $val)
		{
			$vals[]="`".sql_safe($key)."`='".sql_safe($val)."'";
		}
		$sql="INSERT INTO ".sql_safe($this->db_table)." SET ".implode(", ",$vals).";";
		// echo str_replace("\n","<br />",prestr($sql,"base_class->insert_from_arr"));
		$result = $this->db->insert($sql);
		// echo str_replace("\n","<br />",prestr($result,"base_class->insert_from_arr result"));
		if(!$result)
			return array("status" => FALSE, "error" => $this->db->error);
		$this->reload();
		return array("status" => TRUE, "insert_id" => $this->db->insert_id);
	}
	public function update_from_arr($values)
	{
		$vals=array();
		foreach($values as $key => $val)
		{
			$vals[]="`".sql_safe($key)."`='".sql_safe($val)."'";
		}
		$sql="UPDATE ".sql_safe($this->db_table)." SET ".implode(", ",$vals)."
			WHERE id=".sql_safe($this->id).";";
		echo str_replace("\n","<br />",prestr($sql,"base_class->update_from_arr"));
		$result = $this->db->query($sql);
		echo str_replace("\n","<br />",prestr($result,"base_class->update_from_arr result"));
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
	
	protected function reload()
	{
		$sql="SELECT * FROM `".sql_safe($this->db_table)."` WHERE id=".sql_safe($this->id);
		// preprint($sql, $this->db_table." reload");
		$this->data=$this->db->select_first($sql);
	}
}

?>