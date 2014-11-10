<?php
/*
Filename: convert.php
Description: the image class which represents an image object and a method to convert a large Array
				into many image objects
Created by: Guanchen Zhang
Last modified by: Shigang Zhu
*/
// This is the object representing an image in instagram
class Image
{
	var $image_id;
	var $url;
	var $caption;
	var $user;
	
	function Image($image_id,$url,$caption,$user)
	{
		$this->$image_id = $image_id;
		$this->$url = $url;
		$this->$caption = $caption;
		$this->$user = $user;
	}
	
	
}

function convert($jsonArray){
	$images = array();
	foreach($results['data'] as $item){
		$image_link = $item['images']['low_resolution']['url'];
		$image_caption = $item['caption']['text'];
		$image_owner = $item['user']['username'];
		$image_id = $item['id'];
		$image = new Image($image_id,$image_link,$image_caption,$image_owner);
		array_push($images,$image);
	}
	
	return $images;
}


?>