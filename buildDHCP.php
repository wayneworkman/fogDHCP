<?php

$servername="127.0.0.1";
$username="root";
$password="";
$database="fog";
$DHCP_Service_Sleep_Time=60;
$DHCP_To_Use="/etc/dhcp/dhcpd.conf";
$Current_DHCP_Checksum="";
$New_DHCP_Checksum="";
$New_File="";
$New_Line="\n";
$tmpFile = "$DHCP_To_Use.tmp";
$log = "/opt/fog/log/fogdhcp.log";
$TimeZone="UTC";
date_default_timezone_set($TimeZone);



//Function to write to the log.
function WriteLog($Message) {
	global $log;
	global $New_Line;
	$Now=date("Y-m-d  h:i:sa");
	if (file_exists($log)) {
		$current = file_get_contents($log);
		$current .= "$Now  $Message$New_Line";	
	} else {
		$current = "$Now  $Message$New_Line";
	}
	file_put_contents($log, $current);
}





// Create connection
$link = new mysqli($servername, $username, $password, $database);
// Check connection
if ($link->connect_error) {
        // Couldn't establish a connection with the database.
	WriteLog("Couldn't establish a connection with the database.");
        die("Error");
}






//start loop.
while(1) {




	//Clear out the contents of the New_File variable.
	$New_File = "";



	//Get the timezone.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'FOG_TZ_INFO' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$TimeZone = trim($row["settingValue"]);
			if ($TimeZone != "" ) {
				date_default_timezone_set($TimeZone);
			} else {
				WriteLog("The FOG_TZ_INFO only has white space in it. Default timezone of \"$TimeZone\" is set.");
			}
		}
	} else {
		WriteLog("Could not get the FOG_TZ_INFO (timezone) from the globalSettings table. Default timezone of \"$TimeZone\" is set.");
	}
	$result->free();




	//Get sleep time.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_Service_Sleep_Time' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$DHCP_Service_Sleep_Time = $tmp;
			} else {
				WriteLog("The DHCP_Service_Sleep_Time setting in the globalSettings table only has white space in it. Default sleep time \"$DHCP_Service_Sleep_Time\" is set.");
			}
		}
	} else {
		WriteLog("Could not get the DHCP_Service_Sleep_Time from the globalSettings table. Default sleep time \"$DHCP_Service_Sleep_Time\" is set.");
	}
	$result->free();





	//Get the logs path.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'SERVICE_LOG_PATH' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$log = $tmp . "fogdhcp.log";
			} else {
				WriteLog("The SERVICE_LOG_PATH setting in the globalSettings table only has white space in it. Default log \"$log\" is set.");
			}
		}
	} else {
		WriteLog("Could not get the SERVICE_LOG_PATH from the globalSettings table. Default log \"$log\" is set.");
	}
	$result->free();

	



	//Get DHCP Config file.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_To_Use' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$DHCP_To_Use = $tmp;
			} else {
				WriteLog("The DHCP_To_Use setting in the globalSettings table only has white space in it. Default file \"$DHCP_To_Use\" is set.");
			}
		}
	} else {
		WriteLog("Could not get the DHCP_To_Use fro mthe globalSettings table. Default file \"$DHCP_To_Use\" is set.");
	}
	$result->free();




	//Build Global Options into file.
	$sql = "SELECT dgOption,dgID FROM dhcpGlobals ORDER BY dgID ASC";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$dgOption = trim($row["dgOption"]);
			$tmp = trim($row["dgID"]);
			if ($dgOption != "") {
				$New_File .= "$dgOption$New_Line";
			} else {
				WriteLog("The dhcp Global option with ID number \"$tmp\" only has white space.");
			}
		}
	} else {
		WriteLog("No global DHCP options could be found in the dhcpGlobals table. This is probably not good.");
	}
	$result->free();




	//Build Subnets.
	$sql = "SELECT * FROM dhcpSubnets ORDER BY dsID ASC";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$dsID = trim($row["dsID"]);
			$dsSubnet = trim($row["dsSubnet"]);
			$dsNetmask = trim($row["dsNetmask"]);
			$dsOptionSubnetMask = trim($row["dsOptionSubnetMask"]);
			$dsRangeDynamicBootpStart = trim($row["dsRangeDynamicBootpStart"]);
			$dsRangeDynamicBootpEnd = trim($row["dsRangeDynamicBootpEnd"]);
			$dsDefaultLeaseTime = trim($row["dsDefaultLeaseTime"]);
			$dsMaxLeaseTime = trim($row["dsMaxLeaseTime"]);
			$dsOptionRouters = trim($row["dsOptionRouters"]);
			$dsOptionDomainNameServers = trim($row["dsOptionDomainNameServers"]);
			$dsOptionNtpServers = trim($row["dsOptionNtpServers"]);
			$dsNextServer = trim($row["dsNextServer"]);
			$dsCustomArea1 = trim($row["dsCustomArea1"]);
			$dsCustomArea2 = trim($row["dsCustomArea2"]);
			$dsCustomArea3 = trim($row["dsCustomArea3"]);



			if ( empty($dsSubnet) || empty($dsNetmask) ) {
				if ( empty($dsSubnet) ) {
					WriteLog("Inside the dhcpSubnets table, the row with ID \"$dsID\" only has white space for it's dsSubnet field. Because of this, this particular subnet will be skipped.");
				} else {
					WriteLog("Inside the dhcpSubnets table, the row with ID \"$dsID\" only has white space for it's dsNetmask field. Because of this, this particular subnet will be skipped.");
				}
				continue;
			}
			$New_File .= "subnet $dsSubnet netmask $dsNetmask { $New_Line";
			
			if ($dsOptionSubnetMask != "") {
				$New_File .= "    option subnet-mask $dsOptionSubnetMask;$New_Line";
			}
			if ($dsRangeDynamicBootpStart != "" && $dsRangeDynamicBootpEnd != "") {
				$New_File .= "    range dynamic-bootp $dsRangeDynamicBootpStart $dsRangeDynamicBootpEnd;$New_Line";
			}
			if ($dsDefaultLeaseTime != "") {
				$New_File .= "    default-lease-time $dsDefaultLeaseTime;$New_Line";
			}
			if ($dsMaxLeaseTime != "") {
				$New_File .= "    max-lease-time $dsMaxLeaseTime;$New_Line";
			}
			if ($dsOptionRouters != "") {
				$New_File .= "    option routers $dsOptionRouters;$New_Line";
			}
			if ($dsOptionDomainNameServers != "") {
				$New_File .= "    option domain-name-servers $dsOptionDomainNameServers;$New_Line";
			}
			if ($dsOptionNtpServers != "") {
				$New_File .= "    option ntp-servers $dsOptionNtpServers;$New_Line";
			}
			if ($dsNextServer != "") {
				$New_File .= "    next-server $dsNextServer;$New_Line";
			}
			if ($dsCustomArea1 != "") {
				$New_File .= "    $dsCustomArea1$New_Line";
			}
			if ($dsCustomArea2 != "") {
				$New_File .= "    $dsCustomArea2$New_Line";
			}
			if ($dsCustomArea3 != "") {
				$New_File .= "    $dsCustomArea3$New_Line";
			}



			//Build classes for this subnet.	
			$sql = "SELECT * FROM dhcpClasses WHERE dc_dsID = $dsID ORDER BY dcID ASC";
        		$result2 = $link->query($sql);
        		if ($result2->num_rows > 0) {
				while($row2 = $result2->fetch_assoc()) {
					$tmp = trim($row2["dcID"]);
					$dcClass = trim($row2["dcClass"]);
					$dcMatch = trim($row2["dcMatch"]);
					$dcMatchOption1 = trim($row2["dcMatchOption1"]);
					$dcMatchOption2 = trim($row2["dcMatchOption2"]);
					$dcMatchOption3 = trim($row2["dcMatchOption3"]);




					if ( empty($dcClass) || empty($dcMatch) || empty($dcMatchOption1) ) {
						if ( empty($dcClass)) {
							WriteLog("The class with ID \"$tmp\" inside the dhcpClasses table only had whitespace for the dcClass field. Because of this, this class is being skipped.");
						} else if ( empty($dcMatch) ) {
							WriteLog("The class with ID \"$tmp\" inside the dhcpClasses table only had whitespace for the dcMatch field. Because of this, this class is being skipped.");
						} else {
							WriteLog("The class with ID \"$tmp\" inside the dhcpClasses table only had whitespace for the dcMatchOption1 field. Because of this, this class is being skipped.");
						}
						continue;
					}
					$New_File .= "    class \"$dcClass\" { $New_Line";
					$New_File .= "        $dcMatch$New_Line";
					$New_File .= "        $dcMatchOption1$New_Line";
					if ($dcMatchOption2 != "") {
						$New_File .= "        $dcMatchOption2$New_Line";
					}
					if ($dcMatchOption3 != "") {
						$New_File .= "        $dcMatchOption3$New_Line";
					}
					$New_File .= "    } $New_Line";
				}
			}
		$New_File .= "} $New_Line";
		}
	}
	$result->free();
	$result2->free();




	//Build Reservations.
	$sql = "SELECT * FROM dhcpReservations ORDER BY drID ASC";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			
			$tmp = trim($row["drID"]);
			$drMAC = trim($row["drMAC"]);
			$drName = trim($row["drName"]);
			$drFilename = trim($row["drFilename"]);
			$drIP = trim($row["drIP"]);
			$drOptionDomainNameServers = trim($row["drOptionDomainNameServers"]);
			$drCustomArea1 = trim($row["drCustomArea1"]);
			$drCustomArea2 = trim($row["drCustomArea2"]);
			$drCustomArea3 = trim($row["drCustomArea3"]);




			if (empty($drName) || empty($drMAC)) {
				if ( empty($drName) ) {
					WriteLog("The dhcp reservation with ID \"$tmp\" inside the dhcpReservations table only has white space for the field drName. Because of this, this reservation is being skipped.");
				} else {
					WriteLog("The dhcp reservation with ID \"$tmp\" inside the dhcpReservations table only has white space for the field drMAC. Because of this, this reservation is being skipped.");
				}
				continue;
			}
			$New_File .= "host $drName { $New_Line";
			$New_File .= "    hardware ethernet $drMAC;$New_Line";

			if ($drFilename != "") {
				$New_File .= "    filename \"$drFilename\";$New_Line";
			}
			if ($drIP != "") {
				$New_File .= "    fixed-address $drIP;$New_Line";
			}
			if ($drOptionDomainNameServers != "") {
				$New_File .= "    option domain-name-servers $drOptionDomainNameServers;$New_Line";
			}
			if ($drCustomArea1 != "") {
				$New_File .= "    $drCustomArea1$New_Line";
			}
			if ($drCustomArea2 != "") {
				$New_File .= "    $drCustomArea2$New_Line";
			}
			if ($drCustomArea3 != "") {
				$New_File .= "    $drCustomArea3$New_Line";
			}
			$New_File .= "} $New_Line";
		}
	}
	$result->free();




	// Write the conf file to the temp location.
	if (file_exists($tmpFile)) {
		unlink($tmpFile);
		if (file_exists($tmpFile)) {
			WriteLog("Deleting the temporary DHCP config file from \"$tmpFile\" failed for some reason. Check permissions and maybe SELinux.");
        	} else {
			file_put_contents($tmpFile, $New_File);
		}
	} else {
		file_put_contents($tmpFile, $New_File);
	}
	



	//Checksum of current file.
	if (file_exists($DHCP_To_Use)) {
		$Current_DHCP_Checksum = sha1_file($DHCP_To_Use);
	} else {
		WriteLog("The DHCP configuration file \"$DHCP_To_Use\" does not exist. Because of this, the temporary DHCP file will not be swapped out in place of where the current DHCP file should be. You should investigate why it's missing. Is the path correct? Is DHCP installed? Are permissions OK? Could it be SELinux?");
		$Current_DHCP_Checksum = "0";
	}



	//Checksum of new file.
	if (file_exists($tmpFile)) {
		$New_DHCP_Checksum = sha1_file($tmpFile);
	} else {
		WriteLog("The new DHCP configuration file \"$tmpFile\" was supposed to be written moments ago, but does not exist now. Because of this, the temporary DHCP file will not be moved into the position of the current DHCP file. Something might be wrong with permissions, the path, or possibly SELinux. The partition might be full as well. You should investigate.");
		$New_DHCP_Checksum = "1";
	}
	


	//Check if files match or not. If not, put the new file in place and restart the DHCP service.
	if ($Current_DHCP_Checksum != $New_DHCP_Checksum) {
		if ($New_DHCP_Checksum != "1" && $Current_DHCP_Checksum != "0") {
			WriteLog("The newly generated DHCP file's checksum does not match the checksum of the currently in use DHCP file. Attempting to move the current file to \"$DHCP_To_Use.old\" and attempting to place the newly generated file \"$tmpFile\" in it's place.");
			// Move old file.
			if (file_exists($DHCP_To_Use)) {
				// Delete pre-existing old file.
				if (file_exists("$DHCP_To_Use.old")) {
					unlink("$DHCP_To_Use.old");
				}
				// Move current to old.
				rename($DHCP_To_Use, "$DHCP_To_Use.old");
			}
			// Place new file.
			rename($tmpFile, $DHCP_To_Use);	
			if (file_exists($DHCP_To_Use)) { 
				$Current_DHCP_Checksum = sha1_file($DHCP_To_Use);
				if ($Current_DHCP_Checksum != $New_DHCP_Checksum) {
					WriteLog("The new DHCP file was moved to \"$DHCP_To_Use\" but for some reason the move did not complete correctly, because the new checksum for the newly placed file does not match what it was before the move happened. You should investigate. For now, we will try to move the broken file to \"$DHCP_To_Use.broke\" for you to look at and move the \"$DHCP_To_Use.old\" back into place.");
					rename($DHCP_To_Use, "$DHCP_To_Use.broke");
					rename("$DHCP_To_Use.old", $DHCP_To_Use);
				} else {
					WriteLog("Moving the files succeeded, attempting to restart the DHCP service.");
					//Restart the service here.



				}
			} else {
				WriteLog("The DHCP configuration file \"$DHCP_To_Use\" that was placed moments ago is missing. You should investigate why it's missing. Is the path correct? Is DHCP installed? Are permissions OK? Could it be SELinux? For now, we will try to put \"$DHCP_To_Use.old\" back into place.");
				rename("$DHCP_To_Use.old", $DHCP_To_Use);
			}
		}	
	} else {
		unlink($tmpFile);
	}
	




	// Sleep.
	sleep($DHCP_Service_Sleep_Time);




//end of loop.
}


//Close connection.
$link->close();

?>
