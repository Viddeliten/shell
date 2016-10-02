<?php
/*	This file contains functions for printing various html elements according to style guides for bootstrap.
	The thought behind having this file is to only have to change html creating in one place when bootstrap updates	
	ALL the functions just returns html in strings so they can be echoed or used in other ways. */
	
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