<?php
	
	$mysqli = new mysqli("localhost", "root", "password", "wms");
	/* check connection */
	if ($mysqli->connect_errno) {
		die ("Database connection failed: " . $mysqli->connect_error);
	}
?>
