<?php
// This is the methods used to cache data locally. 
/*
Filename: cache.php
Description: Background script which will be excuted periodicly to update the database
			with the data fetched from an instagram API
Created by: Shigang Zhu
Last modified by: Shigang Zhu
*/
include 'convert.php';
include 'Database.php';
require_once '/home/BUSaleCredentials/DatabaseCredentials.php';
require_once 'Blacklist.php';

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

function getJson($url)
{
	$inst_stream = callInstagram($url);
	$results = json_decode($inst_stream, true);

	//Now parse through the $results array to display your results... 
	/*
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
	*/
	$image_older_url = Null;
	echo var_dump(array_key_exists('next_url',$results['pagination']));
	if(array_key_exists('next_url',$results['pagination'])){
		$image_older_url = $results['pagination']['next_url'];
	}
	return array('older_url'=>$image_older_url, 'results' =>$results);
}
//$image_older_url=Null;

$tag = 'busale';
$client_id = "e646dc91d9884287b59a363611990fce";

$url = 'https://api.instagram.com/v1/tags/'.$tag.'/media/recent?client_id='.$client_id;
$res = getJson($url);
$json = array();
array_push($json,$res['results']);
$image_older_url = $res['older_url'];
//$json = getJson($url);
while($image_older_url!=Null){
	$res = getJson($image_older_url);
	$json2 = $res['results'];
	$image_older_url = $res['older_url'];
	array_push($json,$json2);
}
$mydate=getdate(date("U"));
echo "Updated on $mydate[hours]: $mydate[minutes] ,$mydate[weekday], $mydate[month] $mydate[mday], $mydate[year]\n";

// convert everything into images
// echo var_dump($json);
$images = convert($json);
echo "Objects created \n";
//echo var_dump($images);
// Query db to store data. 
$rows = array();
$i = 0;
// echo var_dump($images);
foreach ($images as $image)
{
	echo var_dump(blacklist($image));
	if(blacklist($image)){
		$rows[$i] = array('user' => $image->user, 'url' => $image->url, 'caption' => $image->caption, 'id' => $image->image_id);
		$i=$i+1;
		//echo "image added to rows array";
	}
}
//echo var_dump($rows);
$col_names = array('user', 'url', 'caption', 'id');
$credentials = DatabaseCredentials::get();
$db123 = new Database($credentials['db_name'], $credentials['db_host_address'], 
                                 $credentials['db_username'], $credentials['db_password']);
//$_rows = array();
//$_rows[] = array('user' => "phptest123", 'url' => "te", 'caption' => 'dra', 'id' => '1234');
//echo var_dump($_rows[0]);
//echo var_dump($rows[0]);
$db123->deleteRows("InstagramImages", array("1"=>"1"));
echo var_dump($rows);
$db123->insertRows("InstagramImages", $rows);
return $json;


function blacklist($image){
	$bl = Blacklist::get();
	foreach($bl as $b){
		if ($image->user==$b){
			return false;
		}
	}
	return true;
}
?>
