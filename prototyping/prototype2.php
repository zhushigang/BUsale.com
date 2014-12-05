<?php
require_once 'SearchController.php';

$keyword = $_POST['search'];
if ($keyword===""){
	$keyword = " ";
}
$search = new SearchController();
$images = json_decode($search->getInstagramImages($keyword),true);


//$page = file_get_html("result.html");
//echo gettype($page);
$doc = new DOMDocument();
$html_string = file_get_contents("result.html");
$doc->loadHTML($html_string);
$xpathsearch = new DOMXPath($doc);
$nodes = $xpathsearch->query('//div[contains(@class,"row 150%")]');
foreach($nodes as $node){
	//echo var_dump($node);
	//echo "<p></p>";
}



$str = "";
foreach($images as $image){
	$str.=create_html($image["url"],$image["user"],$image["caption"]);
}


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
											<h2>'.$user.'</h2>
										</header>
										<a href="#" class="image featured"><img src="'.$url.'" alt="" /></a>
										<p>'.$caption.'</p>
									</section>
								</div>';
	
	return $str;
}





echo $doc->saveHTML();
?>