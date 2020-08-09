<?php 

$string = "Hello World! This is a #text with as many #hashtags as I could possible get. Let's #tryItOut now!";
$matches = array();
if (preg_match_all('/#([^\s]+)/', $string, $matches)) {
    for($item = 0; $item < count($matches[1]); $item++) {
        $string = str_replace("#".$matches[1][$item], "<a class = 'x'>"."#".$matches[1][$item]."</a>", $string);
    }
}
$matches = array();
if (preg_match_all('/@([^\s]+)/', $string, $matches)) {
    for($item = 0; $item < count($matches[1]); $item++) {
        $string = str_replace("#".$matches[1][$item], "<a class = 'x'>"."#".$matches[1][$item]."</a>", $string);
    }
}



echo $string."\n";

?>