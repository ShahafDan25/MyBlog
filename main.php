<?php
    include "db.php";
    $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
    //set time zone
    // date_default_timezone_set("America/Los_Angeles");
    if($_POST['message'] == "deactivate-comment") {
        $c = connDB();
        $sql = "UPDATE BlogComments SET active = 0 WHERE ID = ".$_POST['commentid'].";";
        $c -> prepare($sql) -> execute();

        $c = null; //close connection
        echo populateManagementTable();
    }

    if($_POST['message'] == "activate-comment") {
        $c = connDB();
        $sql = "UPDATE BlogComments SET active = 1 WHERE ID = ".$_POST['commentid'].";";
        $c -> prepare($sql) -> execute();

        $c = null; //close connection
        echo populateManagementTable();
    }

    if($_POST['message'] == "edit-comment") {
        $c = connDB(); //set connection
        $sql = "UPDATE BlogComments SET Text = '".$_POST['comment']."' WHERE ID = ".$_POST['commentid'].";";
        $c -> prepare($sql) -> execute();

        $c = null; //forget connection
        echo populateManagementTable();
    }

    if($_POST['message'] == "populate-comments-tomanage") {
        echo populateManagementTable();
    }

    if($_POST['message'] == "get-add-blog-pw") {
        $c = connDB();

        $sql = "SELECT password FROM BlogDetails WHERE active = 1";
        $s = $c -> prepare($sql);
        $s -> execute();
        $r = $s -> fetch(PDO::FETCH_ASSOC);
        echo $r['password'];
    }

    if($_POST['message'] == "change-pw") {
        $c = connDB();
        $sql = "UPDATE BlogDetails SET active = 0;";
        $c -> prepare($sql) -> execute();

        $sql = "SELECT MAX(ID)+1 FROM BlogDetails;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $max = $s -> fetchColumn();

        $sql = "INSERT INTO BlogDetails VALUES (".$max.", '".$_POST['pw']."', NOW(), 1);";
        $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $c -> exec($sql);

        echo "true";
    }

    if($_POST['message'] == "comment-text-byid") {
        echo commentTextById($_POST['commentid']);
    }

    if($_POST['message'] == "add-blog-comment") 
    {
        
        $file = addslashes(file_get_contents($_FILES["attachment"]["tmp_name"])); 
        $fileSize = $_FILES['attachment']['size'];
        $fileError = $_FILES['attachment']['error'];
        if($fileError === 0) {
            if($fileSize > 5000000) echo "<script>alertify.error('File too large. Must 5MB or less.'); goBack(); </script>";
            else echo updateProfilePicture($file);
        }
        else echo "<script>alertify.error('Something Went Wrong...'); goBack();</script>";
        $time = date('Y-m-d').' '.date('H:i');

        // echo "true";
        if (newComment($time, $_POST['content'], $_POST['rating-option'], $file)) echo '<script>alertify.success("Comment Added!"); location.replace("index.html");</script>';
        else echo '<script>alertify.message("Something Went Wrong..."); location.replace("add.html");</script>';
    }

    if($_POST['message'] == "populate-blog") {
        $c = connDB();
        $sql = "SELECT ID, Stamp, Text, FeelingRate FROM BlogComments WHERE active = 1 ORDER BY Stamp DESC;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $data = "";
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
            $data .= "<div class = 'blog-comment-container'><div class = 'blog-comment'>";
            // $data .= "";
            $data .= "<p class = 'blog-comment-text'>&#".$r['FeelingRate']."&emsp;&emsp;".$r['Text']."</p>";
            $data .= "</div>
                <div class = 'blog-like'>
                    <div style = 'display: block'>
                        <p class = 'time-stamp'>".$stamp."</p>
                    </div>
                    <div style = 'display: block'>
                        <button class = 'blog-not-liked-btn' id = 'not-liked-".$r['ID']."' onclick = 'likeComment(".$r['ID'].")' style = 'display: inline-block; color: red;'>
                            <i class = 'fa fa-heart-o'></i>
                        </button>
                        <button class = 'blog-liked-btn' id = 'liked-".$r['ID']."' onclick = 'dislikeComment(".$r['ID'].")' style = 'display: none; color: red;'>
                            <i class = 'fa fa-heart'></i>
                        </button>           
                        <p class = 'amount-likes' id = 'amount-likes'>3</p>
                    </div>
                </div>
            </div>";
        }

        $c = null; //close connection
        echo $data;
    }

    function newComment($time, $content, $rating, $file) {
        $c = connDB();

        $sql = "SELECT MAX(ID)+1 FROM BlogComments;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $max = $s -> fetchColumn();

        $sql = "INSERT INTO BlogComments VALUES (".$max.", '".$time."', '".$content."', ".$rating.", 1, '".$file."');";
        try {
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);
        } catch(PDOException $e) {
            return false;
        }
        return true;
    }

    function miltoregtime($time) {
        $add = "AM";
        $timeInt = intval(substr($time,0,strlen($time)-3).substr($time, strlen($time)-2,2));
        if($timeInt > 1159) $add = "PM";
        if($timeInt > 1259) $timeInt = $timeInt - 1200;
        return substr(strval($timeInt),0,strlen(strval($timeInt))-2).":".substr(strval($timeInt),strlen(strval($timeInt))-2,2)." ".$add;

    }

    function populateManagementTable() {
        $c = connDB(); 
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];

        $sql = "SELECT ID, Stamp, Text, FeelingRate, active FROM BlogComments ORDER BY Stamp DESC;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $data = "";
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
            $data .= "<div class = 'blog-comment gillsans'>";
            $data .= "<p class = 'time-stamp'>".$stamp."</p>";
            $data .= "<p class = 'blog-comment-text'>";
            $data .= "<button class = 'action-btn edit-btn' onclick = 'allow_edit(".$r['ID'].")';><i class = 'fa fa-pencil'></i></button>";
            if($r['active'] == 1) $data .= "<button class = 'action-btn deactivate-btn' onclick = 'deactiavte(".$r['ID'].")'><i class = 'fa fa-power-off'></i></button>";
            else $data .= "<button class = 'action-btn activate-btn' onclick = 'activate(".$r['ID'].")'><i class = 'fa fa-power-off'></i></button>";
            $data .= "&#".$r['FeelingRate']."&emsp;&emsp;".$r['Text']."</p>";
            $data .= "</div>"; //comment div
        }
        $c = null; //close connection
        echo $data; //to populate with ajax
    }

    function commentTextById($id){
        $c = connDB(); //set connection
        $sql = "SELECT Text FROM BlogComments WHERE ID = ".$id.";";
        $s = $c -> prepare($sql);
        $s -> execute();
        $r = $s -> fetch(PDO::FETCH_ASSOC);
        $c = null; //forget connection...
        echo $r['Text'];
    }
?>