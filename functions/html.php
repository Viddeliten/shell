<?php
/*	This file contains functions for printing various html elements according to style guides for bootstrap.
	The thought behind having this file is to only have to change html creating in one place when bootstrap updates	
	ALL the functions just returns html in strings so they can be echoed or used in other ways. */
	
function html_tag($tag_type, $text, $class=NULL, $get_link_titles=false, $div_id=NULL, $html_format_text=TRUE)
{
	$the_text=$text;
	return html_tag_text_ref($tag_type, $the_text, $class, $get_link_titles, $div_id, $html_format_text);
}

function html_img($source)
{
	return '<img src="'.$source.'" />';
}

function html_tag_text_ref($tag_type, &$text, $class=NULL, $get_link_titles=false, $div_id=NULL, $html_format_text=TRUE)
{
    if($html_format_text)
    {
        $text=str_replace("\n", "<br />", $text);
        $text=str_ireplace("\r","<br />",$text);
        $text=str_ireplace("<br /><br />","</p><p>",$text);
        //Look for urls
        string_replace_urls_with_links($text, $get_link_titles);
        
        //Break long words
        string_break_long_words($text);
    }

	return '<'.$tag_type.($class==NULL ? "":' class="'.$class.'"').($div_id==NULL ? "":' id="'.$div_id.'"').'>'.$text.'</'.$tag_type.'>';
}

function html_link($url, $text, $class=NULL)
{
	$the_text=str_ireplace("\n","<br />",$text);
	$the_text=str_ireplace("<br /><br />","</p></p>",$the_text);
	return '<a href="'.$url.'"'.($class==NULL ? "":' class="'.$class.'"').'>'.$the_text.'</a>';
}

function html_card($card_link="", $card_link_text="Go somewhere", $card_title="", $card_text="", $img_source=NULL, $image_alt="Image")
{
	if($card_link!="" && $card_link_text!="")
		$card_text.='<a href="'.$card_link.'" class="btn btn-primary">'.$card_link_text.'</a>';
	if($card_link!="" && $card_link_text=="")
	{
		$card_title=html_link($card_link, $card_title);
		$card_image_link=$card_link;
	}
	
	$card_parts=array( array(	"type" =>"img",
								"content" => array( "src" => $img_source,
													"alt" => $image_alt,
													"link" => (isset($card_image_link) ? $card_image_link : NULL)
												)
							),
						array(	"type" => "body",
								"content"	=>	array(	array(	"type"	 => "title",
																"content"	=> $card_title),
														array(	"type"	 => "text",
																"content" => $card_text)
										)
							)
					);
	return html_card_from_array($card_parts);
}

function html_card_from_array_parts($array)
{
    $content="";
    foreach($array as $part)
    {
		if(!isset($part['content']) && is_array($part))
		{
			foreach($part as $p)
			{
				if(is_array($p))
					$content.=html_card_from_array_parts($p);
			}
			return $content;
		}
		
		if(!isset($part['content']))
			return $content;
		
		if(!isset($part['type']))
			$part['type']="text";
        
		if(is_array($part['content']) && strcmp($part['type'],"img"))
            $part['content']=html_card_from_array_parts($part['content']);
        
		if(!isset($part['class']))
			$part['class']="";
		
        switch ($part['type'])
        {
            case "title":
                 $content.=html_tag("h5",$part['content'],"card-title ".$part['class'], FALSE, NULL, FALSE);
                break;
            case "img":
				$image='<img src="'.$part['content']['src'].'" alt="'.$part['content']['alt'].'" />';
				if(isset($part['content']['link']) && $part['content']['link']!=NULL)
					$image=html_link($part['content']['link'], $image);
                 $content.=html_tag("span",$image,"card-img-top ".$part['class'], FALSE, NULL, FALSE);
                break;
            case "text":
                 $content.=html_tag("p",$part['content'],"card-text ".$part['class'], FALSE, NULL, TRUE);
                break;
            case "list":
                 $content.=html_tag("ul",$part['content'],"list-group list-group-flush ".$part['class'], FALSE, NULL, FALSE);;
                break;
            default:
                $content.=html_tag("div",$part['content'], "card-".$part['type']." ".$part['class'], FALSE, NULL, FALSE);
        }
    }
    
    return $content;
}

function html_card_from_array($array, $div_class="", $div_id=NULL)
{
    $content=html_tag("div",html_card_from_array_parts($array),"card ".$div_class, FALSE, $div_id, FALSE);
   
    return $content;
    
    /* Example card:
    '<div class="card">
    <img class="card-img-top" src=".../100px180/?text=Image cap" alt="Card image cap">
          <div class="card-header">
            Featured
          </div>
          <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
      <ul class="list-group list-group-flush">
    <li class="list-group-item">Cras justo odio</li>
    <li class="list-group-item">Dapibus ac facilisis in</li>
    <li class="list-group-item">Vestibulum at eros</li>
  </ul>
  <div class="card-body">
    <a href="#" class="card-link">Card link</a>
    <a href="#" class="card-link">Another link</a>
  </div>
    </div>';
    */
}

function html_link_register($text, $class=NULL)
{
	return html_link(SITE_URL."/?reg", $text, $class);
}

function html_list($items, $list_class=NULL, $list_item_class=NULL, $list_type="ol")
{
    if(empty($items))
        return NULL;
    
    $list=array();
    foreach($items as $item)
        $list[]=html_tag("li",$item, $list_item_class);
        
    return html_tag($list_type, implode($list), $list_class);
}
	
// Returns rows of elements.
// All elements in first layers will be one row each
// Extra row is created if there is too many elements in one row
function html_rows($min_columns, $max_columns, $elements, $element_class=NULL, $row_class=NULL)
{
	$return="";
	if(!empty($elements))
	{
		foreach($elements as $key => $e)
		{
			if(!is_array($e))
				$element=array($e);
			else
				$element=$e;
			
			$return.=html_row($min_columns, $max_columns, $element, $element_class, $row_class);
		}
	}
	return $return;
}

function html_row_uneven($lg_sizes, $elements, $element_class=NULL, $row_class=NULL)
{
	$return='<div class="row'.($row_class==NULL ? "":" ".$row_class).'">';
	foreach($lg_sizes as $key => $val)
	{
		$col_lg_size=$val;
		if($col_lg_size<3 || 12-$col_lg_size<3)
		{
			$col_md_size=12;
		}
		else
			$col_md_size=$val;

		if($col_lg_size<6 || 12-$col_lg_size<6)
		{
			$col_sm_size=12;
		}
		else
			$col_sm_size=$val;

		$col_xs_size=12; //Always make it full columns on mobile for now

		$return.= html_tag("div", $elements[$key], "col-lg-".$col_lg_size." col-md-".$col_md_size." col-sm-".$col_sm_size." col-xs-".$col_xs_size);
	}
	$return.='</div>';
	return $return;
}
function html_row($min_columns, $max_columns, $elements, $element_class=NULL, $row_class=NULL)
{
	$nr=count($elements);
	
	if($nr>=$max_columns)
		$columns=$max_columns;
	// else if($nr<=$min_columns)
		// $columns=$min_columns;
	else
		$columns=$nr;
	
	if($columns==0)
		error_log("html_row 0 columns! Params: ".print_r(array($min_columns, $max_columns, $elements, $element_class, $row_class),1));
	
	$col_size=(int)(12/$columns);
	$md_columns=ceil(($min_columns+$columns)/2);
	// if($col_size<=2)
		// $col_md_size=ceil($col_size*1.5);
	// else
		// $col_md_size=$col_size;
	$col_md_size=(int)(12/$md_columns);
	
	$col_xs_size=(int)(12/$min_columns);
		
	$return="";
    // $return.=prestr(array($columns,$min_columns, $max_columns, $nr),"STUFFS!");
	if(!empty($elements))
	{
		$return.='<div class="row'.($row_class==NULL ? "":" ".$row_class).'">';
        $i=0;
		foreach($elements as $key => $e)
		{
			if($i%$columns==0 && $i!=0)
				$return.= '</div><div class="row">';
			$return.= html_element($col_md_size, $col_xs_size, $e, $element_class, $col_size);
            $i++;
		}
		$return.='</div>';
	}
	return $return;
}

function html_elements($col_size, $col_xs_size, $elements, $element_class=NULL)
{
	$return="";
	foreach($elements as $e)
	{
		if(is_array($e))
			$return.=html_elements($col_size, $col_xs_size, $e);
		else
			$return.=html_element($col_size, $col_xs_size, $e, $element_class);
	}
	return $return;
}

function html_element($col_md_size, $col_xs_size, $element, $element_class=NULL, $col_lg_size=NULL)
{
	// $col_sm_size=(int)(12/(ceil(6/($col_xs_size))+ceil(6/($col_md_size))));
	$col_sm_size=ceil(($col_xs_size+$col_md_size)/2);
	$return="";
	$return.= '<div class="col-md-'.$col_md_size.' col-sm-'.$col_sm_size.' col-xs-'.$col_xs_size.''.($col_lg_size==NULL ? "":" col-lg-".$col_lg_size).''.($element_class==NULL ? "":" ".$element_class).'">';
	if(is_array($element))
		$return.=html_elements(12,12,$element);
	else
		$return.=$element;
	$return.= '</div>';
	return $return;
}
	
function html_form_input($input_id, $label, $type, $name, $value, $placeholder=NULL, $input_class=NULL, $helptext=NULL, $group_class=NULL, $onchange=NULL, $required=FALSE)
{
	if(!strcmp($type,"hidden"))
		return '<input '.($input_id!=NULL?'id="'.$input_id.'" ':'').'type="hidden" name="'.$name.'" value="'.$value.'">';
	
	return ($type!="hidden" ? '<div class="form-group'.($group_class!==NULL ?  " ".$group_class : "").' row">' : "").
			($label!==NULL ? '<label for="'.$input_id.'" class="col-sm-2 col-form-label">'.$label.'</label>':'').
			'<div class="col-sm-10">'.
			'<input type="'.$type.'" '.
			       ($type!="hidden" ? 'class="form-control'.($input_class!==NULL ? " ".$input_class :"").'" ' :"").
				   'id="'.$input_id.'" '.
				   'placeholder="'.$placeholder.'" '.
				   'name="'.$name.'" '.
				   'value="'.$value.'" '.
				   ($helptext!==NULL ?  'aria-describedby="'.$input_id.'helpBlock"' : "").
				   ($onchange!==NULL ?  'onchange="'.$onchange.'" ' : "").
				   ($required ? ' required' : '').
				   ' />'.
			($helptext!==NULL ? '<span id="'.$input_id.'helpBlock" class="help-block">'.$helptext.'</span>' :"").
		($type!="hidden" ?'</div>':"").
		'</div>';
}

function html_form_checkbox($label, $id, $name, $checked=NULL, $required=FALSE, $onclick=NULL, $inline=TRUE)
{
	$return='<div class="checkbox">'.
		'<label>'.
			'<input type="checkbox" id="'.$id.'" name="'.$name.'"'.
		  ($checked ? ' checked="checked"' : '').
		  ($onclick!==NULL ? ' onclick="'.$onclick.'"' : '').
		  ($required ? ' required' : '').
			'> '.$label.
		'</label>'.
	'</div>';
	if($inline)
		$return=html_tag("div",html_tag("div","","col-sm-2").html_tag("div",$return,"col-sm-2"),"row");
	return $return;
}
function html_form_radio($label, $id, $name, $options, $selected=NULL, $onclick=NULL)
{
	$r='';
	if(empty($options))
		return $r;
	
	if($label!==NULL)
		$r.=html_tag("p",html_tag("strong",$label));
	foreach($options as $value => $option_label)
	{
		$r.='<div class="radio">
		  <label>
			<input type="radio" name="'.$name.'" id="'.$id."_".$value.'" value="'.$value.'" '.
			($selected==$value ? ' checked="checked" ' : '').
			(isset($onclick[$value]) ? 'onclick="'.$onclick[$value].'"' : '').
			'>'.
			$option_label.
		  '</label>
		</div>';
	}
	return $r;
}

function html_form_textarea($input_id, $label, $name, $value="", $placeholder=NULL)
{
	return '<div class="form-group row">'.
				'<label for="'.$input_id.'" class="col-sm-2 col-form-label">'.$label.'</label>'.
				html_tag("div",'<textarea class="form-control autoExpanding " id="'.$input_id.'"'.
								' placeholder="'.$placeholder.'" name="'.$name.'">'.
								string_html_to_text($value).
								'</textarea>',"col-sm-10", false, NULL, FALSE).
			'</div>';
}

function html_form_droplist($input_id, $label, $name, $options, $selected="", $onchange=NULL, $class=NULL)
{
	if($label!==NULL && $label!="")
		$return='<label for="'.$input_id.'">'.$label.'</label>';
	else
		$return="";
	$return.='<select '.$selected.' class="form-control '.($class?$class:'').'" name="'.$name.'" id="'.$input_id.'"'.($onchange!==NULL ? 'onchange="'.$onchange.'"':'').'>';
	if(!empty($options))
    {
        foreach($options as $key => $val)
        {
            $return.='<option value="'.$key.'"'.(!strcmp($selected,$key) || $selected==$key ? ' selected="selected"':'').'>'.$val.'</option>';
        }
    }
	$return.='</select>';
	return $return;
}

function html_action_button($target_link, $button_text, $hidden_values=NULL, $button_type="primary", $large=FALSE)
{
	$h="";
	if(!empty($hidden_values))
	{
		foreach($hidden_values as $name => $value)
		{
			$h.=html_form_input(NULL, NULL, "hidden", $name, $value);
		}
	}
	return '<form action="'.$target_link.'" method="post">'.
		$h.
		html_form_button("action",$button_text, $button_type, NULL, $large).
	'</form>';
}

function html_form_button($name, $value, $button_type="default", $onclick=NULL, $large=FALSE, $inline=FALSE)
{
	$button='<input type="submit" name="'.$name.'" value="'.$value.'" '.
				'class="btn btn-'.$button_type.($large==TRUE ? ' btn-lg' : '').'" '.
				($onclick!=NULL ? 'onclick="'.$onclick.'"':'').
			'>';
	if($inline)
		$button=html_tag("div",html_tag("div","","col-sm-2").html_tag("div",$button,"col-sm-2"),"row");

	return $button;
}

function html_button($button_text, $class="btn btn-default", $onclick=NULL, $button_type=TRUE)
{
	return '<button '.($class!==NULL ? 'class="'.$class.'"' : '')
					.($onclick!==NULL ? ' onclick="'.$onclick.'"' : '')
					.($button_type ? ' type="button"' : '')
			.'>'
			.$button_text
			.'</button>';
}

function html_form($method, $inputs, $multipart=FALSE)
{
    $r='<form method="'.$method.'">';
    if(!empty($inputs))
    {
        foreach($inputs as $i)
        {
            $r.=$i;
        }
    }
    $r.='</form>';
    return $r;
}

function html_form_from_db_table($table_name, $id=NULL, $skip_members, $db_name=NULL, $just_inputs=FALSE, $field_type_override=NULL, $custom_labels=NULL, $nr_id=NULL, $only_members=NULL)
{
	// Get table columns
	$table=sql_get("SHOW COLUMNS FROM ".($db_name!=NULL ? $db_name.".":"").sql_safe($table_name).";");

	// Recieve
	if(isset($_POST[$table_name."_update_".$id]))
	{
		require_once(FUNC_PATH."db.php");
		require_once(FUNC_PATH."base_class.php");
		$values=array();
		$db=new C_db(db_host, ($db_name!=NULL ? $db_name : db_name), db_user, db_pass);
		$class=new base_class($db, $table_name, $_POST['id']);
		foreach($table as $column)
		{
			if(!strcmp($column['Field'],"id") || !strcmp($column['Field'],"last_updated") || in_array($column['Field'],$skip_members))
				continue;
			if(!strcmp($column['Field'],"source") || !strcmp($column['Field'],"origin"))
				$value=SITE_URL;
			else
				$value=$_POST[$column['Field']];
			$values[$column['Field']]=$value;
		}
		$class->update_from_arr($values);
	}
	
	$inputs=array();

	// Get values
	if($id!=NULL)
		$values=sql_get_first("SELECT * FROM ".($db_name!=NULL ? $db_name.".":"").sql_safe($table_name)." WHERE id=".sql_safe($id).";");
    
	foreach($table as $column)
	{
		if(!strcmp($column['Field'],"id") || (!empty($skip_members) && in_array($column['Field'],$skip_members)))
			continue;
		if(!empty($only_members) && !in_array($column['Field'],$only_members))
			continue;
        
		//Decide input type based on field type or override
		if(isset($field_type_override[$column['Field']]))
			$type=$field_type_override[$column['Field']];
		else
		{
			$type="text";
			if(!strcmp($column['Type'],"text"))
				$type="textarea";
			else if(!strcmp(substr($column['Type'],0,3),"int"))
			{
				//Default just number
				$type="number";
				
				//int that is foreign key to other table should be a droplist
				if(!strcmp($column['Key'], "MUL"))
				{
					//Check if it is a reference to foreign key
					$sql="	SELECT 
								`REFERENCED_TABLE_NAME`,
								`REFERENCED_COLUMN_NAME`
							FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` 
							WHERE `TABLE_SCHEMA`='".($db_name!=NULL ? $db_name : db_name)."'
							AND `COLUMN_NAME`='".$column['Field']."'
							AND REFERENCED_TABLE_NAME IS NOT NULL;";
					$fk=sql_get_first($sql);

					if(isset($fk['REFERENCED_TABLE_NAME']))
					{
						$type="droplist";
						$sql="SELECT * FROM ".$fk['REFERENCED_TABLE_NAME'].";";

						$warning_on_fail=TRUE;
						$array=false;
						$reference_table_contents=sql_get($sql, $array, NULL, $warning_on_fail);

						if(!empty($reference_table_contents))
						{
							// $fk['REFERENCED_COLUMN_NAME']
							unset($name_column);
							foreach($reference_table_contents[0] as $ref_column => $v)
							{
								if(!isset($name_column) && strcmp($ref_column, $fk['REFERENCED_COLUMN_NAME']))
								{
									$name_column=$ref_column;
								}
							}
							foreach($reference_table_contents as $refs)
							{
								$options[$refs[$fk['REFERENCED_COLUMN_NAME']]]=$refs[$name_column];
							}
						}
						$onchange=NULL;
						$class=NULL;
					}
						
				}
			}
           else if(!strcmp(substr($column['Type'],0,4),"enum"))
           {
                $type="droplist";
				$options=array();
				if(!strcmp($column['Null'],"YES"))
					$options["NULL"]=_("No value");
                
                $enum_choices=explode(",",str_replace("enum(","", str_replace(")","",str_replace("'","",$column['Type']))));
                foreach($enum_choices as $ec)
                {
                    $options[$ec]=string_unslugify($ec);
                }
           }
			//tinyint should be a checkbox
			else if(!strcmp(substr($column['Type'],0,7),"tinyint"))
				$type="checkbox";
			
			// TODO: more types!
		}	
		
		if(isset($custom_labels[$column['Field']]))
			$label=$custom_labels[$column['Field']];
		else
			$label=string_unslugify($column['Field']);

		if($nr_id!==NULL)
			$name=$table_name."[".$nr_id."][".$column['Field']."]";
		else
			$name=$column['Field'];
				
		if(!strcmp($type,"textarea"))
			$inputs[]=html_form_textarea($column['Field']."_text_".$id, $label, $name, (isset($values[$column['Field']]) ? $values[$column['Field']] : NULL));
		else if(!strcmp($type,"droplist"))
			$inputs[]=html_form_droplist($column['Field']."_text_".$id, $label, $name, $options, (isset($values[$column['Field']]) ? $values[$column['Field']] : NULL), (isset($onchange) ? $onchange : NULL), (isset($class) ? $class : NULL));
		else if(!strcmp($type,"checkbox"))
			$inputs[]=html_form_checkbox($label, $column['Field']."_checkbox_".$id, $name, (isset($values[$column['Field']]) ? $values[$column['Field']] : ($column['Default'] ? TRUE : NULL)), FALSE, NULL);
		else
			$inputs[]=html_form_input($column['Field']."_text_".$id, $label, $type, $name, (isset($values[$column['Field']]) ? $values[$column['Field']] : NULL), $column['Default'], NULL, $column['Type']);		
	}
	if($id!=NULL)
	{
		if($nr_id!==NULL)
			$hidden_name=$table_name."[".$nr_id."][id]";
		else
			$hidden_name="id";

		$inputs[]=html_form_input(NULL, NULL, "hidden", $hidden_name, $id);
        if(!$just_inputs)
            $inputs[]=html_form_button($table_name."_update_".$id, _("Update"), "success");
	}
    if($just_inputs)
        return $inputs;
	return html_form("post", $inputs);
}

function html_form_add_div($div_id, $button_text, $path, $button_class="btn btn-default")
{
	$r='<div id="'.$div_id.'">';
	$r.=html_button($button_text, $button_class, "replace_html_div('".$div_id."', '".$path."'); return false;");
	$r.='</div>';
	return $r;

}

function html_tooltip($tip_text)
{
	return '<a class="helpmarker" href=# title="'.$tip_text.'">?</a>';
}

function html_table_from_single_array($array, $headlines=NULL, $silent_columns=array(), $vertical=TRUE, $size_table=array(), $class=NULL)
{
    $r='<table class="table table-striped table-condensed'.($class?" ".$class:"").'">';
	if($headlines!==-1)
		$r.="<tr>";
    foreach($array as $key => $val)
    {
        if(!in_array($key,$silent_columns))
        {
            $size="";
            if(isset($size_table[$key]['min-width']))
                $size.="min-width: ".$size_table[$key]['min-width']."; ";
            if(isset($size_table[$key]['max-width']))
                $size.="max-width: ".$size_table[$key]['max-width']."; ";
            if(isset($size_table[$key]['width']))
                $size.="width: ".$size_table[$key]['width']."; ";
            if($size!="")
                $style=' style="'.$size.'"';
            else
                $style="";
            if($headlines!==-1)
                $r.="<th $style>".string_unslugify($key)."</th>";
            $keys[]=$key;
        }
    }
		$r.="<tr>";
    foreach($array as $k => $val)
	{
        if(!in_array($k,$silent_columns))
        {
            $size="";
            if(isset($size_table[$k]['min-width']))
                $size.="min-width: ".$size_table[$k]['min-width']."; ";
            if(isset($size_table[$k]['max-width']))
                $size.="max-width: ".$size_table[$k]['max-width']."; ";
            if(isset($size_table[$k]['width']))
                $size.="width: ".$size_table[$k]['width']."; ";
            if($size!="")
                $style=' style="'.$size.'"';
            else
                $style="";

            $r.="<td $style>".$val."</td>";
        }
    }
    $r.="</tr>";
	$r.="</table>";
	return $r;
}

function html_table_from_array($array, $headlines=NULL, $silent_columns=array(), $size_table=array(), $class=NULL)
{
    if(empty($array))
        return _("Empty array");
    
	$r='<table class="table table-striped table-condensed'.($class?" ".$class:"").'">';
	if($headlines!==-1)
		$r.="<tr>";
	$keys=array();
	if($headlines==NULL || empty($headlines) || $headlines==-1)
	{
		foreach($array[0] as $key => $val)
		{
			if(!in_array($key,$silent_columns))
			{
				$size="";
				if(isset($size_table[$key]['min-width']))
					$size.="min-width: ".$size_table[$key]['min-width']."; ";
				if(isset($size_table[$key]['max-width']))
					$size.="max-width: ".$size_table[$key]['max-width']."; ";
				if(isset($size_table[$key]['width']))
					$size.="width: ".$size_table[$key]['width']."; ";
				if($size!="")
					$style=' style="'.$size.'"';
				else
					$style="";
				if($headlines!==-1)
					$r.="<th $style>".string_unslugify($key)."</th>";
				$keys[]=$key;
			}
		}
	}
	else
	{
		foreach($headlines as $key => $headline)
		{
				$size="";
				if(isset($size_table[$key]['min-width']))
					$size.="min-width: ".$size_table[$key]['min-width']."; ";
				if(isset($size_table[$key]['max-width']))
					$size.="max-width: ".$size_table[$key]['max-width']."; ";
				if(isset($size_table[$key]['width']))
					$size.="width: ".$size_table[$key]['width']."; ";
				if($size!="")
					$style=' style="'.$size.'"';
				else
					$style="";

			$r.="<th $style>$headline</th>";
			$keys[]=$key;
		}
	}
	if($headlines!==-1)
		$r.="</tr>";
	foreach($array as $a)
	{
		$r.="<tr>";
		foreach($keys as $k)
		{
			if(!in_array($k,$silent_columns))
			{
				$size="";
				if(isset($size_table[$k]['min-width']))
					$size.="min-width: ".$size_table[$k]['min-width']."; ";
				if(isset($size_table[$k]['max-width']))
					$size.="max-width: ".$size_table[$k]['max-width']."; ";
				if(isset($size_table[$k]['width']))
					$size.="width: ".$size_table[$k]['width']."; ";
				if($size!="")
					$style=' style="'.$size.'"';
				else
					$style="";

				$r.="<td $style>".(isset($a[$k]) ? $a[$k] : "")."</td>";
			}
		}
		$r.="</tr>";
	}
	$r.="</table>";
	return $r;
}

function html_pagination_row($page_nr_name, $total_pages, $first_page_number=1)
{
	
	if(isset($_REQUEST[$page_nr_name]))
		$pnr=$_REQUEST[$page_nr_name];
	else
		$pnr=$first_page_number;

	echo '<div class="row center">
		<nav>
		  <ul class="pagination">';
	
	// if we are further along than 5 pages in, put a link to first page
	if($pnr-5>$first_page_number)
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $first_page_number).'" aria-label="First"><span aria-hidden="true">|&laquo;</span></a></li>';
	
	if($pnr<$first_page_number)
		echo '<li class="disabled"> <a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
	else if($pnr>$first_page_number)
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $_REQUEST[$page_nr_name]-1).'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

	for($i=($pnr-5);$i<=$total_pages && $i<($pnr+5); $i++)
	{
		if($i>=$first_page_number)
		{
			if($i==$pnr)
			{
				echo '<li class="active">
					<a href="'.add_get_to_current_URL($page_nr_name, $i).'"><span class="sr-only">('._("current").')</span>'.($i).'</a>
				</li>';
			}
			else
				echo '<li><a href="'.add_get_to_current_URL($page_nr_name, $i).'">'.($i).'</a></li>';
		}
	}

	if($pnr<$total_pages)
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $pnr+1).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';	
	else 
		echo '<li class="disabled"> <a href="'.add_get_to_current_URL($page_nr_name, $pnr+1).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';	
	
	
	if($i<$total_pages)
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $total_pages).'" aria-label="Next"><span aria-hidden="true">&raquo;|</span></a></li>';	
	
	echo '
		  </ul>
		</nav>
	</div>';
}

//Wrapper since this was apparently already done in message.php
function html_progress_bar($percent, $max_decimals=2)
{
	return message_progress_bar($percent, $max_decimals);
}

function html_menu($menu=array(), $request_choser="p", $brand_text="", $brand_link="", $class="navbar navbar-default", $show_home_link=TRUE, $show_feedback=TRUE, $expand_size="lg")
{
    if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
    {
        if(!strcmp($class, "navbar navbar-default"))
            $class="navbar navbar-light bg-light"; // This is the new "default"
        else if(!strcmp($class, "navbar navbar-inverse"))
            $class="navbar navbar-dark bg-dark"; // This is the new black
        $class.=" navbar-expand-".$expand_size;
        
        $r='<nav class="'.$class.' horisontal">'.
            ( $brand_text!="" ? '<a class="navbar-brand" href="'.$brand_link.'">'.$brand_text.'</a>' : "").
          '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>';

        $menu_items=array();
		
		//version
		$menu_items[]=version_show_linked_number("v", 'navbar-brand', TRUE);
		
        if($show_home_link)
            $menu_items[]=html_tag("li",html_link(SITE_URL, _("Home"), "nav-link"),"navbar-nav".(!isset($_GET[$request_choser]) ? " active" : ""));


		// Parameter menu items
		foreach($menu as $m)
		{
            $menu_items[]=html_tag("li",html_link($m['link'], $m['text'], "nav-link"),"navbar-nav".(isset($_GET[$request_choser]) && !strcmp($_GET[$request_choser],$m['text']) ? " active" : ""));
		}
		
        $menu_items[]=admin_menu_dropdown(TRUE);
        $menu_items[]=display_custom_pages_menu(TRUE);
        if($show_feedback)
            $menu_items[]=html_tag("li",html_link(SITE_URL."/feedback", _("Feedback"), "nav-link"),"navbar-nav".(isset($_GET[$request_choser]) && !strcmp($_GET[$request_choser],"feedback") ? " active" : ""));

        $r.='<div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">'.
                implode("",$menu_items).
            '
            </ul>
            ';
		// User menu items
		$r.=login_display_link('data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"', TRUE);
        $r.=display_friend_request_drop_menu(TRUE);
		
		//search form
        $r.=html_form_search();

        $r.='
          </div>
        </nav>';
        return $r;
    }

    $r='<nav class="'.$class.'">
			<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#comp-navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>';
		if($brand_text!="")
			$r.='<a class="navbar-brand" href="'.$brand_link.'">'.$brand_text.'</a>';
		// else if($brand_text!="")
			// $r.=$brand_text;
		$r.='	</div>';
		$r.= '<div class="collapse navbar-collapse" id="comp-navbar-collapse">';
		$r.='<ul class="nav navbar-nav menu">';
		foreach($menu as $m)
		{
			$r.="<li";
				if((!isset($_REQUEST[$request_choser]) &&  !strcmp($m['text'],$menu[0]['text']))|| (isset($_REQUEST[$request_choser]) && !strcmp($_REQUEST[$request_choser],$m['text'])))
					$r.=" class=\"active\""; $r.=">";
				$r.="<a href=\"".$m['link']."\">".$m['text']."</a>";
			$r.="</li>";
		}
		$r.="</ul>
		</div>
        </nav>";
	return $r;
}

function html_form_search()
{
    return '<form class="form-inline my-2 my-lg-0" action="'.SITE_URL.'">
              <input class="form-control mr-sm-2" type="search" placeholder="'._("Search").'" aria-label="'._("Search").'" name="search" value="'.(isset($_GET['search']) ? $_GET['search'] : "").'">
              <button class="btn btn-outline-success my-2 my-sm-0" type="submit">'._("Search").'</button>
            </form>';
}
/***
/*	Populating tabs array:
	$tabs[]=array(	"id"	=>	"important",
						"link"	=>	Url to page this is on. Only needed on the first item,
						"text"	=>	_("Tab text"),
						"content"	=>	All html visible when the tab is active);
*
***/
function html_nav_tabs($tabs=array(), $active=NULL)
{
	if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
    {
        //https://getbootstrap.com/docs/4.1/components/navs/#tabs
        $first_key=key($tabs);
        $r='<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">';
        foreach($tabs as $id => $tab)
        {
            // if($tab['has_tab'])
                $r.='<a class="nav-item nav-link '
						.(($active!==NULL && !strcmp($active, $id) ) || (!strcmp($first_key, $id) && $active==NULL ) ? "active " :	"") // Active set or first id
						.(!$tab['has_tab'] && ($active==NULL || strcmp($active, $id)) ? 'd-none' : '').'" id="'.$id.'-tab" data-toggle="tab" href="#'.$id.'" role="tab" aria-controls="'.$id.'" aria-selected="true">'.string_unslugify($id).'</a>';
        }
        $r.='</div>
        </nav>
        <div class="tab-content" id="nav-tabContent">';
        foreach($tabs as $id => $tab)
        {
            $r.='<div class="tab-pane fade '.(($active!==NULL && !strcmp($active, $id) ) || (!strcmp($first_key, $id) && $active==NULL )? 'show active' : "").'" id="'.$id.'" role="tabpanel" aria-labelledby="'.$id.'-tab">'.
            $tab['content'].
            '</div>';
        }
        $r.='</div>';
        return $r;
    }
	
	$r='<div class="row">
		<div class="col-lg-12">';
/*			<!-- Nav tabs -->		*/
	$r.='<ul class="nav nav-tabs" role="tablist">';
	foreach($tabs as $key => $tab)
	{
		if($key==0)
			$r.='<li role="presentation"'.' class="active">'.
					'<a href="'.$tab['link'].'" >'.$tab['text'].'</a>'.
				'</li>';
		else if(!isset($tab['invisible']))
			$r.='<li role="presentation">'.
					'<a href="#'.$tab['id'].'" aria-controls="'.$tab['id'].'" role="tab" data-toggle="tab">'.$tab['text'].'</a>'.
				'</li>';
		else
			$r.='<span style="display:none">'.
					'<li role="presentation">'.
						'<a href="#'.$tab['id'].'" aria-controls="'.$tab['id'].'" role="tab" data-toggle="tab">'.$tab['text'].'</a>'.
					'</li>'.
				'</span>';
	}	
	$r.='</ul>';

/*			<!-- Tab panes -->		*/
	$r.='<div class="tab-content">';
	foreach($tabs as $key => $tab)
	{
		$r.='<div role="tabpanel" class="tab-pane fade in '.($key==0?'active':'').'" id="'.$tab['id'].'">'.
				$tab['content'].
			'</div>';
	}
	$r.='</div>';
	$r.='</div>';
	$r.='</div>';
	return $r;
}

function html_show_hide_clicker($div_id, $label, $contents)
{
	$r='<div id="'.$div_id.'" style="display:none">';
	$r.=html_tag("p",'<a class="showhideclicker"'.
						'onClick="showhide(\''.$div_id."');".
						"showhide('".string_slugify($label)."_".$div_id."');\">".
					"[-".sprintf(_("Hide %s"),$label)."-]</a>"); //toggle-pryl! =)
	$r.=$contents;
	$r.='</div>';
	$r.=html_tag("p",'<a id="'.string_slugify($label)."_".$div_id.'" onClick="showhide(\''.$div_id."');".
													"showhide('".string_slugify($label)."_".$div_id."');\"".
						'class="commentclicker" href="#'.$label.'">'.
						'[-'.sprintf(_("Show %s"),$label)."-]
		</a>");
	return $r;
}

/***
*   function html_carousel
*   Parameters:
*       image_array -   array of "images"; array("url"    =>  string
                                                 "alt"   =>  string)
***/
function html_carousel($image_array, $div_class="", $images_full_width=TRUE)
{
	reset($image_array);
	$first_key = key($image_array);

	// https://getbootstrap.com/docs/4.1/components/carousel/#with-indicators
	$r='<div id="carouselExampleIndicators" class="carousel slide '.$div_class.'" data-ride="carousel">
  <ol class="carousel-indicators">';
    // <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
    // <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
    // <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
	foreach($image_array as $key => $val)
		$r.='<li data-target="#carouselExampleIndicators" data-slide-to="'.$key.'" '.(!strcmp($first_key, $key) ? 'class="active"' : "").'></li>';
  $r.='</ol>
  <div class="carousel-inner">';
    // <div class="carousel-item active">
      // <img class="d-block w-100" src=".../800x400?auto=yes&bg=777&fg=555&text=First slide" alt="First slide">
    // </div>
		foreach($image_array as $key => $image)
			$r.='<div class="carousel-item  '.(!strcmp($first_key, $key) ? 'active' : "").'">
				'.(isset($image['link']) ? '<a href="'.$image['link'].'">' : '').'
				  <img class="d-block '.($images_full_width ? "w-100" : "").'" src="'.$image['url'].'" alt="'.(isset($image['alt']) ? $image['alt'] : "Image").'">
					'.(isset($image['caption']) ? '<div class="carousel-caption d-none d-md-block">
						'.$image['caption'].'
					</div>' :'').
					(isset($image['link']) ? '</a>' : '').'
				</div>';
	$r.='</div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>';
return $r;
}

?>