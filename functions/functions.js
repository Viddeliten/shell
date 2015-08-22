function replace_html_div(div_id_to, path)
{
	if(path !== undefined)
	{
		$.get( path, function( data ) {
			$( '#' + div_id_to ).replaceWith( data );
		});
	}
	else
		$( '#' + div_id_to ).replaceWith();
}

function feedback_operation(operation, id, target_div_id, extra_element_id)
{
	// alert('onwards');
	var adress='functions/feedback/operation.php?operation=' + operation + '&id=' + id +'&div_id=' + target_div_id ;
	// alert(adress);
	if(extra_element_id)
	{
		if(operation=='expand' || operation=='colapse')
			adress+='&parent=' + extra_element_id;
		else
			adress+='&extra=' + document.getElementById(extra_element_id).value;
	}
	// alert('adressen: ' + adress);
	// alert(adress);
	//('unresolve',".$id.", 'feedback_resolved_".$id."')	
	$.get( adress, function( data ) {
		$( '#' + target_div_id ).replaceWith( data );
	})
}

$( ".autoExpanding" ).each(function() {

	this.addEventListener('keyup', function() {
		this.style.overflow = 'hidden';
		this.style.height = 0;
		this.style.height = this.scrollHeight + 'px';
	}, false);

});