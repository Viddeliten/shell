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
   } else
   {
      obj.style.display = 'none';
   }
}

function toggleshow(id)
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

function togglehide(id)
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

function condShow(boxName,id)
{
	if( document.getElementById(boxName).checked==true)
	{
		toggleshow(id)
	}
	else
	{
		togglehide(id);
	}
} 

function getCheckedBoxes(chkboxName) {
  var checkboxes = document.getElementsByName(chkboxName);
  var checkboxesChecked = [];
  // loop over them all
  for (var i=0; i<checkboxes.length; i++) {
     // And stick the checked ones onto an array...
     if (checkboxes[i].checked) {
        checkboxesChecked.push(checkboxes[i].value);
     }
  }
  // Return the array if it is non-empty, or null
  return checkboxesChecked.length > 0 ? checkboxesChecked : null;
}

function getCheckBoxes(chkboxName) {
  var checkboxes = document.getElementsByName(chkboxName);
  var checkboxesChecked = [];
  // loop over them all
  for (var i=0; i<checkboxes.length; i++) {
     // And stick the checked ones onto an array...
        checkboxesChecked.push(checkboxes[i].value);
  }
  // Return the array if it is non-empty, or null
  return checkboxesChecked.length > 0 ? checkboxesChecked : null;
}

function submit_delete_invoice_post(thisform, id)
{
	document.getElementById('delete_invoice_post_id').value=id;
	// alert(form.delete_invoice_post_id.value + id);
	thisform.submit();
}

function confirmation_delete(form_id, asking_text)
{
	if(asking_text === undefined) {
        asking_text = 'Do you really want to delete? This cannot be undone.';
    }
	var answer = confirm(asking_text)
	if (answer){
		document.forms[form_id].submit();
	}
	else
	{
		void(0);
	}
}

function confirmation_form(form_id, c_string)
{
	var answer = confirm(c_string)
	if (answer){
		document.forms[form_id].submit();
	}
	else
	{
		void(0);
	}
}

function CheckAll(oFrm)
{
	els=oFrm.elements;
	for (i=0;i<els.length;i++)
	{
		if(els[i].type=='checkbox')
			els[i].checked=!els[i].checked;
	}
}

function replace_html_div(div_id_to, path, async)
{
	if(path !== undefined)
	{
        if(async !== undefined)
        {
            var result=false;
            $.ajax({
                url: path,
                type: 'get',
                dataType: 'html',
                async: true,
                success: function(data) {
                    $( '#' + div_id_to ).replaceWith( data );
                    result = true;
                } 
             });
            return result;
        }
    
		$.get( path, function( data ) {
			$( '#' + div_id_to ).replaceWith( data );
		});
	}
	else
		$( '#' + div_id_to ).replaceWith();
}

function js_switch_all(div_id)
{
	var div = document.getElementById(div_id);
	
	// alert(div_id + ' is ' + div.getAttribute('state'));

	//If we are switching from initial, switch all others with same class to initial
	if(div.getAttribute('state')==="initial")
	{
		var elements=document.getElementsByClassName(div.getAttribute("class"));
		var i;
		for (i = 0; i < elements.length; i++) {
			if(elements[i].getAttribute('state')!=="initial")
				js_switch_one(elements[i].getAttribute('id'), "initial");
		}
	}
	
	//Then lastly, switch the one we wanted
	js_switch_one(div_id);
}	

function js_switch_one(div_id, force_state)
{
	// alert(div_id + ' to ' + force_state);
	var div = document.getElementById(div_id);
	console.log("swithc: "+div_id + ' from ' + div.getAttribute('state'));
	
	// html_ajax_div_switcher($div_id, $content_function, $initial_parameters, $switch_parameters, $class, $switch_all=TRUE, $switching=FALSE);
		// $r='<div 
		// id="'.$div_id.'" 
		// class="'.$class.' '.$extra_classes.'" 
		// state="'.($switching ? "switched" : "initial").'" 
		// content_function="'.$content_function.'" 
		// initial_parameters="'.serialize($initial_parameters).'"
		// switch_parameters="'.serialize($switch_parameters).'"
		// onclick="'.($switch_all ? "js_switch_all(".$div_id.");" : "js_switch_one(this);" ).'" >';

	var parameters={};
	
	parameters.div_id=div_id;
	parameters.content_function=div.getAttribute('content_function');
	parameters.initial_parameters=div.getAttribute('initial_parameters');
	parameters.switch_parameters=div.getAttribute('switch_parameters');
	parameters.class=div.getAttribute('class');
	parameters.switch_all=false;
	if(force_state==="initial")
		parameters.switching=false;
	else if(force_state==="switched")
		parameters.switching=true;
	else if(div.getAttribute('state')==="initial")
		parameters.switching=true;
	else
		parameters.switching=false;

	var path = get_base_url() + '/op.php?f=switch&parameters='+JSON.stringify( parameters );

	replace_html_div(div.id, path, false);
}

function datestring_from_date(d)
{
    var month=d.getMonth()+1;
    if(month<10)
        month='0' + month;
    var day=d.getDate();
    if(day<10)
        day='0' + day;
    var hour=d.getHours();
    if(hour<10)
        hour='0' + hour;
    var minute=d.getMinutes();
    if(minute<10)
        minute='0' + minute;
    var second=d.getSeconds();
    if(second<10)
        second='0' + second;
    
    return '' + d.getFullYear() + month + day + hour + minute + second;

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
	
	console.log(adress);

    replace_html_div_inner(target_div_id, adress);
    
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
 
 
 // For heights of carousel
function carouselNormalization() {
	var items = $('.auto_height_resize .carousel-item'), //grab all slides
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

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " ;
        if(obj[i] !== null && typeof obj[i] === 'object')
            out += dump(obj[i]);
        else
            out += obj[i];
        out += "\n";
    }
    
    return out;
}

$(document).ready(function() {
	// console.log( "ready!" );
    carouselNormalization();

// For filter droplist (searchable droplist)
  $(".droplistSearch").on("keyup", function() {
    var value = $(this).val().toLowerCase();
	var droplist_id = $(this).attr('id') + "_droplist";
    $("#" + droplist_id + " a").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

});