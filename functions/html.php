<?php
/*	This file contains functions for printing various html elements according to style guides for bootstrap.
	The thought behind having this file is to only have to change html creating in one place when bootstrap updates	
	ALL the functions just returns html in strings so they can be echoed or used in other ways. */
	
function html_rows($min_columns, $max_columns, $elements)
{
	$nr=count($elements);
	
	if($nr>=$max_columns)
		$columns=$max_columns;
	else if($nr<=$min_columns)
		$columns=$min_columns;
	else
		$columns=$nr;
	
	$col_size=(int)(12/$columns);
		
	$return="";
	if(!empty($elements))
	{
		$return.='<div class="row">';
		foreach($elements as $key => $e)
		{
			if($key%$columns==0 && $key!=0)
				$return.= '</div><div class="row">';
			$return.= '<div class="col-md-'.$col_size.'">';
				$return.=$e;
			$return.= '</div>';
		}
		$return.='</div>';
	}
	return $return;
}
	
function html_form_input($input_id, $label, $type, $name, $value, $placeholder=NULL)
{
	return '<div class="form-group">
			<label for="'.$input_id.'">'.$label.'</label>
			<input type="'.$type.'" class="form-control" id="'.$input_id.'" placeholder="'.$placeholder.'" name="'.$name.'" value="'.$value.'">
		</div>';
}

function html_form_textarea($input_id, $label, $name, $value, $placeholder=NULL)
{
	return '<div class="form-group">
			<label for="'.$input_id.'">'.$label.'</label>
			<textarea class="form-control autoExpanding" id="'.$input_id.'" placeholder="'.$placeholder.'" name="'.$name.'">'.$value.'</textarea>
		</div>';
}

function html_form_button($name, $value, $button_type="default")
{
	return '<input type="submit" name="'.$name.'" value="'.$value.'" class="btn btn-'.$button_type.'">';
}

function html_tooltip($tip_text)
{
	return '<a class="helpmarker" href=# title="'.$tip_text.'">?</a>';
}