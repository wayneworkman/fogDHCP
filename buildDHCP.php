<?php

$servername="127.0.0.1";
$username="root";
$password="";
$database="fog";


$True="True";
$False="False";
$LogFileName = "fogdhcp.log";
$DefaultLogFilePath="/opt/fog/log/"; // Include trailing slash here.
$dhcpdSetting="dhcpd=";
$SettingsFile="/opt/fog/.fogsettings";
$New_Line="\n";
$NotAvailable="NA";
$Failed="";
$TimeZone="UTC";
date_default_timezone_set($TimeZone);





//Function to write to the log.
function WriteLog($Message) {
	global $log;
	global $New_Line;
	global $DefaultLogFilePath;
	global $LogFileName;
	$Now=date("[Y-m-d  h:i:sa]");
	if ($log == "") {
		$log = "$DefaultLogFilePath$LogFileName";
	}
	if (file_exists($log)) {
		$current = file_get_contents($log);
		$current .= "$Now  $Message$New_Line";	
	} else {
		$current = "$Now  $Message$New_Line";
	}
	file_put_contents($log, $current);
}







//Function to restart DHCP.
function RestartDHCP() {
	global $DHCP_METHOD;
	global $dhcpStatus;
	global $NotAvailable;
	global $dhcpd;
	if ($DHCP_METHOD == "1") {
		WriteLog(shell_exec("service $dhcpd stop;sleep 2;service $dhcpd start;sleep 2;service $dhcpd status;sleep 2"));
		$dhcpStatus=shell_exec("service $dhcpd status");
	} else if ($DHCP_METHOD == "2") {
		WriteLog(shell_exec("service $dhcpd stop;sleep 2;service $dhcpd start"));
		$dhcpStatus=$NotAvailable;
		WriteLog("The DHCP_METHOD is set to \"$DHCP_METHOD\". This means we don't know if DHCP is running properly or not. You should verify if it's OK.");
	} else if ($DHCP_METHOD == "3") {
		WriteLog(shell_exec("systemctl stop $dhcpd;sleep 2;systemctl start $dhcpd;sleep 2;systemctl status $dhcpd;sleep 2"));
		$dhcpStatus=shell_exec("systemctl status $dhcpd");
	} else if ($DHCP_METHOD == "0") {
		WriteLog("The settingKey \"DHCP_METHOD\" in the globalSettings table is set to 0, no interaction with the system's DHCP service will be attempted.");
		$dhcpStatus=$NotAvailable;
	} else {
		WriteLog("The settingKey \"DHCP_METHOD\" in the globalSettings table is set to something that is not supported. It's currently set to \"$DHCP_METHOD\"   Are there typos, white spaces, and/or other odd things?");
		$dhcpStatus=$NotAvailable;
	}
}




//Function to check DHCP status.
function CheckDHCP() {
	global $dhcpStatus;
	global $NotAvailable;
	global $Failed;
	global $True;
	global $False;
	global $ONLY_LOG_CHANGES;

	// Bad status patterns.
	$bad1="Active: inactive (dead)";
	$bad2="Loaded: not-found";
	$bad3="Active: failed";
	$bad4="Configuration file errors encountered -- exiting";
	$bad5="(code=exited, status=1/FAILURE)";
	$bad6="Failed to start DHCPv4 Server Daemon.";
	$bad7="stop/waiting";

	// Good status patterns.
	$good1="Active: active (running)";
	$good2="start/running";

	//Check for failure/success patterns.
	if ((strpos($dhcpStatus, $bad1) == true) || (strpos($dhcpStatus, $bad2) == true) || (strpos($dhcpStatus, $bad3) == true) || (strpos($dhcpStatus, $bad4) == true) || (strpos($dhcpStatus, $bad5) == true) || (strpos($dhcpStatus, $bad6) == true) || (strpos($dhcpStatus, $bad7) == true)) {
		//DHCP status is bad.
		$Failed=$True;
		WriteLog("Detected a DHCP service failure!");

	} else if (((strpos($dhcpStatus, $good1) == true) || (strpos($dhcpStatus, $good2) == true)) && ($dhcpStatus != $NotAvailable)) {
		//DHCP status is good.
		$Failed=$False;
		if ($ONLY_LOG_CHANGES == "0") {
			WriteLog("Detected that the DHCP service is running without error.");
		}
	} else if ($dhcpStatus == $NotAvailable) {
		$Failed=$NotAvailable;
		if ($ONLY_LOG_CHANGES == "0") {
			WriteLog("The DHCP service status is not available. This may be due to OS type, or a request to not bother the DHCP servie.");
		}
	} else {
		$Failed=$True;
		WriteLog("Could not reliably determine the state of the DHCP service. You should imediately investigate it's status.");
	}
}




// Create connection
$link = new mysqli($servername, $username, $password, $database);
// Check connection
if ($link->connect_error) {
	// Couldn't establish a connection with the database.
	WriteLog("Couldn't establish a connection with the database.");
}







//start loop.
while(1) {



	// Make sure the connection is still alive, if not, try to reconnect. DO NOT proceed without a good connection.
	while(!$link->ping()) {
		WriteLog("Lost connection to the database. Not doing anything until the connection can be re-established.");
		//Attempt a re-connect.
		$link = new mysqli($servername, $username, $password, $database);
		// Check connection
		if ($link->connect_error) {
			WriteLog("Couldn't re-establish a connection with the database.");
		} else {
			WriteLog("Connection to the database has been re-established.");
		}
		// Give the server breathing room, whether the database is active or not.
		$DHCP_SERVICE_SLEEP_TIME=60;
		sleep($DHCP_SERVICE_SLEEP_TIME);
	}


	

	//Clear out the contents of the New_File variable.
	$New_File = "";



	//Read dhcpd setting type from settings file.
	if (file_exists($SettingsFile) && ($myfile = fopen($SettingsFile, "r")!==false)) {
		$myfile = fopen($SettingsFile, "r");
		$SettingsContent = fread($myfile,filesize($SettingsFile));
		fclose($myfile);
		//get position of dhcpd setting, and set the offset, then get the value.
		$FirstCharacterPosition = (strpos($SettingsContent, $dhcpdSetting) + 7);
		$EnclosingCharacter =  $SettingsContent{($FirstCharacterPosition - 1)};
		$LastCharacterPosition = strpos($SettingsContent, $EnclosingCharacter, $FirstCharacterPosition);
		$dhcpd = trim(substr($SettingsContent, $FirstCharacterPosition, ($LastCharacterPosition - $FirstCharacterPosition)));
		if ($dhcpd == "") {
			$dhcpd = "dhcpd";
			WriteLog("The setting \"$dhcpdSetting\" inside the file \"$SettingsFile\" was not read correctly. For now, the dhcpd type has been set to \"$dhcpd\".");
		}
	} else {
		$dhcpd="dhcpd";
		WriteLog("The settings file \"$SettingsFile\" either wasn't found or couldn't be opened. It might not be there, or something else like permissions or SELinux may be blocking it from being detected or opened. You should investigate. For now, the dhcpd type has been set to \"$dhcpd\".");
	}





	//Get the timezone.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'FOG_TZ_INFO' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$TimeZone = trim($row["settingValue"]);
			if ($TimeZone != "" ) {
				date_default_timezone_set($TimeZone);
			} else {
				$TimeZone="UTC";
				date_default_timezone_set($TimeZone);
				WriteLog("The FOG_TZ_INFO setting inside of the globalSettings table only has white space in it. Default timezone of \"$TimeZone\" is set.");
			}
		}
	} else {
		$TimeZone="UTC";
		date_default_timezone_set($TimeZone);
		WriteLog("Could not get the FOG_TZ_INFO setting from the globalSettings table. Default timezone of \"$TimeZone\" is set.");
	}
	$result->free();




	//Get sleep time.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'DHCP_SERVICE_SLEEP_TIME' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$DHCP_SERVICE_SLEEP_TIME = $tmp;
			} else {
				$DHCP_SERVICE_SLEEP_TIME=60;
				WriteLog("The DHCP_SERVICE_SLEEP_TIME setting in the globalSettings table only has white space in it. Default sleep time \"$DHCP_SERVICE_SLEEP_TIME\" is set.");
			}
		}
	} else {
		$DHCP_SERVICE_SLEEP_TIME=60;
		WriteLog("Could not get the DHCP_SERVICE_SLEEP_TIME from the globalSettings table. Default sleep time \"$DHCP_SERVICE_SLEEP_TIME\" is set.");
	}
	$result->free();





	//Get the logs path.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'SERVICE_LOG_PATH' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$log = $tmp . $LogFileName;
			} else {
				$log = "$DefaultLogFilePath$LogFileName";
				WriteLog("The SERVICE_LOG_PATH setting in the globalSettings table only has white space in it. Default log \"$log\" is set.");
			}
		}
	} else {
		$log = "$DefaultLogFilePath$LogFileName";
		WriteLog("Could not get the SERVICE_LOG_PATH from the globalSettings table. Default log \"$log\" is set.");
	}
	$result->free();

	



	//Get DHCP Config file.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'DHCP_TO_USE' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$DHCP_TO_USE = $tmp;
				$tmpFile = "$DHCP_TO_USE.tmp";
			} else {
				$DHCP_TO_USE="/etc/dhcp/dhcpd.conf";
				$tmpFile = "$DHCP_TO_USE.tmp";
				WriteLog("The DHCP_TO_USE setting in the globalSettings table only has white space in it. Default file \"$DHCP_TO_USE\" is set.");
			}
		}
	} else {
		$DHCP_TO_USE="/etc/dhcp/dhcpd.conf";
		$tmpFile = "$DHCP_TO_USE.tmp";
		WriteLog("Could not get the DHCP_TO_USE from the globalSettings table. Default file \"$DHCP_TO_USE\" is set.");
	}
	$result->free();





	// Get DHCP Method.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'DHCP_METHOD' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$DHCP_METHOD = $tmp;
			} else {
				$DHCP_METHOD="3";
				WriteLog("The DHCP_METHOD setting in the globalSettings table only has white space in it. Default option \"$DHCP_METHOD\" is set.");
			}
		}
	} else {
		$DHCP_METHOD="3";
		WriteLog("Could not get the DHCP_METHOD from the globalSettings table. Default option \"$DHCP_METHOD\" is set.");
	}
	$result->free();





	// Get Logging Preference.
	$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey` = 'ONLY_LOG_CHANGES' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$tmp = trim($row["settingValue"]);
			if ($tmp != "") {
				$ONLY_LOG_CHANGES = $tmp;
			} else {
				$ONLY_LOG_CHANGES="1";
				WriteLog("The ONLY_LOG_CHANGES setting in the globalSettings table only has white space in it. Default option \"$ONLY_LOG_CHANGES\" is set.");
			}
		}
	} else {
		$ONLY_LOG_CHANGES="1";
		WriteLog("Could not get the ONLY_LOG_CHANGES from the globalSettings table. Default option \"$ONLY_LOG_CHANGES\" is set.");
	}
	$result->free();






	//Build Global Options into file.
	$sql = "SELECT `dgOption`,`dgID` FROM `dhcpGlobals` ORDER BY `dgID` ASC";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$dgOption = trim($row["dgOption"]);
			$tmp = trim($row["dgID"]);
			if ($dgOption != "") {
				$New_File .= "$dgOption$New_Line";
			} else {
				WriteLog("The dhcp Global option with ID number \"$tmp\" only has white space. Skipping this global setting.");
			}
		}
	} else {
		if ($ONLY_LOG_CHANGES == "0") {
			WriteLog("No global DHCP options could be found in the dhcpGlobals table. This is probably not good.");
		}
	}
	$result->free();








	//Build global classes. All classes with a dc_dsID greater than one-million are global.   
	$sql = "SELECT * FROM `dhcpClasses` WHERE `dc_dsID` = 2000000 ORDER BY `dcID` ASC";
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
				$New_File .= "class \"$dcClass\" { $New_Line";
				$New_File .= "    $dcMatch$New_Line";
				$New_File .= "    $dcMatchOption1$New_Line";
				if ($dcMatchOption2 != "") {
					$New_File .= "    $dcMatchOption2$New_Line";
				}
				if ($dcMatchOption3 != "") {
					$New_File .= "    $dcMatchOption3$New_Line";
				}
			$New_File .= "} $New_Line";
			}
		}






	//Build Subnets.
	$sql = "SELECT * FROM `dhcpSubnets` ORDER BY `dsID` ASC";
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
			$sql = "SELECT * FROM `dhcpClasses` WHERE `dc_dsID` = $dsID ORDER BY `dcID` ASC";
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


			//Build DHCP Reservations for this subnet.
			$sql = "SELECT * FROM `dhcpReservations` WHERE `dr_dsID` = $dsID ORDER BY `drID` ASC";
			$result3 = $link->query($sql);
			if ($result3->num_rows > 0) {
				while($row3 = $result3->fetch_assoc()) {
			
					$tmp = trim($row3["drID"]);
					$drMAC = trim($row3["drMAC"]);
					$drName = trim($row3["drName"]);
					$drFilename = trim($row3["drFilename"]);
					$drIP = trim($row3["drIP"]);
					$drOptionDomainNameServers = trim($row3["drOptionDomainNameServers"]);
					$drCustomArea1 = trim($row3["drCustomArea1"]);
					$drCustomArea2 = trim($row3["drCustomArea2"]);
					$drCustomArea3 = trim($row3["drCustomArea3"]);
					if (empty($drName) || empty($drMAC)) {
						if ( empty($drName) ) {
							WriteLog("The dhcp reservation with ID \"$tmp\" inside the dhcpReservations table only has white space for the field drName. Because of this, this reservation is being skipped.");
						} else {
							WriteLog("The dhcp reservation with ID \"$tmp\" inside the dhcpReservations table only has white space for the field drMAC. Because of this, this reservation is being skipped.");
						}
						continue;
					}
					$New_File .= "    host $drName { $New_Line";
					$New_File .= "        hardware ethernet $drMAC;$New_Line";

					if ($drFilename != "") {
						$New_File .= "        filename \"$drFilename\";$New_Line";
					}
					if ($drIP != "") {
						$New_File .= "        fixed-address $drIP;$New_Line";
					}
					if ($drOptionDomainNameServers != "") {
						$New_File .= "        option domain-name-servers $drOptionDomainNameServers;$New_Line";
					}
					if ($drCustomArea1 != "") {
						$New_File .= "        $drCustomArea1$New_Line";
					}
					if ($drCustomArea2 != "") {
						$New_File .= "        $drCustomArea2$New_Line";
					}
					if ($drCustomArea3 != "") {
						$New_File .= "        $drCustomArea3$New_Line";
					}
					$New_File .= "    } $New_Line";
				}
			}


		$New_File .= "} $New_Line";
		}
	}
	$result->free();
	$result2->free();
	$result3->free();



	//Build Global Reservations.
	$sql = "SELECT * FROM `dhcpReservations` WHERE `dr_dsID` = 2000000 ORDER BY `drID` ASC";
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
	if (file_exists($DHCP_TO_USE)) {
		$Current_DHCP_Checksum = sha1_file($DHCP_TO_USE);
		//Check it twice.
		$tmp = sha1_file($DHCP_TO_USE);
		if ($tmp != $Current_DHCP_Checksum) {
			WriteLog("The DHCP configuration file \"$DHCP_TO_USE\" failed to be checksumed correctly. No action will be taken with the DHCP configuration file or the DHCP service. This can be due to RAM issues, a failing HDD or possibly other causes.");
			$Current_DHCP_Checksum = "0";
		}
	} else {
		WriteLog("The DHCP configuration file \"$DHCP_TO_USE\" does not exist. Because of this, the temporary DHCP file will not be swapped out in place of where the current DHCP file should be. You should investigate why it's missing. Is the path correct? Is DHCP installed? Are permissions OK? Could it be SELinux?");
		$Current_DHCP_Checksum = "0";
	}



	//Checksum of new file.
	if (file_exists($tmpFile)) {
		$New_DHCP_Checksum = sha1_file($tmpFile);
		//Check it twice.
		$tmp = sha1_file($tmpFile);
		if ($tmp != $New_DHCP_Checksum) {
			WriteLog("The new DHCP configuration file \"$tmpFile\" failed to be checksumed correctly. No action will be taken with the DHCP configuration file or the DHCP service. This can be due to RAM issues, a failing HDD or possibly other causes.");
			$New_DHCP_Checksum = "1";
		}
	} else {
		WriteLog("The new DHCP configuration file \"$tmpFile\" was supposed to be written moments ago, but does not exist now. Because of this, the temporary DHCP file will not be moved into the position of the current DHCP file. Something might be wrong with permissions, the path, or possibly SELinux. The partition might be full as well. You should investigate.");
		$New_DHCP_Checksum = "1";
	}
	


	//Check if files match or not. If not, put the new file in place and restart the DHCP service.
	if ($Current_DHCP_Checksum != $New_DHCP_Checksum) {
		if ($New_DHCP_Checksum != "1" && $Current_DHCP_Checksum != "0") {
			WriteLog("The newly generated DHCP files checksum does not match the checksum of the currently in use DHCP file. Attempting to move the current file to \"$DHCP_TO_USE.old\" and attempting to place the newly generated file \"$tmpFile\" in it's place.");
			// Move old file.
			if (file_exists($DHCP_TO_USE)) {
				// Delete pre-existing old file.
				if (file_exists("$DHCP_TO_USE.old")) {
					unlink("$DHCP_TO_USE.old");
				}
				// Move current to old.
				rename($DHCP_TO_USE, "$DHCP_TO_USE.old");
			}
			// Place new file.
			rename($tmpFile, $DHCP_TO_USE);	
			if (file_exists($DHCP_TO_USE)) { 
				$Current_DHCP_Checksum = sha1_file($DHCP_TO_USE);
				if ($Current_DHCP_Checksum != $New_DHCP_Checksum) {
					WriteLog("The new DHCP file was moved to \"$DHCP_TO_USE\" but for some reason the move did not complete correctly, because the new checksum for the newly placed file does not match what it was before the move happened. You should investigate. For now, we will try to move the broken file to \"$DHCP_TO_USE.broke\" for you to look at and move the \"$DHCP_TO_USE.old\" back into place.");
					rename($DHCP_TO_USE, "$DHCP_TO_USE.broke");
					rename("$DHCP_TO_USE.old", $DHCP_TO_USE);
				} else {
					WriteLog("Moving the files succeeded, attempting to restart the DHCP service.");
					//Restart the service here.
					RestartDHCP();

					//Here, attempt to check the status of DHCP.
					CheckDHCP();

					//Check for failure/success.
					if ($Failed == $True) {
						//Restarting DHCP failed.
						//Restore old dhcp config file and try to restart the service again.
						if (file_exists("$DHCP_TO_USE.old")) {
							WriteLog("Attempting to move the newly made bad configuration \"$DHCP_TO_USE\" to \"$DHCP_TO_USE.broke\" and attempting to move \"$DHCP_TO_USE.good\" in place as the current DHCP file.");
							// Move the newly made file.
							if (file_exists($DHCP_TO_USE)) {
								rename($DHCP_TO_USE, "$DHCP_TO_USE.broke");
							} else {
								WriteLog("The newly created dhcp file could not be found, that's not good. Continuing efforts to restore DHCP services.");
							}
							// Copy good to current.
							copy("$DHCP_TO_USE.good", $DHCP_TO_USE);
							if (file_exists($DHCP_TO_USE)) {
								WriteLog("Moving \"$DHCP_TO_USE.good\" to \"$DHCP_TO_USE\" seems to have succeeded. Attempting to restart the DHCP service now.");
								// Attempt to restart service one last time.
								RestartDHCP();
								CheckDHCP();
								if ($Failed == $True) {
									WriteLog("It seems that either the old config file was bad too, or there are larger problems. Efforts to restore DHCP services have failed. You need to take imediate action to restore them.");
								} else {
									WriteLog("It seems that efforts to restore DHCP services from the old file have succeeded. You should investigate what is wrong with your configuration.");
									$DHCP_SERVICE_SLEEP_TIME = 3600;
									WriteLog("DHCP_SERVICE_SLEEP_TIME has been set to \"$DHCP_SERVICE_SLEEP_TIME\" due to the recent failure in an effort to minimize interruptions. If you'd like this service to attempt to rebuild the DHCP config file and attempt a restart before then, you'll need to restart the service manually.");
								}
							}
						} else {
							WriteLog("The DHCP service has failed, and for some reason, there is no \"$DHCP_TO_USE.old\" to try to restore. You need to immediately investigate the cause of the failure and find a solution. It could be something as simple as a typo in your configuration, or someone has removed the backup dhcp file, or something else as well.");
						}
					} else {
						WriteLog("Making a copy of the current DHCP configuration to \"$DHCP_TO_USE.good\" as a point to revert to if a future configuration fails." );
						copy($DHCP_TO_USE, "$DHCP_TO_USE.good");
					}
				}
			} else {
				WriteLog("The DHCP configuration file \"$DHCP_TO_USE\" that was placed moments ago is missing. You should investigate why it's missing. Is the path correct? Is DHCP installed? Are permissions OK? Could it be SELinux? For now, we will try to put \"$DHCP_TO_USE.old\" back into place.");
				rename("$DHCP_TO_USE.old", $DHCP_TO_USE);
			}
		}	
	} else {
		// Check logging preference and then perform preference.
		if ($ONLY_LOG_CHANGES == "0") {
			WriteLog("The new and current configs are identical.");
		}
		unlink($tmpFile);
	}
	




	// Sleep.
	sleep($DHCP_SERVICE_SLEEP_TIME);




//end of loop.
}


//Close connection.
$link->close();

?>
