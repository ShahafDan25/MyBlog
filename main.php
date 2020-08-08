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

        if(isset($_COOKIE['correct-pw'])) echo "cookiefound";
        else {
            $sql = "SELECT password FROM BlogDetails WHERE active = 1";
            $s = $c -> prepare($sql);
            $s -> execute();
            $r = $s -> fetch(PDO::FETCH_ASSOC);
            echo $r['password'];
        }
        
    }

    if($_POST['message'] == "login-cookie-start") {
        setcookie('correct-pw', true, time()+60*60*24*30); //cookie will expire after 30 days :(
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
        if(strlen($_FILES["attachment"]["tmp_name"]) > 1) { //file was added
            $file = addslashes(file_get_contents($_FILES["attachment"]["tmp_name"])); 
            $fileSize = $_FILES['attachment']['size'];
            $fileError = $_FILES['attachment']['error'];
            if($fileError === 0) {
                if($fileSize > 5000000) echo "<script>alert('File too large. Must 5MB or less.'); goBack(); </script>";
                else {
                    $time = date('Y-m-d').' '.date('H:i');
                    if (newComment($time, $_POST['content'], $_POST['rating-option'], $file)) echo '<script>alert("Comment Added!"); location.replace("index.html");</script>';
                    else echo '<script>alert("Something Went Wrong..."); location.replace("add.html");</script>';
                }
            }
            else echo "<script>alert.error('Something Went Wrong...'); goBack();</script>";
        } 
        else { //file was not added
            $time = date('Y-m-d').' '.date('H:i');
            if (newComment($time, $_POST['content'], $_POST['rating-option'], NULL)) echo '<script>alert("Comment Added!"); location.replace("index.html");</script>';
            else echo '<script>alert("Something Went Wrong..!."); location.replace("add.html");</script>';
        }
        
        
    }

    if($_POST['message'] == "populate-blog") {
        echo populateBlog();
    }

    if($_POST['message'] == "set-account") {
        echo setAccount($_POST['firstname'], $_POST['lastname'], $_POST['pin'], $_POST['post']);
    }

    if($_POST['message'] == "check-login-cookie") {
        if(!isset($_COOKIE["shahafster-user-firstname"])) echo "notfound";
        else echo likePost($_POST['postid']);
    }

    if($_POST['message'] == "update-likes-amount") {
        $c = connDB();
        $sqlb = "SELECT COUNT(*) FROM Likes WHERE Comment_ID = ".$_POST['postid'].";";
        $sb = $c -> prepare($sqlb);
        $sb -> execute();
        if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $likes = $rb ['COUNT(*)'];
        else $likes = 0;
        echo $likes;
    }

    if($_POST['message'] == "display-users") {
        echo displayManageUsers();
    }

    function displayManageUsers(){
        $c = connDB(); //set connection
        
        $data = '';
        $sql = "SELECT ID, FirstName, LastName, Stamp FROM Visitors ORDER BY FirstName, LastName;";
        $s = $c -> prepare($sql);
        $s -> execute();
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
            $data .= '
                <div class = "user-row">
                    <button class = "user-action action-a"><i class = "fa fa-times"></i></button>
                    <button class = "user-action action-b"><i class = "fa fa-power"></i></button>
                    <button class = "user-action action-c"><i class = "fa fa-heart"></i></button>
                    <p> '.$r['ID'].' </p>
                    <h3><strong>'.$r['FirstName'].' '.$r['LastName'].'</strong></h3>
                    <p class = "stamp"> '.$stamp.' </p>
                </div>';
        }
        $begin = '';
        
        $c = null; //close connection
        if(strlen($data) < 2) return "No Users Found...";
        else return $begin.$data;
    }

    function likePost($postid) {
        $c = connDB(); // set database connection
        $sqlb = "SELECT Stamp FROM Likes WHERE Comment_ID = ".$postid." AND Visitors_ID = ".$_COOKIE['shahafster-user-id']." AND Visitors_FirstName = '".$_COOKIE["shahafster-user-firstname"]."' AND Visitors_LastName = '".$_COOKIE["shahafster-user-lastname"]."';";
        $sb = $c -> prepare($sqlb);
        $sb -> execute();
        if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) { //unlike
            $sql = "DELETE FROM Likes WHERE Comment_ID = ".$postid." AND Visitors_ID = ".$_COOKIE['shahafster-user-id']." AND Visitors_FirstName = '".$_COOKIE["shahafster-user-firstname"]."' AND Visitors_LastName = '".$_COOKIE["shahafster-user-lastname"]."';";
            $c -> prepare($sql) -> execute();
        }
        else { //like
            $sql = "INSERT INTO Likes (Comment_ID, Visitors_ID, Visitors_LastName, Visitors_FirstName, Stamp) VALUES (".$postid.", ".$_COOKIE["shahafster-user-id"].", '".$_COOKIE["shahafster-user-lastname"]."', '".$_COOKIE["shahafster-user-firstname"]."', NOW());";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);
        }
        $c = null; //close connection
        return $_COOKIE["shahafster-user-firstname"];
    }

    function setAccount($first, $last, $pin, $postid) {
        $c = connDB();
        $sql = "SELECT Stamp FROM Visitors WHERE ID = ".$pin." AND FirstName = '".$first."' AND LastName = '".$last."';"; 
        $s = $c -> prepare($sql);
        $s -> execute();
        if($s -> fetch(PDO::FETCH_ASSOC)) return "false";
        else {
            $sql = "INSERT INTO Visitors (ID, FirstName, LastName, Stamp) VALUES (".$pin.", '".$first."', '".$last."', NOW());";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);
            $sql = "INSERT INTO Likes (Comment_ID, Visitors_ID, Visitors_LastName, Visitors_FirstName, Stamp) VALUES (".$postid.", ".$pin.", '".$last."', '".$first."', NOW());";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);
            // $cookiedata = array($first, $last, $pin);
            setcookie("shahafster-user-id", $pin, time() + 60*60*24*365*25); //set cookie to 25 years
            setcookie("shahafster-user-firstname", $first, time() + 60*60*24*365*25); //set cookie to 25 years
            setcookie("shahafster-user-lastname", $last, time() + 60*60*24*365*25); //set cookie to 25 years
        }
        $c = null; //close connection
        return "true";
    }

    function populateBlog() {
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
        $c = connDB();
        $sql = "SELECT ID, Stamp, Text, FeelingRate, file FROM BlogComments WHERE active = 1 ORDER BY Stamp DESC;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $data = "";
        if(isset($_COOKIE["shahafster-user-firstname"])) {
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                $sqlb = "SELECT COUNT(*) FROM Likes WHERE Comment_ID = ".$r['ID'].";";
                $sb = $c -> prepare($sqlb);
                $sb -> execute();
                if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $likes = $rb ['COUNT(*)'];
                else $likes = 0;
                $sqlb = "SELECT Stamp FROM Likes WHERE Comment_ID = ".$r['ID']." AND Visitors_ID = ".$_COOKIE['shahafster-user-id']." AND Visitors_FirstName = '".$_COOKIE["shahafster-user-firstname"]."' AND Visitors_LastName = '".$_COOKIE["shahafster-user-lastname"]."';";
                $sb = $c -> prepare($sqlb);
                $sb -> execute();
                if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $iconclass = "fa-heart";
                else $iconclass = "fa-heart-o";
                $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
                $data .= "<div class = 'blog-comment'>";
                $data .= '
                <div class = "details">
                    <p class = "feeling" id = "feeling-'.$r['ID'].'">&#'.$r['FeelingRate'].'</p>
                    &emsp;&emsp;
                    <div class = "likes">
                        <button class = "likes-btn" onclick = "likePost('.$r['ID'].');">
                            <i id = "like-icon-'.$r['ID'].'" class = "fa '.$iconclass.'"></i>
                        </button>
                        <p class = "likes-label" id = "likes-label-'.$r['ID'].'">'.$likes.'</p>
                    </div>
                    <p class = "time-stamp">'.$stamp.'</p>
                </div>';
                $data .= "<div class = 'content'><p class = 'blog-comment-text'>".$r['Text']."</p>";
                if($r['file']) {
                    $presentor = 'data:image/jpeg;base64,'.base64_encode($r['file']);
                    $data .= '<div class = "file-container"><div class = "file" style = "background-image: url(\''.$presentor.'\')"></div></div>';
                }
                $data .= "</div></div>";
            }
        }
        else {
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                $sqlb = "SELECT COUNT(*) FROM Likes WHERE Comment_ID = ".$r['ID'].";";
                $sb = $c -> prepare($sqlb);
                $sb -> execute();
                if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $likes = $rb ['COUNT(*)'];
                else $likes = 0;
                $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
                $data .= "<div class = 'blog-comment'>";
                $data .= '
                <div class = "details">
                    <p class = "feeling" id = "feeling-'.$r['ID'].'">&#'.$r['FeelingRate'].'</p>
                    &emsp;&emsp;
                    <div class = "likes">
                        <button class = "likes-btn" onclick = "likePost('.$r['ID'].');">
                            <i id = "like-icon-'.$r['ID'].'" class = "fa fa-heart-o"></i>
                        </button>
                        <p class = "likes-label" id = "likes-label-'.$r['ID'].'">'.$likes.'</p>
                    </div>
                    <p class = "time-stamp">'.$stamp.'</p>
                </div>';
                $data .= "<div class = 'content'><p class = 'blog-comment-text'>".$r['Text']."</p>";
                if($r['file']) {
                    $presentor = 'data:image/jpeg;base64,'.base64_encode($r['file']);
                    $data .= '<div class = "file-container"><div class = "file" style = "background-image: url(\''.$presentor.'\')"></div></div>';
                }
                $data .= "</div></div>";
            }
        }
        

        $c = null; //close connection
        return $data;
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
            echo '<script>console.log('.$e.');</script>';
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
        $sql = "SELECT ID, Stamp, Text, FeelingRate, active, file FROM BlogComments ORDER BY Stamp DESC;";
        $s = $c -> prepare($sql);
        $s -> execute();
        $data = "";
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $sqlb = "SELECT COUNT(*) FROM Likes WHERE Comment_ID = ".$r['ID'].";";
            $sb = $c -> prepare($sqlb);
            $sb -> execute();
            if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $likes = $rb ['COUNT(*)'];
            else $likes = 0;
            $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
            $data .= "<div class = 'blog-comment gillsans'>";
            $data .= "<p class = 'time-stamp'>".$stamp."</p>";
            $data .= "<p class = 'blog-comment-text'>";
            $data .= "<button class = 'action-btn edit-btn' onclick = 'allow_edit(".$r['ID'].")';><i class = 'fa fa-pencil'></i></button>";
            if($r['active'] == 1) $data .= "<button class = 'action-btn deactivate-btn' onclick = 'deactiavte(".$r['ID'].")'><i class = 'fa fa-power-off'></i></button>";
            else $data .= "<button class = 'action-btn activate-btn' onclick = 'activate(".$r['ID'].")'><i class = 'fa fa-power-off'></i></button>";
            $data .= "&#".$r['FeelingRate']."&emsp;&emsp;".$r['Text']."</p>";
            if($r['file']) {
                $presentor = 'data:image/jpeg;base64,'.base64_encode($r['file']);
                $data .= '<div class = "file-container"><div class = "file" style = "background-image: url(\''.$presentor.'\')"></div></div>';
            }
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

    // TODO: Fix Time Stamos displayal
    // TODO: UI SideBar change
    // TODO: Display name of whoever is signed in
    // TODO: ReTweet Button, connect to twitter
    // TODO: Create pages (to slow load time)
    // TODO: Restyle the manage posts section so it has an upper bar too:
        // TODO: in tht upper bar, by clicking the likes button you can see who liked that post
        // TODO: also options to edit file, add file, remove file, edit bitmoji, edit text, de/reactivate post
    // TODO: text in label for input file
    // TODO: On server: inputs are too long in registration form,
    // TODO: On server, label does not update when liked post (when registering, not when already registered)
    // TODO: on desk top, increase both height and width a bit of pictures
?>