<?php

$servername="";
$username="";
$password="";
$database="fog";
$DHCP_Service_Sleep_Time=0;
$DHCP_To_Use="";
$Current_DHCP_Checksum="";
$New_DHCP_Checksum="";




//start loop.
while(1) {
	
	// Sleep.
	sleep($DHCP_Service_Sleep_Time);


	// Create connection
	$link = new mysqli($servername, $username, $password, $database);
	// Check connection
	if ($link->connect_error) {
        	// Couldn't establish a connection with the database.
        	die("Error");
	}



	//Get sleep time.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_Service_Sleep_Time' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$DHCP_Service_Sleep_Time = $row["settingValue"];
		}
	}
	

	//Get DHCP Config file.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_To_Use' LIMIT 1";
        $result = $link->query($sql);
        if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                        $DHCP_To_Use = $row["settingValue"];
                }
        }





	//Build Global Options into file.
	
		//select Data.






	//Build Subnets.
		//Build the subnet's classes.
		//select data.






	//Build Reservations.
		//select data.



















	// Write commands for this user, for this setting, to the setting's file.
	$file = $PathToSMBShares . $JaneSettingsNickName . ".ps1";
	if (file_exists($file)) {
		$current = file_get_contents($file);
		$current = $current . $ThisCOMMAND;	
	} else {
		$current = $ThisCOMMAND;
	}
	file_put_contents($file, $current);


//end loop.
}
?>
