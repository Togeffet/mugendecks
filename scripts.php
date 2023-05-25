<?php
session_start();
require '/unimportant.php'; // Important file outside webserver

// Database connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, "jmdict");
$conn->set_charset("utf8mb4");

?>
