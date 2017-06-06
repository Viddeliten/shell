<?php
/*	This file contains functions for printing various html elements according to style guides for bootstrap.
	The thought behind having this file is to only have to change html creating in one place when bootstrap updates	
	ALL the functions just returns html in strings so they can be echoed or used in other ways. */
	
function html_tag($tag_type, $text, $class=NULL, $get_link_titles=false, $div_id=NULL)
{
	$the_text=$text;
	return html_tag_text_ref($tag_type, $the_text, $class, $get_link_titles, $div_id);
}

function html_tag_text_ref($tag_type, &$text, $class=NULL, $get_link_titles=false, $div_id=NULL)
{
	$text=str_replace("\n", "<br />", $text);
	$text=str_ireplace("\r","<br />",$text);
	$text=str_ireplace("<br /><br />","</p><p>",$text);
	//Look for urls
	string_replace_urls_with_links($text, $get_link_titles);
	
	//Break long words
	string_break_long_words($text);

	return '<'.$tag_type.($class==NULL ? "":' class="'.$class.'"').($div_id==NULL ? "":' id="'.$div_id.'"').'>'.$text.'</'.$tag_type.'>';
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
	else if($nr<=$min_columns)
		$columns=$min_columns;
	else
		$columns=$nr;
	
	$col_size=(int)(12/$columns);
	if($col_size<=2)
		$col_md_size=ceil($col_size*1.5);
	else
		$col_md_size=$col_size;
	
	$col_sm_size=(int)(12/$min_columns);
		
	$return="";
	if(!empty($elements))
	{
		$return.='<div class="row'.($row_class==NULL ? "":" ".$row_class).'">';
		foreach($elements as $key => $e)
		{
			if($key%$columns==0 && $key!=0)
				$return.= '</div><div class="row">';
			$return.= html_element($col_md_size, $col_sm_size, $e, $element_class, $col_size);
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
	// preprint(array($col_md_size, $col_xs_size, $element, $element_class, $col_lg_size),"DEBUGhtml_element");
	$col_sm_size=(int)(12/(ceil(6/($col_xs_size))+ceil(6/($col_md_size))));
	$return="";
	$return.= '<div class="col-md-'.$col_md_size.' col-sm-'.$col_sm_size.' col-xs-'.$col_xs_size.''.($col_lg_size==NULL ? "":" col-lg-".$col_lg_size).''.($element_class==NULL ? "":" ".$element_class).'">';
	if(is_array($element))
		$return.=html_elements(12,12,$element);
	else
		$return.=$element;
	$return.= '</div>';
	return $return;
}
	
function html_form_input($input_id, $label, $type, $name, $value, $placeholder=NULL, $input_class=NULL, $helptext=NULL, $group_class=NULL, $onchange=NULL)
{
	return ($type!="hidden" ? '<div class="form-group'.($group_class!==NULL ?  " ".$group_class : "").'">' : "").
			($label!==NULL ? '<label for="'.$input_id.'">'.$label.'</label>':'').
			'<input type="'.$type.'" '.
			       ($type!="hidden" ? 'class="form-control'.($input_class!==NULL ? " ".$input_class :"").'" ' :"").
				   'id="'.$input_id.'" '.
				   'placeholder="'.$placeholder.'" '.
				   'name="'.$name.'" '.
				   'value="'.$value.'" '.
				   ($helptext!==NULL ?  'aria-describedby="'.$input_id.'helpBlock"' : "").
				   ($onchange!==NULL ?  'onchange="'.$onchange.'" ' : "").
				   ' />'.
			($helptext!==NULL ? '<span id="'.$input_id.'helpBlock" class="help-block">'.$helptext.'</span>' :"").
		($type!="hidden" ?'</div>':"");
}

function html_form_checkbox($label, $id, $name, $checked=NULL, $required=FALSE, $onclick=NULL)
{
	return '<div class="checkbox">
    <label>
      <input type="checkbox" id="'.$id.'" name="'.$name.'"'.
	  ($checked ? ' checked="checked"' : '').
	  ($onclick!==NULL ? ' onclick="'.$onclick.'"' : '').
	  ($required ? ' required' : '').
		'> '.$label.'
    </label>
  </div>';
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

function html_form_textarea($input_id, $label, $name, $value, $placeholder=NULL)
{
	return '<div class="form-group">
			<label for="'.$input_id.'">'.$label.'</label>
			<textarea class="form-control autoExpanding" id="'.$input_id.'" placeholder="'.$placeholder.'" name="'.$name.'">'
			.string_html_to_text($value).'</textarea>
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

function html_action_button($target_link, $button_text, $hidden_values=NULL)
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
		html_form_button("action",$button_text, "info").
	'</form>';
}

function html_form_button($name, $value, $button_type="default")
{
	return '<input type="submit" name="'.$name.'" value="'.$value.'" class="btn btn-'.$button_type.'">';
}

function html_button($button_text, $class="btn btn-default", $onclick=NULL)
{
	return '<button '.($class!==NULL ? 'class="'.$class.'"' : '')
					.($onclick!==NULL ? ' onclick="'.$onclick.'"' : '')
			.'>'
			.$button_text
			.'</button>';
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

function html_table_from_array($array, $headlines=NULL, $silent_columns=array())
{
	$r="<table class=\"table table-striped table-condensed\">
	<tr>";
	$keys=array();
	if($headlines==NULL || empty($headlines))
	{
		foreach($array[0] as $key => $val)
		{
			if(!in_array($key,$silent_columns))
			{
				$r.="<th>$key</th>";
				$keys[]=$key;
			}
		}
	}
	else
	{
		foreach($headlines as $key => $headline)
		{
			$r.="<th>$headline</th>";
			$keys[]=$key;
		}
	}
	$r.="</tr>";
	foreach($array as $a)
	{
		$r.="<tr>";
		foreach($keys as $k)
		{
			if(!in_array($k,$silent_columns))
			{
				$r.="<td>".$a[$k]."</td>";
			}
		}
		$r.="</tr>";
	}
	$r.="</table>";
	return $r;
}

function html_pagination_row($page_nr_name, $total_pages)
{
	echo '<div class="row center">
		<nav>
		  <ul class="pagination">';
	if(!isset($_REQUEST[$page_nr_name]) || $_REQUEST[$page_nr_name]<1)
		echo '<li class="disabled"> <a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
	else
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $_REQUEST[$page_nr_name]-1).'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

	
	if(isset($_REQUEST[$page_nr_name]))
		$pnr=$_REQUEST[$page_nr_name];
	else
		$pnr=1;
	
	for($i=($pnr-5);$i<$total_pages && $i<($pnr+5); $i++)
	{
		if($i>=1)
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

	if($i<=$total_pages)
		echo '<li> <a href="'.add_get_to_current_URL($page_nr_name, $i).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';	
	else 
		echo '<li class="disabled"> <a href="'.add_get_to_current_URL($page_nr_name, $i).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';	
	echo '
		  </ul>
		</nav>
	</div>';
}

//Wrapper since this was apparently already done in message.php
function html_progress_bar($percent)
{
	return message_progress_bar($percent);
}


?>