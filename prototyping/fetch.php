<?php
/*
Filename: fetch.php
Description: API interface and front end display script for prototype 1
Created by: Shigang Zhu
Last modified by: Shigang Zhu
*/
// this file is modified base on this one found on stackoverflow: 
// http://stackoverflow.com/questions/18458038/how-to-use-instagram-api-to-fetch-image-with-certain-hashtag

function callInstagram($url)
{
$ch = curl_init();
curl_setopt_array($ch, array(
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => 2
));
$result = curl_exec($ch);
curl_close($ch);
return $result;
}
function display($url)
{
	$inst_stream = callInstagram($url);
	$results = json_decode($inst_stream, true);
	//Now parse through the $results array to display your results... 
	foreach($results['data'] as $item){
		$image_link = $item['images']['low_resolution']['url'];
		$image_caption = $item['caption']['text'];
		$image_owner = $item['user']['username'];
		global $_POST, $found;
		$s = $_POST['search'];
		if(strpos($image_caption,$s)!==false){
			$found = true;
			echo '<img src="'.$image_link.'" />';
			echo '<p>'.$image_owner.'</p>';
			echo '<p>'.$image_caption.'</p>';
		}
	}
	
	$image_older_url = $results['pagination']['next_url'];
	return $image_older_url;
}
$tag = $_POST['tag'];
$client_id = "e646dc91d9884287b59a363611990fce";
$url = 'https://api.instagram.com/v1/tags/'.$tag.'/media/recent?client_id='.$client_id;
$url = display($url);
for ($x=0; $x<=10; $x++) {
  $url = display($url);
} 
?>