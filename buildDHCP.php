<?php

$servername="127.0.0.1";
$username="root";
$password="";
$database="fog";
$DHCP_Service_Sleep_Time=60;
$DHCP_To_Use="";
$Current_DHCP_Checksum="";
$New_DHCP_Checksum="";
$New_File="";
$New_Line="\n";



// Create connection
$link = new mysqli($servername, $username, $password, $database);
// Check connection
if ($link->connect_error) {
	// Couldn't establish a connection with the database.
	die("Error");
}



//start loop.
while(1) {


	//Clear out the contents of the New_File variable.
	$New_File = "";


	//Get sleep time.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_Service_Sleep_Time' LIMIT 1";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$DHCP_Service_Sleep_Time = trim($row["settingValue"]);
		}
	}
	

	//Get DHCP Config file.
	$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = 'DHCP_To_Use' LIMIT 1";
        $result = $link->query($sql);
        if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                        $DHCP_To_Use = trim($row["settingValue"]);
                }
        }



	//Build Global Options into file.
	$sql = "SELECT dgOption FROM dhcpGlobals ORDER BY dgID ASC";
        $result = $link->query($sql);
        if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                        $dgOption = trim($row["dgOption"]);
			$New_File .= "$dgOption$New_Line";
                }
        }
	



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
					$dcClass = trim($row2["dcClass"]);
					$dcMatch = trim($row2["dcMatch"]);
					$dcMatchOption1 = trim($row2["dcMatchOption1"]);
					$dcMatchOption2 = trim($row2["dcMatchOption2"]);
					$dcMatchOption3 = trim($row2["dcMatchOption3"]);
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



	//Build Reservations.
	$sql = "SELECT * FROM dhcpReservations ORDER BY drID ASC";
	$result = $link->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {

			$drMAC = trim($row["drMAC"]);
			$drName = trim($row["drName"]);
			$drFilename = trim($row["drFilename"]);
			$drIP = trim($row["drIP"]);
			$drOptionDomainNameServers = trim($row["drOptionDomainNameServers"]);
			$drCustomArea1 = trim($row["drCustomArea1"]);
			$drCustomArea2 = trim($row["drCustomArea2"]);
			$drCustomArea3 = trim($row["drCustomArea3"]);


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





	// Write the conf file.
	$file = "/tmp/dhcpd.conf";
	if (file_exists($file)) {
		unlink($file);
	}
	file_put_contents($file, $New_File);
	
	//Check MD5 Sum.
	$Current_DHCP_Checksum = sha1_file($DHCP_To_Use);
	$New_DHCP_Checksum = sha1_file($file);
	echo $Current_DHCP_Checksum . "\n";
	echo $New_DHCP_Checksum . "\n";
	if ($Current_DHCP_Checksum != $New_DHCP_Checksum) {
		// Move file and restart service.
		unlink($DHCP_To_Use);
		rename($file, $DHCP_To_Use);	
	} else {
		unlink($file);
	}

	
	// Sleep.
        sleep($DHCP_Service_Sleep_Time);

//end of loop.
}

//Close connection.
$link->close();

?>
