<?php

define("DATA_TABLE_NAME","user_save_data");

function data_save_user_data($handle, $local_handle, $content)
{
	$user_id=login_get_user();
	$db=new db_class();
	
	$values=array(
		"user_id"		=> $user_id,
		"type"			=> $handle,
		"local_type"	=> $local_handle,
		"data"			=> json_encode($content)		
	);
	
	$db->insert_from_array(DATA_TABLE_NAME, $values);
}

function data_get_user_data($handle, $local_handle=NULL)
{
	$user_id=login_get_user();
	$db=new db_class();
	
	$values=array(
		"user_id"		=> $user_id,
		"type"			=> $handle
	);
	
	if($local_handle!==NULL)
		$values['local_type']=$local_handle;
	
	$data = $db->get_from_array(DATA_TABLE_NAME, $values);

	if(!empty($data))
	{
		foreach($data as $key => $val)
		{
			$data[$key]['data']=json_decode($val['data']);
		}
	}
	return $data;
}

?>