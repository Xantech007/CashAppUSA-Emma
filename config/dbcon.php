<?php
//connection to mysql database

$host = "sql113.infinityfree.com";  //database host
$username = "if0_42072233";  //database user
$password = "MdgqQP9MTFdqUnS";    //database password
$database = "if0_42072233_Emma";  //database name

$con = mysqli_connect("$host","$username","$password","$database");

if(!$con)
{
    echo 'error in connection';
}




