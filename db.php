<?php
    function connDB() {
        // FOR LOCAL HOST ON MY MACHINE
        $username = "root";
        $password = "MMB3189@A";
        $dsn = 'mysql:dbname=Shahafs;host=127.0.0.1;port=3306socket=/tmp/mysql.sock';
        try {$conn = new PDO($dsn, $username, $password);}
        catch(PDOException $e) {return connDB();}
        return $conn;


        // FOR 000WEBHOSTING ON THE WEB
        // $servername = "localhost";
        // $username = "id14291323_shahaf";
        // $password = "MMB3189@Sdan";
        // $database = "id14291323_shahafster";
        // try { 
        //     $conn = new PDO("mysql:host=".$servername.";dbname=".$database, $username, $password);
        //     $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // }
        // catch (PDOException $e) {
        //     echo "Database Connection Failed";
        //     return null;
        // }
        // return $conn;
    }


    function noteVersion() {
        /*
        1.0 
            release blog: I can upload text and people can view it
        1.5
            I can now upload images too
        2.0 
            People can now like the posts and login as members or as guests
        3.0
            Tag recognition, quicker load time by loading less posts per page




        */
    }
?>