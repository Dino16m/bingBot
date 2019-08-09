<?php 
/**
*Plugin Name: BingBot
*Plugin URI: https://www.uzucorp.com
*Description: This plugin submits the URI of any post you submit to Bing bot
*Version: 1.0
*Author: hiz_dynasty
*Author URI: hiz_dynasty.uzucorp.com
**/
defined('ABSPATH') or die('hahahahahahaha');
$DATA_ARRAY = array();
$init_error = array();
init();
if (!defined('API_KEY')) {
	define('API_KEY', 'b0139e4615044b6ea9992c836300e8fa');
}
get_post_request();
function init()
{
	global $DATA_ARRAY, $init_error;
	$get_api_key = get_option('API_KEY', false);
	if ($get_api_key != false) {
		define('API_KEY', $get_api_key);
	}
	else{
		if (!defined('INIT')) {
			define('INIT', false);
		}
		$error = ['API_KEY is not set please set it in the plugins panel under BingbotAdmin'];
		$init_error = array_merge($init_error, $error);
		echo "null confirmed";
	}
	$home = get_home_url();
	define('HOME_URI', $home);
	$get_data_arr = get_option('DATA_ARRAY', false);
	if($get_data_arr != false){
		$DATA_ARRAY = unserialize($get_data_arr);
	}

	if (!defined('INIT')) {
			define('INIT', true);
		}
}

add_action('admin_menu', 'add_pages');
function add_pages()
{
	add_plugins_page('Bing Bot by Uzucorp', 'BingBot','manage_options', 'uzu-bing-bot', 'add_plugin_view');
	add_plugins_page('BingBot admin page', 'BingbotAdmin', 'manage_options', 'uzu-bing-bot-admin','add_admin_view');
}

function add_plugin_view()
{
	global  $DATA_ARRAY;
	if (isset($DATA_ARRAY) && !empty($DATA_ARRAY)) {
		show_data();
	}
	else{
		echo "<div>There are no records for submitted index.</div>";
	}
}

function add_admin_view()
{
	add_settings_view();
	manual_index_view();
}


add_action('publish_post','on_publish', 10, 2);
function on_publish($id, $post)
{
	$post_uri = get_permalink($id);
	submit_site_uri($post_uri, 'auto');
}

function init_error($type='manual')
{
	if ($type =='auto') {
		return;
	}
	global  $init_error;
	if (empty($init_error)) {
		echo "<div>There is an error with your setup, please check that every thing has been set in the BingbotAdmin menu</div>";
	}
	foreach ($init_error as $error) {
		echo "<span> $error </span><br>";
	}
}

function submit_site_uri($post_uri, $type)
{
	if(defined('INIT') && INIT == false)
	{
		return init_error($type);
	}
	$apikey = API_KEY;
	$query = "https://ssl.bing.com/webmaster/api.svc/json/SubmitUrl?apikey=$apikey";
	$body = array("siteUrl"=>HOME_URI, "url"=>$post_uri);
	$body = json_encode($body);
	$headers = Array('Content-Type: application/json');
	$ch = curl_init($query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $status === 200 ? indexed($post_uri, true, $type) : indexed($post_uri, false, $type);
}

function show_data()
{	
	global $DATA_ARRAY;
	$variable = $DATA_ARRAY;
	echo "<table>";
	echo "<tr>";
	echo "<th>Post Link </th>";
	echo "<th>Date published</th>";
	echo "<th>Submission type</th>";
	echo "<th>Indexed</th>";
	echo "</tr>";
	foreach ($variable as $value) {
		echo "<tr>";
		echo "<td>".$value['POST_LINK']."</td>";
		echo "<td>".$value['PUBLISHED_ON']."</td>";
		echo "<td>".$value['TYPE']."</td>";
		echo "<td>".$value['INDEXED']."</td>";
		echo "</tr>";
	}
	echo "</table>";
}

function add_settings_view()
{
?>
<div>
	<form name="BingBot-settings" method="Post" action="">
		<input type="hidden" name="settings" value=1>
		API KEY: <input type="text" name="api_key" style="width: 50%" placeholder="please leave empty if already set"><br>
		<p class="submit">
			<input type="submit" name="" class="button-primary" value="Save changes">
		</p>
	</form>
</div><br>
<?php
}

function manual_index_view()
{
?>
<div>
	<form name="BingBot-manual-index" method="post">
		<input type="hidden" name="index" value="1">
		POST LINK: <input type="text" style="width: 50%" name="post_uri" placeholder="this is the permalink generated after publishing the post">
		<p class="submit">
			<input type="submit" name="" class="button-primary" value="Save changes">
		</p>
	</form>
</div>
<?php
}

function initiate_manual_index()
{
	$post_uri = $_POST['post_uri'];
	if ($post_uri == '' || !$post_uri) {
		echo "you submitted an empty string for the post link";
		return; 
	}
	submit_site_uri($post_uri, 'manual');
}

function modify_settings()
{
	$api_key = $_POST['api_key'];
	if ($api_key != '' && $api_key) {
		$define('API_KEY_UPDATE', $api_key);
	}
	
}

function get_post_request()
{
	if (isset($_POST['index']) && $_POST['index'] == 1) {
		initiate_manual_index();
	}
	if (isset($_POST['settings']) && $_POST['settings'] == 1) {
		modify_settings();
	} 
}

function indexed($post_uri, $status, $type)
{
	global $DATA_ARRAY;
	if($status === true){
		$POST_LINK = $post_uri;
		$PUBLISHED_ON = date('m/d/Y h:i a', time());
		$TYPE = $type;
		$INDEXED = 'YES';
		if ($type !== 'auto') {
			echo "<span> Successfully indexed </span>";
		}
	}
	if($status === false){
		$POST_LINK = $post_uri;
		$PUBLISHED_ON = date('m/d/Y h:i a', time());
		$TYPE = $type;
		$INDEXED = 'NO';
		if ($type !== 'auto') {
			echo "<span> Link not indexed please try again</span>";
		}
	}
	$child_array = array('POST_LINK'=>$POST_LINK, 'PUBLISHED_ON'=>$PUBLISHED_ON, 'TYPE'=>$TYPE, 'INDEXED'=>$INDEXED);
	$parent_array = array($child_array);
	$DATA_ARRAY = $DATA_ARRAY? array_merge($DATA_ARRAY, $parent_array) : $parent_array;
	shut_down();
}

function shut_down()
{
	global $DATA_ARRAY;
	if(defined('API_KEY') && !defined('API_KEY_UPDATE')){
		update_option('API_KEY', API_KEY);
	}
	if (defined('API_KEY_UPDATE')) {
		update_option('API_KEY', API_KEY_UPDATE);
	}

	if (isset($DATA_ARRAY)) {
		update_option('DATA_ARRAY', serialize($DATA_ARRAY));
	}
}
shut_down();
?>