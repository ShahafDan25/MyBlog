<?php 
include "db.php";

$firstPostStamp = 1;
function populateBlog($direction) {
    if($firstPostStamp != NULL) $sql = "SELECT b.ID, b.Stamp FROM BlogComments b WHERE active = 1 ORDER BY Stamp DESC LIMIT 12;";
    else if($direction == "prev") $sql = "SELECT b.ID, b.Stamp FROM BlogComments b WHERE active = 1 AND b.Stamp < '".$firstPostStamp."' ORDER BY Stamp ASC LIMIT 12;";
    else if($direction == "next") $sql = "SELECT b.ID, b.Stamp FROM BlogComments b WHERE active = 1 AND b.Stamp > '".$firstPostStamp."' ORDER BY Stamp DESC LIMIT 12;";
    echo "\n".$sql."\n";
    $c = connDB(); 
    $s = $c -> prepare($sql);
    $s -> execute();
    $data = "";
    $setFirstStamp = false;

    while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
        if(!$setFirstStamp) {
            $firstPostStamp = $r['Stamp'];
            $setFirstStamp = true;
        }
        $data .= $r['ID']."\t";
    }
    return $data;
}

echo "NULL    -      ".populateBlog(NULL)."\n\n";
echo "PREV    -      ".populateBlog("prev")."\n\n";
echo "NEXT    -      ".populateBlog('next')."\n\n";

?>