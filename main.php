<?php
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start(); // start session
    }
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
        echo editComment($_POST['comment'], $_POST['commentid']);
    }

    if($_POST['message'] == "populate-comments-tomanage") {
        echo populateManagementTable("first");
    }

    if($_POST['message'] == "movepage-manage-comments") {
        echo populateManagementTable($_POST['goto']);
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

    if($_POST['message'] == "add-blog-comment") {
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
        echo populateBlog("first");
    }

    if($_POST['message'] == "move-posts") {
        echo populateBlog($_POST['goto']);
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

    if($_POST['message'] == "populate-post-likes") {
        // echo populateBlogLikes($_POST['postid']);
        echo "code messed up.... too lazy to fix atm";
    }

    if($_POST['message'] == "delete-user") {
        deleteUser($_POST['userid'], $_POST['first'], $_POST['last']);
    }

    if($_POST['message'] == "get-current-cookie-user") {
        if(isset($_COOKIE['shahafster-user-firstname'])) echo $_COOKIE['shahafster-user-firstname'].' '.$_COOKIE['shahafster-user-lastname'];
        else echo "nouser";
    }

    if($_POST['message'] == "login-user") {
        echo loginUser($_POST['firstname'], $_POST['lastname'], $_POST['pin'], $_POST['post']);
    }

    function loginUser($first, $last, $pin, $post) {
        $c = connDB(); //set connection
        $sql = "SELECT Stamp FROM Visitors WHERE FirstName = '".$first."' AND LastName = '".$last."' AND ID = ".$pin.";";
        $s = $c -> prepare($sql);
        $s -> execute();
        if($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            //TODO mark login in login history table I will later build
            
            // set cookies because apparently they arent set or were deletd from that device
            setcookie("shahafster-user-id", $pin, time() + 60*60*24*365*25); //set cookie to 25 years
            setcookie("shahafster-user-firstname", $first, time() + 60*60*24*365*25); //set cookie to 25 years
            setcookie("shahafster-user-lastname", $last, time() + 60*60*24*365*25); //set cookie to 25 years
            
            //like posts
            $sql = "INSERT INTO Likes (Comment_ID, Visitors_ID, Visitors_LastName, Visitors_FirstName, Stamp) VALUES (".$postid.", ".$pin.", '".$last."', '".$first."', NOW());";
            $c -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $c -> exec($sql);

            $c = null; //close connection
            return "loggedin";
        }
        else return "notfound";
    }

    function deleteUser($id, $first, $last) {
        $c = connDB(); // set connection

        $sql = "DELETE FROM Likes WHERE Visitors_ID = ".$id." AND Visitors_FirstName = ".$first." AND Visitors_LastName = ".$last.";";
        $sql .= "DELETE FROM Visitors WHERE ID = ".$id." AND FirstName = ".$first." AND LastName = ".$last.";";
        try {   
            $c -> prepare($sql) -> execute();
        } catch(PDOException $e) {
            return "false";
        }
        $c = null; //close connection
        return "true";
    }

    function editComment($comment, $id) {
        $c = connDB(); //set connection
        $sql = "UPDATE BlogComments SET Text = '".$comment."' WHERE ID = ".$id.";";
        $c -> prepare($sql) -> execute();
        $c = null; //forget connection
        return $comment;
    }

    function populateBlogLikes($postid) {
        $c = connDB(); //set connection

        $sql = "SELECT * FROM Likes WHERE Comment_ID = ".$postid.";";
        try {
            $s = $c -> prepare($sql);
            $s -> execute();
            $data = '';
            $found = false;
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
                $data .= '<h5 class = "user-who-liked"> '.$r['FirstName'].' '.$r['LastName'].' | @ '.$stamp.' </h5>';
            }

        } catch(PDOException $e) {
            return "false";
        }
        
        if($found) return  "Nobody liked this post yet...";
        $c = null; ///close connection
        return $data;
    }

    /// STILL NOT SURE WHAT TODO WITH THIS ONE
    function displayManageUsers(){
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
        $c = connDB(); //set connection
        
        $data = '';
        $sql = "SELECT ID, FirstName, LastName, Stamp FROM Visitors ORDER BY FirstName, LastName;";
        $s = $c -> prepare($sql);
        $s -> execute();
        while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
            $stamp = miltoregtime(substr($r['Stamp'], 11, 5))."&ensp;&ensp;&ensp;".$months[intval(substr($r['Stamp'], 5, 2))-1]." ".substr($r['Stamp'], 8, 2).", ".substr($r['Stamp'], 0, 4);
            $data .= '
                <div class = "user-row">
                    <button class = "user-action action-a" onclick = "deleteUser('.$r['ID'].');"><i class = "fa fa-times"></i></button>
                    <button class = "user-action action-b" onclick = ""><i class = "fa fa-power-off"></i></button>
                    <button class = "user-action action-c"><i class = "fa fa-heart"></i></button>
                    <h3 class = "user-name"><strong>'.$r['FirstName'].' '.$r['LastName'].'</strong> &emsp;&emsp; [ '.$r['ID'].' ]</h3>
                    <p class = "stamp"> '.$stamp.' </p>
                    <input type = "hidden" id = "user-manage-info-firstname-'.$r['ID'].'" value = "'.$r['FirstName'].'">
                    <input type = "hidden" id = "user-manage-info-lastname-'.$r['ID'].'" value = "'.$r['LastName'].'">
                </div>
                ';
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
        if($s -> fetch(PDO::FETCH_ASSOC)) return "accountexists";
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
        return "successSetAcount";
    }

    function populateBlog($postDisp) {
        $postsToLoad = 20; //IF I WANT TO CHANGE THE AMOUNT OF POSTS LOADED PER PAGE <<<<
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
        $c = connDB();
        if($postDisp == "first") {
            if(session_status() !== PHP_SESSION_ACTIVE) session_start(); // start session
            $sql = "SELECT COUNT(ID) FROM BlogComments";
            $s = $c -> prepare($sql);
            $s -> execute();
            $r = $s -> fetch(PDO::FETCH_ASSOC);
            $_SESSION['postCount'] = $r['COUNT(ID)'];
        }
        else if($postDisp == "prev") {
            if ($_SESSION['postCount'] < $postsToLoad) return "earliestPost";
            else $_SESSION['postCount'] -= $postsToLoad;
        }
        else if($postDisp == "next") { 
            $sql = "SELECT COUNT(ID) FROM BlogComments";
            $s = $c -> prepare($sql);
            $s -> execute();
            $r = $s -> fetch(PDO::FETCH_ASSOC);
            $totalPosts = $r['COUNT(ID)'];
            if($_SESSION['postCount'] + $postsToLoad <= $r['COUNT(ID)']) $_SESSION['postCount'] += $postsToLoad;
            else return "lastestPost";
        }
        $sql = "SELECT b.ID, b.Stamp, b.Text, b.FeelingRate, b.file FROM BlogComments b WHERE b.active = 1 AND b.ID <= ".$_SESSION['postCount']." ORDER BY b.ID DESC LIMIT ".$postsToLoad.";";
        $s = $c -> prepare($sql);
        $s -> execute();
        $data = "";
        if(isset($_COOKIE["shahafster-user-firstname"])) {
            while($r = $s -> fetch(PDO::FETCH_ASSOC)) {
                //get number of likes
                $sqlb = "SELECT COUNT(*) FROM Likes WHERE Comment_ID = ".$r['ID'].";";
                $sb = $c -> prepare($sqlb);
                $sb -> execute();
                if($rb = $sb -> fetch(PDO::FETCH_ASSOC)) $likes = $rb ['COUNT(*)'];
                else $likes = 0;
                //get if current user likes it or not
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
                $data .= "<div class = 'content'><p class = 'blog-comment-text'>".tagRecognize($r['Text'])."</p>";
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
                $data .= "<div class = 'content'><p class = 'blog-comment-text'>".tagRecognize($r['Text'])."</p>";
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

    function populateManagementTable($postDisp) {
        $postsToLoad = 20; //IF I WANT TO CHANGE THE AMOUNT OF POSTS LOADED PER PAGE <<<<
        $months = ["January", "February", "March", "April", "May", " June", "July", "August", "September", "October", "November", "December"];
        $c = connDB();
        if($postDisp == "first") {
            if(session_status() !== PHP_SESSION_ACTIVE) session_start(); // start session
            $sql = "SELECT COUNT(ID) FROM BlogComments";
            $s = $c -> prepare($sql);
            $s -> execute();
            $r = $s -> fetch(PDO::FETCH_ASSOC);
            $_SESSION['postCountForManagement'] = $r['COUNT(ID)'];
        }
        else if($postDisp == "prev") {
            if ($_SESSION['postCountForManagement'] < $postsToLoad) return "earliestPost";
            else $_SESSION['postCountForManagement'] -= $postsToLoad;
        }
        else if($postDisp == "next") { 
            $sql = "SELECT COUNT(ID) FROM BlogComments";
            $s = $c -> prepare($sql);
            $s -> execute();
            $r = $s -> fetch(PDO::FETCH_ASSOC);
            $totalPosts = $r['COUNT(ID)'];
            if($_SESSION['postCountForManagement'] + $postsToLoad <= $r['COUNT(ID)']) $_SESSION['postCountForManagement'] += $postsToLoad;
            else return "lastestPost";
        }
        $sql = "SELECT b.ID, b.Stamp, b.Text, b.FeelingRate, b.file, b.active FROM BlogComments b WHERE b.ID <= ".$_SESSION['postCountForManagement']." ORDER BY b.ID DESC LIMIT ".$postsToLoad.";";
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
            if($r['file']) $image = '<div class = "file-container"><div class = "file" style = "background-image: url(\'data:image/jpeg;base64,'.base64_encode($r['file']).'\')"></div></div>';
            else $image = '';
            if($r['active'] == 1) {
                $activatecss = 'option-1a'; //currently active
                $activateonclick = 'deactiavte('.$r['ID'].')';
            } 
            else {
                $activatecss = 'option-1b'; //deacitavted currently
                $activateonclick = 'actiavte('.$r['ID'].')';
            }
            $data .= '
                <div class = "blog-comment-manage">
                    <div class = "upper">
                        <button class = "action-btn '.$activatecss.'" onclick = "'.$activateonclick.'"><i class = "fa fa-power-off"></i></button>
                        <button class = "action-btn option-2" onclick = "allow_edit('.$r['ID'].');"><i class = "fa fa-pencil"></i></button>
                        <button class = "action-btn option-3"><i class = "fa fa-heart"></i> <p>'.$likes.'</p></button>
                        <p class = "emoji"> &#'.$r['FeelingRate'].'</p>
                        <p class = "stamp">'.$stamp.'</p>
                    </div>
                    <div class = "lower">
                        '.$image.'
                        <p class = "text" id = "manage-commet-text-'.$r['ID'].'"> '.tagRecognize($r['Text']).' </p>
                        <div class = "user-row-likes" id = "user-likes-'.$r['ID'].'"></div>
                    </div>
                </div>
            ';
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
        echo tagRecognize($r['Text']);
    }

    function tagRecognize($string) {
        $sepChars = array(",", ".", "(", ")", ":", "!");

        $hashtags = array();
        if (preg_match_all('/#([^\s]+)/', $string, $hashtags)) {
            for($item = 0; $item < count($hashtags[1]); $item++) {
                $string = str_replace("#".$hashtags[1][$item], "<a class = 'tag-linker'>"."#".$hashtags[1][$item]."</a>", $string);
            }
        } //Add to database too (need to modify database for hashtags per post)
        $nametags = array();
        if (preg_match_all('/@([^\s]+)/', $string, $nametags)) {
            for($item = 0; $item < count($nametags[1]); $item++) {
                $string = str_replace("@".$nametags[1][$item], "<a class = 'tag-linker'>"."@".$nametags[1][$item]."</a>", $string);
            }
        } //add to db   
        $placetags = array();
        if (preg_match_all('/=([^\s]+)/', $string, $placetags)) {
            for($item = 0; $item < count($placetags[1]); $item++) {
                $string = str_replace("=".$placetags[1][$item], "<a class = 'tag-linker'>"."<i class = 'fa fa-map-marker'></i>".$placetags[1][$item]."</a>", $string);
            }
        } // add to db
        return $string;
    }

    // TODO: Fix Time Stamps displayal
    // TODO: Display name of whoever is signed in
    // TODO: ReTweet Button, connect to twitter
    // TODO: Restyle the manage posts section so it has an upper bar too:
        // TODO: in tht upper bar, by clicking the likes button you can see who liked that post
        // TODO: also options to edit file, add file, remove file, edit bitmoji, edit text, de/reactivate post
    // TODO: text in label for input file
    // TODO: add option to enter ' in my posts
    // TODO : Display in manage comments who liked that comment
    // TODO: search bar
    // TODO : create login history for visitors
    // TODO: Login history
    // TODO: Individual Post like history
?>