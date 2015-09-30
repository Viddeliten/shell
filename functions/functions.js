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

function replace_html_div_inner(div_id_to, path)
{
	if(path !== undefined)
	{
		var result=false;
		$.ajax({
			url: path,
			type: 'get',
			dataType: 'html',
			async: false,
			success: function(data) {
				$( '#' + div_id_to ).html( data );
				result = true;
			} 
		 });
		return result;
	}
	return false;
}

function run_html(path)
{
	// alert (path);
	if(path !== undefined)
	{
		var result=false;
		$.ajax({
			url: path,
			type: 'get',
			dataType: 'html',
			async: false,
			success: function(data) {
				// alert(data);
				result = true;
			} 
		 });
		return result;
	}
	return false;
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


//Activating bootstrap tabs from link outside tab list
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = this.href.split('#');
    $('.nav a').filter('[href="#'+target[1]+'"]').tab('show');
});

//Tooltip
$(function() {
	$(".helpmarker").tooltip();
 });