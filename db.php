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

?>