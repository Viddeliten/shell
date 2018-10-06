<?php
/********************************************************/
/*		function: user_profile_custom_content			*/
/*														*/
/*		For echoing custom content in users profiles	*/
/*		you can put all code in function				*/
/*		or just make function calls						*/
/********************************************************/
function user_profile_custom_content($user_id)
{
	//Example: display some stats
	echo '<div class="row">
		<div class="col-lg-12">
			<table class="table">
				<tr>
					<th>'._("Registered").'</th>
					<td>'.date("Y-m-d H:i", strtotime(user_get_regdate($user_id))).'</td>
				</tr>
				<tr>
					<th>'._("Last logged in").'</th>
					<td>'.date("Y-m-d H:i", strtotime(user_get_lastlogin($user_id))).'</td>
				</tr>
			</table>
		</div>
	</div>';
}

/********************************************************************************/
/*		function: user_profile_edit_receive  			                        */
/*														                        */
/*		Handle the inputs you added in function user_profile_edit_inputs        */
/*      here and return TRUE if it went all right, otherwise FALSE				*/
/********************************************************************************/
function user_profile_edit_receive()
{
    return TRUE;
}
/********************************************************/
/*		function: user_profile_edit_inputs  			*/
/*														*/
/*		return html for any input fields you want to    */
/*      add in profile editing form!					*/
/********************************************************/
function user_profile_edit_inputs()
{
    return "";
}
?>