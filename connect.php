<?php 
	$connection = new mysqli('localhost', 'root','','dbtrippilot');
	
	if (!$connection){
		die (mysqli_error($mysqli));
	}
		
?>