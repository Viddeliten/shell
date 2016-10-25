<?php
/*	This file contains functions for printing various html elements according to style guides for bootstrap.
	The thought behind having this file is to only have to change html creating in one place when bootstrap updates	
	ALL the functions just returns html in strings so they can be echoed or used in other ways. */
	
function html_tag($tag_type, &$text, $class=NULL, $get_link_titles=false)
{
	$text=str_ireplace("\n","<br />",$text);
	$text=str_ireplace("<br /><br />","</p><p>",$text);
	//Look for urls
	string_replace_urls_with_links($text, $get_link_titles);

	return '<'.$tag_type.($class==NULL ? "":' class="'.$class.'"').'>'.$text.'</'.$tag_type.'>';
}

function html_link($url, $text, $class=NULL)
{
	$the_text=str_ireplace("\n","<br />",$text);
	$the_text=str_ireplace("<br /><br />","</p></p>",$the_text);
	return '<a href="'.$url.'"'.($class==NULL ? "":' class="'.$class).'>'.$the_text.'</a>';
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

function html_row($min_columns, $max_columns, $elements, $element_class=NULL, $row_class=NULL)
{
	$nr=count($elements);
	
	if($nr>=$max_columns)
		$columns=$max_columns;
	else if($nr<=$min_columns)
		$columns=$min_columns;
	else
		$columns=$nr;
	
	$col_size=(int)(12/$columns);
	$col_sm_size=(int)(12/$min_columns);
		
	$return="";
	if(!empty($elements))
	{
		$return.='<div class="row'.($row_class==NULL ? "":" ".$row_class).'">';
		foreach($elements as $key => $e)
		{
			if($key%$columns==0 && $key!=0)
				$return.= '</div><div class="row">';
			$return.= html_element($col_size, $col_sm_size, $e, $element_class);
		}
		$return.='</div>';
	}
	return $return;
}

function html_elements($col_size, $col_sm_size, $elements, $element_class=NULL)
{
	$return="";
	foreach($elements as $e)
	{
		if(is_array($e))
			$return.=html_elements($col_size, $col_sm_size, $e);
		else
			$return.=html_element($col_size, $col_sm_size, $e, $element_class);
	}
	return $return;
}

function html_element($col_size, $col_sm_size, $element, $element_class=NULL)
{
	$return="";
	$return.= '<div class="col-md-'.$col_size.' col-xs-'.$col_sm_size.''.($element_class==NULL ? "":" ".$element_class).'">';
	if(is_array($element))
		$return.=html_elements(12,12,$element);
	else
		$return.=$element;
	$return.= '</div>';
	return $return;
}
	
function html_form_input($input_id, $label, $type, $name, $value, $placeholder=NULL, $class=NULL, $helptext=NULL)
{
	return ($type!="hidden" ? '<div class="form-group">' : "").
			($label!==NULL ? '<label for="'.$input_id.'">'.$label.'</label>':'').
			'<input type="'.$type.'" '.
			       ($type!="hidden" ? 'class="form-control'.($class!==NULL ? " ".$class :"").'" ' :"").
				   'id="'.$input_id.'" '.
				   'placeholder="'.$placeholder.'" '.
				   'name="'.$name.'" '.
				   'value="'.$value.'" '.
				   ($helptext!==NULL ?  'aria-describedby="'.$input_id.'helpBlock"' : "").' />'.
			($helptext!==NULL ? '<span id="'.$input_id.'helpBlock" class="help-block">'.$helptext.'</span>' :"").
		($type!="hidden" ?'</div>':"");
}

function html_form_textarea($input_id, $label, $name, $value, $placeholder=NULL)
{
	return '<div class="form-group">
			<label for="'.$input_id.'">'.$label.'</label>
			<textarea class="form-control autoExpanding" id="'.$input_id.'" placeholder="'.$placeholder.'" name="'.$name.'">'.$value.'</textarea>
		</div>';
}

function html_form_droplist($input_id, $label, $name, $options, $selected="", $onchange=NULL)
{
	$return='<label for="'.$input_id.'">'.$label.'</label>
		<select class="form-control" name="'.$name.'" id="'.$input_id.'"'.($onchange!==NULL ? 'onchange="'.$onchange.'"':'').'>';
	foreach($options as $key => $val)
	{
		$return.='<option value="'.$key.'"'.(!strcmp($selected,$key) ? ' selected="selected"':'').'>'.$val.'</option>';
	}
	$return.='</select>';
	return $return;
}

function html_form_button($name, $value, $button_type="default")
{
	return '<input type="submit" name="'.$name.'" value="'.$value.'" class="btn btn-'.$button_type.'">';
}

function html_tooltip($tip_text)
{
	return '<a class="helpmarker" href=# title="'.$tip_text.'">?</a>';
}