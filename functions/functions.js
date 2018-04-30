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
			async: true,
			success: function(data) {
				$( '#' + div_id_to ).html( data );
				result = true;
			} 
		 });
		return result;
	}
	return false;
}

function get_base_url()
{
	pathArray = location.href.split( '/' );
	protocol = pathArray[0];
	host = pathArray[2];
	return protocol + '//' + host;
}

function showhide(id)
{
   if(document.getElementById)
   {
      var obj = document.getElementById(id);
   }
   else if(document.all)
   {
      var obj = document.all(id);
   }
   
   if(obj.style.display == 'none')
   {
      obj.style.display = '';
   }
   else
   {
      obj.style.display = 'none';
   }
}

function show(id)
{
   if(document.getElementById)
   {
      var obj = document.getElementById(id);
   }
   else if(document.all)
   {
      var obj = document.all(id);
   }

   obj.style.display = '';
}

function hide(id)
{
   if(document.getElementById)
   {
      var obj = document.getElementById(id);
   }
   else if(document.all)
   {
      var obj = document.all(id);
   }
   
  obj.style.display = 'none';
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
	pathArray = location.href.split( '/' );
	protocol = pathArray[0];
	host = pathArray[2];
	url = protocol + '//' + host;
	var adress=url+'/functions/feedback/operation.php?operation=' + operation + '&id=' + id +'&div_id=' + target_div_id ;
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

$('a.single_tab_link').on('click', function (e) {
    $('#single-tab').tab('show');
})
$('a.reply_tab_link').on('click', function (e) {
    e.preventDefault();
    $('#reply-tab').tab('show');
})

//Tooltip
$(function() {
	$(".helpmarker").tooltip();
 });
 
 
 // For heights of carosel
function carouselNormalization() {
	var items = $('.carousel-item'), //grab all slides
		heights = [], //create empty array to store height values
		tallest; //create variable to make note of the tallest slide
	// console.log(items);

	if (items.length) {
		function normalizeHeights() {
			items.each(function() { //add heights to array
				heights.push($(this).height()); 
			});
			tallest = Math.max.apply(null, heights); //cache largest value
			items.each(function() {
				$(this).css('min-height',tallest + 'px');
			});
		};
		normalizeHeights();

		$(window).on('resize orientationchange', function () {
			tallest = 0, heights.length = 0; //reset vars
			items.each(function() {
				$(this).css('min-height','0'); //reset min-height
			}); 
			normalizeHeights(); //run it again 
		});
	}
}

$(document).ready(function() {
	// console.log( "ready!" );
    carouselNormalization();
});