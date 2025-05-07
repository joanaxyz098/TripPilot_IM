<?php 
	$connection = new mysqli('localhost', 'root','','trippilot');
	
	if (!$connection){
		die (mysqli_error($mysqli));
	}
		
?>