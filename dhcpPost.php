<?php

//-----------Begin Temporary code--------------------
$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'fog';
// Create connection
$link = new mysqli($servername, $username, $password, $database);
// Check connection
if ($link->connect_error) {
        // Couldn't establish a connection with the database.
        die("ERROR!");
}
//---------------End Temporary code-------------------


$NextURL = "dhcpOverview.php";
$existingGlobalOption = "0";
$newGlobalOption = "1";
$existingClass = "2";
$newClass = "3";
$existingSubnet = "4";
$newSubnet = "5";
$existingReservation = "6";
$newReservation = "7";
$SERVICE_ENABLED = "8";






//Function to do SQL query and return to previous page.
function doQuery() {
        global $sql;
	global $link;

	if ($link->query($sql)) {
        	// good, send back to NextURL
		$itemActionMessage = "<font color=\"green\">Last action was successful.</font>";
	} else {
		// Error
		$itemActionMessage = "<font color=\"red\"><b>Last action failed!</b></font>";
	}
	$link->close();
	session_start();
	$_SESSION['itemActionMessage'] = $itemActionMessage;
	//echo "$sql<br>";
	//global $itemAction;
	//echo "$itemAction<br>";
}




$type = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['type'])));
switch ($type) {
	case $existingGlobalOption:
		$itemAction = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['itemAction'])));
		$dgID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['id'])));
		$dgOption = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dgOption'])));
		if ($itemAction == "copy") {
			//No copying for global options.
		} else if ($itemAction == "delete") {
			$sql = "DELETE FROM `dhcpGlobals` WHERE `dgID`='$dgID'";
			doQuery();
		} else if ($itemAction == "save") {
			$sql = "UPDATE `dhcpGlobals` SET `dgOption`='$dgOption' WHERE `dgID`='$dgID'";
			doQuery();
		}
		break;
	case $newGlobalOption:
		$dgOption = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dgOption'])));
		$sql = "INSERT INTO `dhcpGlobals` (`dgOption`) VALUES ('$dgOption')";
		doQuery();
		break;
	case $existingClass:
		$itemAction = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['itemAction'])));
		$dcID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['id'])));
		$dc_dsID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dc_dsID'])));
		$dcClass = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcClass'])));
		$dcMatch = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatch'])));
		$dcMatchOption1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption1'])));
		$dcMatchOption2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption2'])));
		$dcMatchOption3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption3'])));
		if ($itemAction == "copy") {
			$sql = "INSERT INTO dhcpClasses (`dc_dsID`,`dcClass`,`dcMatch`,`dcMatchOption1`,`dcMatchOption2`,`dcMatchOption3`) VALUES ('$dc_dsID','$dcClass','$dcMatch','$dcMatchOption1','$dcMatchOption2','$dcMatchOption3')";
			doQuery();
		} else if ($itemAction == "delete") {
			$sql = "DELETE FROM `dhcpClasses` WHERE `dcID`='$dcID'";
			doQuery();
		} else if ($itemAction == "save") {
			$sql = "UPDATE dhcpClasses SET `dc_dsID`='$dc_dsID',`dcClass`='$dcClass',`dcMatch`='$dcMatch',`dcMatchOption1`='$dcMatchOption1',`dcMatchOption2`='$dcMatchOption2',`dcMatchOption3`='$dcMatchOption3' WHERE `dcID`='$dcID'";
			doQuery();
		}
		break;
	case $newClass:
		$dc_dsID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dc_dsID'])));
		$dcClass = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcClass'])));
		$dcMatch = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatch'])));
		$dcMatchOption1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption1'])));
		$dcMatchOption2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption2'])));
		$dcMatchOption3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dcMatchOption3'])));
		$sql = "INSERT INTO `dhcpClasses` (`dc_dsID`,`dcClass`,`dcMatch`,`dcMatchOption1`,`dcMatchOption2`,`dcMatchOption3`) VALUES ('$dc_dsID','$dcClass','$dcMatch','$dcMatchOption1','$dcMatchOption2','$dcMatchOption3')";
		doQuery();
		break;
	case $existingSubnet:
		$itemAction = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['itemAction'])));
		$dsID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['id'])));
		$dsSubnet = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsSubnet'])));
		$dsNetmask = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsNetmask'])));
		$dsRangeDynamicBootpStart = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsRangeDynamicBootpStart'])));
		$dsRangeDynamicBootpEnd = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsRangeDynamicBootpEnd'])));
		$dsOptionSubnetMask = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionSubnetMask'])));
		$dsDefaultLeaseTime = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsDefaultLeaseTime'])));
		$dsMaxLeaseTime = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsMaxLeaseTime'])));
		$dsOptionRouters = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionRouters'])));
		$dsOptionDomainNameServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionDomainNameServers'])));
		$dsOptionNtpServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionNtpServers'])));
		$dsNextServer = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsNextServer'])));
		$dsCustomArea1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea1'])));
		$dsCustomArea2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea2'])));
		$dsCustomArea3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea3'])));
		if ($itemAction == "copy") {
			//No copying for subnets.
		} else if ($itemAction == "delete") {
			$sql = "DELETE FROM `dhcpSubnets` WHERE `dsID`='$dsID'";
			doQuery();
			$sql = "DELETE FROM `dhcpClasses` WHERE `dc_dsID`='$dsID'";
			doQuery();
			$sql = "DELETE FROM `dhcpReservations` WHERE `dr_dsID`='$dsID'";
			doQuery();
		} else if ($itemAction == "save") {
			$sql = "UPDATE `dhcpSubnets` SET `dsSubnet`='$dsSubnet',`dsNetmask`='$dsNetmask',`dsOptionSubnetMask`='$dsOptionSubnetMask',`dsRangeDynamicBootpStart`='$dsRangeDynamicBootpStart',`dsRangeDynamicBootpEnd`='$dsRangeDynamicBootpEnd',`dsDefaultLeaseTime`='$dsDefaultLeaseTime',`dsMaxLeaseTime`='$dsMaxLeaseTime',`dsOptionRouters`='$dsOptionRouters',`dsOptionDomainNameServers`='$dsOptionDomainNameServers',`dsOptionNtpServers`='$dsOptionNtpServers',`dsNextServer`='$dsNextServer',`dsCustomArea1`='$dsCustomArea1',`dsCustomArea2`='$dsCustomArea2',`dsCustomArea3`='$dsCustomArea3' WHERE `dsID`='$dsID'";
			doQuery();
		}
		break;
	case $newSubnet:
		$dsSubnet = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsSubnet'])));
		$dsNetmask = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsNetmask'])));
		$dsRangeDynamicBootpStart = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsRangeDynamicBootpStart'])));
		$dsRangeDynamicBootpEnd = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsRangeDynamicBootpEnd'])));
		$dsOptionSubnetMask = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionSubnetMask'])));
		$dsDefaultLeaseTime = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsDefaultLeaseTime'])));
		$dsMaxLeaseTime = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsMaxLeaseTime'])));
		$dsOptionRouters = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionRouters'])));
		$dsOptionDomainNameServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionDomainNameServers'])));
		$dsOptionNtpServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsOptionNtpServers'])));
		$dsNextServer = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsNextServer'])));
		$dsCustomArea1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea1'])));
		$dsCustomArea2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea2'])));
		$dsCustomArea3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dsCustomArea3'])));
		$sql = "INSERT INTO `dhcpSubnets` (`dsSubnet`,`dsNetmask`,`dsOptionSubnetMask`,`dsRangeDynamicBootpStart`,`dsRangeDynamicBootpEnd`,`dsDefaultLeaseTime`,`dsMaxLeaseTime`,`dsOptionRouters`,`dsOptionDomainNameServers`,`dsOptionNtpServers`,`dsNextServer`,`dsCustomArea1`,`dsCustomArea2`,`dsCustomArea3`) VALUES ('$dsSubnet','$dsNetmask','$dsOptionSubnetMask','$dsRangeDynamicBootpStart','$dsRangeDynamicBootpEnd','$dsDefaultLeaseTime','$dsMaxLeaseTime','$dsOptionRouters','$dsOptionDomainNameServers','$dsOptionNtpServers','$dsNextServer','$dsCustomArea1','$dsCustomArea2','$dsCustomArea3')";
		doQuery();
		break;
	case $existingReservation:
		$itemAction = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['itemAction'])));
		$drName = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drName'])));
		$drMAC = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drMAC'])));
		$drIP = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drIP'])));
		$drFilename = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drFilename'])));
		$drOptionDomainNameServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drOptionDomainNameServers'])));
		$drCustomArea1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea1'])));
		$drCustomArea2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea2'])));
		$drCustomArea3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea3'])));
		$dr_dsID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dr_dsID'])));
		$drID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['id'])));
		if ($itemAction == "copy") {
			$sql = "INSERT INTO `dhcpReservations` (`dr_dsID`,`drMAC`,`drName`,`drFilename`,`drIP`,`drOptionDomainNameServers`,`drCustomArea1`,`drCustomArea2`,`drCustomArea3`) VALUES ('$dr_dsID','$drMAC','$drName','$drFilename','$drIP','$drOptionDomainNameServers','$drCustomArea1','$drCustomArea2','$drCustomArea3')";
			doQuery();
		} else if ($itemAction == "delete") {
			$sql = "DELETE FROM `dhcpReservations` WHERE `drID`='$drID'";
			doQuery();
		} else if ($itemAction == "save") {
			$sql = "UPDATE dhcpReservations SET `drName`='$drName',`drMAC`='$drMAC',`drIP`='$drIP',`drFilename`='$drFilename',`drOptionDomainNameServers`='$drOptionDomainNameServers',`drCustomArea1`='$drCustomArea1',`drCustomArea2`='$drCustomArea2',`drCustomArea3`='$drCustomArea3',`dr_dsID`='$dr_dsID' WHERE `drID`='$drID'";
			doQuery();
		}
		break;
	case $newReservation:
		$drName = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drName'])));
		$drMAC = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drMAC'])));
		$drIP = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drIP'])));
		$drFilename = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drFilename'])));
		$drOptionDomainNameServers = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drOptionDomainNameServers'])));
		$drCustomArea1 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea1'])));
		$drCustomArea2 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea2'])));
		$drCustomArea3 = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['drCustomArea3'])));
		$dr_dsID = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['dr_dsID'])));
		$sql = "INSERT INTO `dhcpReservations` (`dr_dsID`,`drMAC`,`drName`,`drFilename`,`drIP`,`drOptionDomainNameServers`,`drCustomArea1`,`drCustomArea2`,`drCustomArea3`) VALUES ('$dr_dsID','$drMAC','$drName','$drFilename','$drIP','$drOptionDomainNameServers','$drCustomArea1','$drCustomArea2','$drCustomArea3')";
		doQuery();
		break;
	case $SERVICE_ENABLED:
		$DHCP_SERVICE_ENABLED = $link->real_escape_string(htmlspecialchars_decode(trim($_REQUEST['DHCP_SERVICE_ENABLED'])));
		$sql = "UPDATE `globalSettings` SET `settingValue`='$DHCP_SERVICE_ENABLED' WHERE `settingKey`='DHCP_SERVICE_ENABLED'";
		doQuery();
		break;
}


header("Location: $NextURL");

?>
