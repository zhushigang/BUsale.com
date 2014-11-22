<?php
include 'Database.php';
$db123 = new Database();
$images=$db123->fetchAll("Prototype1");
foreach($images as $image){
	if(strpos($image[caption],$_POST['search'])!==false){
		echo '<img src="'.$image[url].'" />';
		echo '<p>'.$image[user].'</p>';
		echo '<p>'.$image[caption].'</p>';
	}
}
?>