<?php

/********************************************/
/*      Class for database connection       */
/*      https://support.loopia.se/wiki/mysqli/      */
/********************************************/
class db_class
{
    private $connection;
	public $insert_id;
	public $error;
    
    function __construct($db_server=NULL, $db_database=NULL, $db_username=NULL, $db_password=NULL)
    {
        $this->connection = new mysqli(
          "p:".($db_server!=NULL ? $db_server : db_host), 
          ($db_username!=NULL ? $db_username : db_user), 
          ($db_password!=NULL ? $db_password : db_pass), 
          ($db_database!=NULL ? $db_database : db_name)
        );
		/* check connection */
		if ($this->connection->connect_errno) {
			printf("Connect failed: %s\n", $this->connection->connect_error);
			exit();
		}
        $this->connection->set_charset("utf8");
    }
    
    public function insert($query)
    { 
		$result=$this->connection->query($query);
		if($result)
        {
			$this->insert_id=$this->connection->insert_id;
            $this->error=NULL;
        }
		else
			$this->error=$query." : ".$this->connection->error;
		return $result;
	}
	
	public function insert_from_array($table, $values)
	{
		$updates=array();
		foreach($values as $key => $val)
		{
			if(!in_array($val, array("NOW()", "NULL", "TRUE", "FALSE")))
				$val="'".sql_safe($val)."'";
			else 
				$val=sql_safe($val);

			$updates[]='`'.sql_safe($key)."`=".$val;
		}
		$sql="INSERT INTO ".sql_safe($table)." SET ".implode(", ",$updates).";";

		return $this->insert($sql);
	}
	public function get_from_array($table, $values, $just_first=FALSE)
	{
		$requirements=array();
		foreach($values as $key => $val)
		{
			$requirements[]='`'.sql_safe($key)."`='".sql_safe($val)."'";
		}
		$sql="SELECT * FROM ".sql_safe($table)." WHERE ".implode(" AND ",$requirements).";";

		if($just_first)
			return $this->select_first($sql);
		
		return $this->select($sql);
	}
	public function delete_from_array($table, $values)
	{
		$requirements=array();
		foreach($values as $key => $val)
		{
			$requirements[]='`'.sql_safe($key)."`='".sql_safe($val)."'";
		}
		$sql="DELETE FROM ".sql_safe($table)." WHERE ".implode(" AND ",$requirements).";";

		return $this->query($sql);
	}

    public function query($query)
    { 
		$result=$this->connection->query($query);
		if($result)
			$this->error=NULL;
		else
			$this->error=$query." : ".$this->connection->error;
		return $result;
	}

    public function del($id, $table)
    { 
		$query="DELETE FROM `".$table."` WHERE id=".$id.";";
		$result= $this->query($query);
		return $result;
	}
    
    public function select($query)
    {
		$result=$this->query($query);
		
		if($result && !empty($result))
		{
			$return=array();
			while ($row = $result->fetch_assoc())
			{
				$return[]=$row;
			}
			$result->free();      
			return $return;
		}
		return NULL;
    }
	
	public function select_first($query)
	{
		$result=$this->select($query);
		if(!empty($result))
			return $result[0];
		return NULL;
	}
    
    public function get($column, $table, $id)
	{
		$result = $this->select("SELECT ".$column." FROM `".$table."` WHERE id=".$id);
		if(!empty($result) && isset($result[0][$column]))
		{
			$this->$column=$result[0][$column];
			return $result[0][$column];
		}
		echo "Function db_class->get failed. ".print_r($result,1);
		return NULL;
	}
    public function set($table, $column, $new_value, $id)
	{
		if(!in_array($new_value, array("NOW()", "NULL", "TRUE", "FALSE")))
			$new_value="'".sql_safe($new_value)."'";
		
		$result = $this->query("UPDATE `".sql_safe($table)."` SET `".sql_safe($column)."`=".$new_value." WHERE id=".sql_safe($id));
		return $result;
	}
	
	public function update_from_array($table, $values, $id)
	{
		$updates=array();
		foreach($values as $key => $val)
		{
			if(!in_array($val, array("NOW()", "NULL", "TRUE", "FALSE")))
				$updates[]='`'.sql_safe($key)."`='".sql_safe($val)."'";
			else
				$updates[]='`'.sql_safe($key)."`=".$val;
		}
		$sql="UPDATE ".sql_safe($table)." SET ".implode(", ",$updates)." WHERE id=".sql_safe($id).";";

		return $this->query($sql);
	}
    
    public function close()
    {
        $this->connection->close();
    }
    
    function __destruct() {
       $this->close();
   }
}

if(!function_exists("mysql_query"))
{
	function mysql_query($query)
	{
		$mysqli = new mysqli("p:".db_host, db_user, db_pass, db_name); //VIDDEWEBB_DB_NAME);
		return $mysqli->query($query);
	}
}

?>