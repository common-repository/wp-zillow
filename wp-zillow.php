<?php
/*
Plugin Name: WP Zillow API
Plugin URI: http://www.ultimateidx.com
Description: <strong>WP Zillow 1.0.1 </strong> -- This plugin was created to assist real estate professionals with the marketing of their services via their blog.  By providing Zillow Demographic and Zillow Chart features in Posts you write for neighborhoods and cities, you increasing the usability of your content and provide a more informative blog post for your visitors.  This plugin is fully template driven for maximum usage across many different types of theme designs. The WP Zillow plugin requires you to modify the main plugin file for this release. You can get your <a href="http://www.zillow.com/howto/api/APIOverview.htm" title="Zillow API" target="_blank">ZILLOW API Key</a> which you add below.
Author: Mack McMillan UltimateIDX
Version: 1.0.1
Author URI: http://www.ultimateidx.com/wp-zillow/
*/

$wpzillow_version = '1.0.1';
//////////////////////////////////////////////////
/* Set the action to show the main options page */
//////////////////////////////////////////////////


//////////////////////////////
/* admin_menu hook function */
//////////////////////////////
add_action('admin_menu', 'show_zillow_option');
add_action('activate_wp-zillow/wp-zillow.php', 'zillow_install');
add_filter('the_content', 'zillow_check_content');
function zillow_install()
{
    global $wpdb;
    $table = $wpdb->prefix."zillow";
    $structure = " CREATE TABLE $table (
					`id` INT NOT NULL AUTO_INCREMENT ,
					`template_name` VARCHAR(255) NOT NULL,
					`template_file` VARCHAR(250) NOT NULL,
					`street` VARCHAR(255) NOT NULL,
					`city` VARCHAR(255) NOT NULL,
					`state` VARCHAR(255) NOT NULL,
					`postcode` VARCHAR(255) NOT NULL,
					`created_at` DATETIME NOT NULL,
					PRIMARY KEY ( `id` )
					) ENGINE = MYISAM ";
    $wpdb->query($structure);
}

function show_zillow_option() {
//add on options page a link for our addon's admin
//add_options_page will make to show up our admin on the options tag on WP
if (function_exists('add_options_page')) {
	add_options_page("WP Zillow v{$wpfeatured_version} - Main", 
	"WP Zillow", 8, "wp-zillow", 'zillow_admin_options');
	}		
}

/////////////////////////////////////////////////
/* update the search criterias in the database */
/////////////////////////////////////////////////
function zillow_update_search_criterias()
	{
		global $wpdb;
	    $table = $wpdb->prefix."zillow";
	$template_number=null;
	if(isset($_POST['template_number']) && $_POST['template_number'] > 0)
		$template_number=$_POST['template_number'];
		$template_file = mysql_escape_string($_POST['template']);
		$template_name = mysql_escape_string($_POST['template_name']);
		$street = mysql_escape_string($_POST['street']);
		$city = mysql_escape_string($_POST['city']);
		$state = mysql_escape_string($_POST['state']);
		$postcode = mysql_escape_string($_POST['postcode']);
		if($template_number){
			$sql = "UPDATE $table SET template_name = '$template_name', template_file = '$template_file', street='$street', city='$city', state='$state', postcode='$postcode' WHERE id = $template_number";
			$wpdb->query($sql);
			echo '<div id="message" class="updated fade">';
            echo '<p>Data Updated</p>';
            echo '</div>';
			
		}else{
			$sql = "INSERT INTO $table SET template_name = '$template_name', template_file = '$template_file', street='$street', city='$city', state='$state', postcode='$postcode', created_at = '".date("Y-m-d H:i:s")."'";
			$wpdb->query($sql);
			$template_number = mysql_insert_id();
			echo '<div id="message" class="updated fade">';
            echo '<p>Template created, unique template tag is: {zillow_'.$template_number.'}</p>';
            echo '</div>';
		}
		return $template_number;
}

function zillow_print_menu()
{
		global $wpdb;
	    $table = $wpdb->prefix."zillow";
	$query = "SELECT * FROM $table ORDER BY created_at DESC";
	$items =$wpdb->get_results($query);

		if ($items) :
			
			echo '<table class="widefat" style="width:700px;">
				<thead>
				<tr>
			    <th scope="col" style="width: 50px; text-align: center;">ID</th>
			    <th scope="col" style="width: 250px; text-align: left;">Template Name</th>
			    <th scope="col" style="width: 200px; text-align: left;">Template Tag</th>
			    <th scope="col" style="width: 150px; text-align: left;">Created At</th>
				<th scope="col" style="width: 50px; text-align: left;">Action</th>
			  	</tr>
				</thead><tbody id="the-list">
				<tr id="page-10" class="alternate">';
			
			foreach($items as $item){		
				
				echo  '<td scope="row" style="text-align: center">'.$item->id.'</td>
					<td scope="row" style="text-align: left;"><a class="edit" href="?page=wp-zillow&edit_id='.$item->id.'">'.$item->template_name.'</a></td>
					<td style="text-align: left;">{zillow_'.$item->id.'}</td>
					<td style="text-align: left;">'.$item->created_at.'</td>
					<td style="text-align: left;"><a href="?page=wp-zillow&delete_id='.$item->id.'">Delete</a></td></tr>';
			}
			echo "
		  </tbody>
		</table>";			
		endif;
}

	////////////////////////////////
	/* used when displaying admin */
	////////////////////////////////
	function zillow_admin_options()
		{
		if($_GET['delete_id'])
			zillow_delete_template($_GET['delete_id']);
			
		if($_POST['form'] == 'sent')
			{
			$template_number = zillow_update_search_criterias();
			zillow_options_form();
			zillow_print_menu();
			}else
			{
			zillow_options_form($_GET['edit_id']);
			zillow_print_menu();
		}
	}

	//check the contents for our tags which we want
	function zillow_delete_template($id)
	{
		global $wpdb;
	    $table = $wpdb->prefix."zillow";
	$query = "DELETE FROM $table WHERE id=$id LIMIT 1";
	$wpdb->query($query);
	echo '<div id="message" class="updated fade">';
    echo '<p>Template ID: '.$id.' has been deleted.</p>';
    echo '</div>';	
	}

	///////////////////////////
	/* Look for trigger text */
	///////////////////////////
	function zillow_check_content($content) 
		{
		preg_match_all('/{zillow_([^{}]*?)}/',$content,$tags_found);
		$tags_found = $tags_found[1];
		foreach($tags_found as $tag)
		$content=str_replace("{zillow_".$tag."}",wp_template_show_zillow($tag),$content);
		return $content;
	//return $content;
	}

///////////////////////////////////
/* form for the search criterias */
///////////////////////////////////
function zillow_options_form($template_number = null) 
	{
		global $wpdb;
	    $table = $wpdb->prefix."zillow";
	
	$template='';
	//$folder_to_include ... Open Realty folder to include
    if($template_number){
	$sql = "SELECT * FROM $table WHERE id=$template_number";
	$res = $wpdb->get_row($sql);
	
	$street=$res->street;
	$city=$res->city;
	$state=$res->state;
	$postcode=$res->postcode;
	$template_file=$res->template_file;
	$template_name = $res->template_name;
	
	}
	$templates='<select name="template">';
	foreach(glob("../wp-content/plugins/wp-zillow/template/*.html") as $file)
		if($template_file=='')
			if($file=="../wp-content/plugins/wp-zillow/template/zillow.html")
				$templates.='<option value="'.substr($file,1).'" selected>'.substr($file,strlen("../wp-content/plugins/wp-zillow/template/"));
			else
				$templates.='<option value="'.substr($file,1).'">'.substr($file,strlen("../wp-content/plugins/wp-zillow/template/"));
		else
			if(substr($file,1)==$template_file)
				$templates.='<option value="'.substr($file,1).'" selected>'.substr($file,strlen("../wp-content/plugins/wp-zillow/template/"));
			else
				$templates.='<option value="'.substr($file,1).'">'.substr($file,strlen("../wp-content/plugins/wp-zillow/template/"));
	$templates.='</select>';

	echo '<div class="wrap">';
    echo '<h2>WP Zillow v-0.9.6</h2>';
  
echo '
<fieldset class="options">
<table width="700" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td colspan="4"><p style="font-size:12px"><strong>IMPORTANT SETTING DETAILS</strong> If you fail to enter the required fields marked by the asterisk* you could accidentally disable your blog. You must not leave any field empty until the next release which will allow for the exclusion of the address field.</p></td>
  </tr>
<tr><td colspan="4"><form action="?page=wp-zillow" method="post"><input type="submit" name="submit" value="Create new template" /></form><br/></td></tr>

<form method="post">
<tr>
    <td width="26%" align="right" valign="top"><strong><u>Template Name</u></strong>:&nbsp;</td>
    <td width="25%" align="left" valign="top">&nbsp;<input type="text" name="template_name" value="'.$template_name.'" /></td>
  </tr>  

<tr>
    <td width="40%" align="right" valign="top"><strong>Street</strong>:&nbsp;</td>
    <td width="60%" align="left" valign="top">&nbsp;<input type="text" name="street" value="'.$street.'" /></td>
  </tr>
  <tr>
    <td valign="top" align="right"><strong>City</strong>:&nbsp;</td>
    <td valign="top" align="left">&nbsp;<input type="text" name="city" value="'.$city.'" /></td>
  </tr>
  <tr>
    <td valign="top" align="right"><strong>State</strong>:&nbsp;</td>
    <td valign="top" align="left">&nbsp;<input type="text" name="state" value="'.$state.'" /></td>
  </tr>
  <tr>
    <td valign="top" align="right"><strong>Postcode</strong>:&nbsp;</td>
    <td valign="top" align="left">&nbsp;<input type="text" name="postcode" value="'.$postcode.'" /></td>
  </tr>
  <tr>
    <td colspan="2" align="right" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right"><strong>Template to use:</strong> &nbsp;</td>
    <td valign="top" align="left">&nbsp;<label>'.$templates.'</label></td>
  </tr>
<tr>
	<td colspan="2" align="right" valign="top">&nbsp;      <input type="submit" name="submit" value="Submit" /></td>
</tr>
</table>
<p style="display:block; border:1px solid #333; background:#eee; padding:5px; width:700px;">
Below are the existing templates you currently have available, you can edit or delete the existing Zillow instances here. Then simply past in your template tag like {zillow_1} into your post or page where you want the images to render.</p>
<input type="hidden" name="template_number" value="'.$template_number.'">
<input type="hidden" name="form" value="sent">
</form>
</fieldset>
';
echo '</div>';
}

/* ////////////////////////////////////////////////////////////////////
wp_template_show_featured_listings... 
use this in template to show the featured listings
param template_number ... it's the template number used on {featured_x}
//////////////////////////////////////////////////////////////////// */
function wp_template_show_zillow($template_number)
	{
	global $wpdb;
    $table = $wpdb->prefix."zillow";
	$number_of_listings=5;	
	$sql = "SELECT * FROM $table WHERE id=$template_number";
	$res = $wpdb->get_row($sql);
	$street = $res->street;
	$city = $res->city;
	$state = $res->state;
	$postcode = $res->postcode;
	$template_file=$res->template_file;
	$template_name = $res->template_name;
	$address = array('street'=>$street,'city'=>$city,'state'=>$state,'postcode'=>$postcode);
	require_once("zill.php");
	$zillow = new Zillow($address);
	$chart_image = $zillow->zillowGetChart();
	$region_chart_image = $zillow->zillowGetRegionChart();
	$demographic_data = $zillow->zillowGetDemographicData();
	$regionId = $zillow->zillowGetRegionId();
	$zillow_privacy_text = "&copy; Zillow, Inc., 2009. Use is subject to <a href='http://www.zillow.com/corp/Terms.htm'>Terms of Use</a>.";
	$zillow_privacy_link = "<a href='http://www.zillow.com/local-info/".strtoupper(substr($state,0,2))."-".str_replace(' ','-',$city)."-home-value/r_".$regionId."/' target='_blank'>See ".$city." Home Values at Zillow.com</a>";
	
/* ////////////////////////////////////////////////////////////////////////////
if we have a template defined for that tag, meaning a file with name
featured_x.html, where x is the tag number, use that file... 
if we don't have one, use the default one to have a template for a tag number,
just add in the template folder of the addon a file called featured_x.html, 
where x is the tag number 
//////////////////////////////////////////////////////////////////////////// */
//add the user's option
	if($template_file!='')
		if(file_exists($template_file))
			$template=file_get_contents($template_file);
		else
			$template=file_get_contents("wp-content/plugins/wp-zillow/template/zillow.html");
		else
		if(file_exists("wp-content/plugins/wp-zillow/template/zillow_".$template_number.".html"))
			$template=file_get_contents("wp-content/plugins/wp-zillow/template/zillow_".$template_number.".html");
		else
			$template=file_get_contents("wp-content/plugins/wp-zillow/template/zillow.html");
	$template=str_replace("{zillow_search_address}", $street.', '.$city.' '.$state.' '.$postcode, $template);
	$template=str_replace("{zillow_chart_image}", "<img src='$chart_image' border='0'>", $template);
	$template=str_replace("{zillow_region_chart_image}", "<img src='$region_chart_image' border='0'>", $template);
	$template=str_replace("{zillow_demographic_data}", "$demographic_data", $template);
	$template=str_replace("{zillow_privacy_text}", "$zillow_privacy_text<br/>$zillow_privacy_link", $template);
	
	return $template;
	}
?>