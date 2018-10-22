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
			$this->db=static_db::getInstance();

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
		return $this->db->update_from_array($this->db_table, $values, $this->id);
	}
    
    public function select_from_array($values, $just_first=FALSE, $single_column=NULL)
    {
        return $this->db->select_from_array($this->db_table, $values, $just_first, $single_column);
    }
	
	public function update($column, $new_value)
	{
        $this->db->set($this->db_table, $column, $new_value, $this->id);
        
        // $table=PREFIX.sql_safe($this->db_table);
		// $sql="UPDATE `".$table."` SET 
				// ".sql_safe($column)."='".sql_safe($new_value)."'
			// WHERE id=".sql_safe($this->id).";";
		// $result=$this->db->query($sql);
		$this->reload();
	}
	
	protected function reload()
	{
		$sql="SELECT * FROM `".sql_safe($this->db_table)."` WHERE id=".sql_safe($this->id);
		$this->data=$this->db->select_first($sql);
	}
}

?>