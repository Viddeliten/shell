<?php

/********************************************/
/*      Class for database connection       */
/*      https://support.loopia.se/wiki/mysqli/      */
/********************************************/
class static_db extends db_class
{
    private static $instance ;

    public function __construct(){
      if (self::$instance){
        exit("Instance on static_db already exists.") ;
      }
      parent::__construct();
    }

    public static function getInstance(){
      if (!self::$instance){
        self::$instance = new static_db();
      }
      return self::$instance ;
    }
    
    function __destruct() {
       parent::__destruct();
   }
}

class db_class
{
    private $connection;
	public $insert_id;
	public $error;
    public $affected_rows;
    
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
        $table=sql_safe(PREFIX.$table);
		$updates=array();
        $values=$this->prepare_array_for_query($values, false);
		foreach($values as $key => $val)
		{
			$updates[]='`'.sql_safe($key)."`".$val;
		}
		$sql="INSERT INTO ".$table." SET ".implode(", ",$updates).";";
		return $this->insert($sql);
	}
    
    private function prepare_array_for_query($array, $change_to_is=true)
    {
        foreach($array as $key => $val)
		{
            $array[$key]=$this->prepare_value_for_query($val, $change_to_is);
        }

        return $array;
    }
    
    private function prepare_value_for_query($value, $change_to_is=true)
    {
        if($value===NULL)
            $value="NULL";
        if($value===TRUE)
            $value="TRUE";
        if($value===FALSE)
            $value="FALSE";
        
        $special_values=array("NOW()", "NOT NULL", "NULL", "TRUE", "FALSE");
        for($i=1; $i<=6; $i++)
        {
            $special_values[]="NOW(".$i.")";
        }

        if(!in_array($value, $special_values))
            $value="='".sql_safe($value)."'";
        else if(in_array($value, array("NOT NULL", "NULL")) && $change_to_is)
            $value=" IS ".$value;
        else 
            $value="=".$value;
        return $value;
    }
    
	public function get_all($table)
    {
        $table=sql_safe(PREFIX.$table);
        $result=$this->select("SELECT * FROM ".$table);
        return $result;
    }
	public function get_from_array($table, $values, $just_first=FALSE, $single_column=NULL)
	{
        $table="`".sql_safe(PREFIX.$table)."`";
        
        if($single_column!==NULL)
            $column="`".sql_safe($single_column)."`";
        else
            $column="*";
        
		$requirements=array();
        $values=$this->prepare_array_for_query($values);
		foreach($values as $key => $val)
		{
			$requirements[]='`'.sql_safe($key)."`".$val."";
		}
		$sql="SELECT ".$column." FROM ".$table." WHERE ".implode(" AND ",$requirements).";";

		if($just_first)
        {
			$return=$this->select_first($sql);
            if($single_column!==NULL)
                return $return[$single_column];
            return $return;
        }
		else
        {
            $result=$this->select($sql);
            if($single_column!==NULL)
            {
                $return=array();
                foreach($result as $r)
                {
                    $return[]=$r[$single_column];
                }
                return $return;
            }
            return $result;    
        }
	}
	public function delete_from_array($table, $values)
	{
        $table=sql_safe(PREFIX.$table);
		$requirements=array();
        $values=$this->prepare_array_for_query($values);
		foreach($values as $key => $val)
		{
			$requirements[]='`'.sql_safe($key)."`".$val."";
		}
		$sql="DELETE FROM ".$table." WHERE ".implode(" AND ",$requirements).";";

		return $this->query($sql);
	}

    public function query($query)
    { 
		$result=$this->connection->query($query);
		if($result)
        {
			$this->error=NULL;
            $this->affected_rows=$this->connection->affected_rows;
            if($this->connection->insert_id)
                $this->insert_id=$this->connection->insert_id;
        }
		else
			$this->error=$query." : ".$this->connection->error;
		return $result;
	}

    public function del($id, $table)
    { 
        $table=sql_safe(PREFIX.$table);
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
        $table=sql_safe(PREFIX.$table);
        if($column===NULL)
        {
            $sql="SELECT * FROM `".$table."` WHERE id=".$id;
            return $this->select_first($sql);
        }
        
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
        $table=sql_safe(PREFIX.$table);
		$new_value=$this->prepare_value_for_query($new_value, false);
		
        $sql="UPDATE `".$table."` SET `".sql_safe($column)."`".$new_value." WHERE id=".sql_safe($id);
		$result = $this->query($sql);
		return $result;
	}
	
	public function update_from_array($table, $values, $id)
	{
        $table=sql_safe(PREFIX.$table);
		$updates=array();
        $values=$this->prepare_array_for_query($values);
		foreach($values as $key => $val)
		{
            $updates[]='`'.sql_safe($key)."`".$val;
		}
		$sql="UPDATE ".$table." SET ".implode(", ",$updates)." WHERE id=".sql_safe($id).";";

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
        $connection = static_db::getInstance();
		return $connection->query($query);
	}
    
    function mysql_error()
    {
        $connection = static_db::getInstance();
		return $connection->error;
    }
    
    function mysql_fetch_array($result)
    {
        $assoc=$result->fetch_assoc();
        $return=array();
        $i=0;
        if(!empty($assoc))
        {
            foreach($assoc as $key => $val)
            {
                $assoc[$i]=$val;
                $i++;
            }
        }
        return $assoc;
    }
    
    function mysql_fetch_assoc($result)
    {
        return $result->fetch_assoc();
    }
    
    function mysql_affected_rows()
    {
        $connection = static_db::getInstance();
		return $connection->affected_rows;
    }
    
    function mysql_insert_id()
    {
        $connection = static_db::getInstance();
		return $connection->insert_id;
    }
    
    function mysql_close($connection)
    {
        // ignore
    }
}

?>