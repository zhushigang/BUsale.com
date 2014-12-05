<?php
require_once 'SearchController.php';

$keyword = $_POST['search'];
if ($keyword===""){
	$keyword = " ";
}
$search = new SearchController();
$images = json_decode($search->getInstagramImages($keyword),true);


$doc = new DOMDocument();
$html_string = file_get_contents("result.html");
$doc->loadHTML($html_string);
$xpathsearch = new DOMXPath($doc);
$nodes = $xpathsearch->query('//div[contains(@id,"content")]');



$str = '<div class="row 150%">';
$counter = 1;
foreach($images as $image){
	$str.=create_html($image["url"],$image["user"],$image["caption"]);
	if($counter%3==0){
		$str .= '</div>';
		$str .= '<div class="row 150%">';
	}
	$counter ++;
}
$str.='</div>';

foreach($nodes as $node) {
	$newnode = $doc->createDocumentFragment();
	$newnode->appendXML($str);
	$node->appendChild($newnode);
	break;
}


function create_html($url,$user,$caption){
	$str = '<div class="4u">
									<section class="box">
										<header>
											<h2><a href="http://instagram.com/'.$user.'" target="_blank">'.$user.'</a></h2>
										</header>
										<a href="#" class="image featured"><img src="'.$url.'" alt="" /></a>
										<p>'.$caption.'</p>
									</section>
								</div>';
	
	return $str;
}





echo $doc->saveHTML();
?>