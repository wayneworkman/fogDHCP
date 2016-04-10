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
echo "<!DOCTYPE html>";
echo "<html lang=\"en\">";
echo "<body>";
//---------------End Temporary code-------------------



$formAction = "dhcpPost.php";
$globalIdentifier = "2000000";
$globalText = "global";
$subnetText = "subnet";
$maskText = "netmask";
$requiredColor = "red";
$tab = "&emsp;&emsp;"; //Note every $tab used in code is followed by a space or red asterisk.
$True="True";
$False="False";
$NotAvailable="NA";
$existingGlobalOption = "0";
$newGlobalOption = "1";
$existingClass = "2";
$newClass = "3";
$existingSubnet = "4";
$newSubnet = "5";
$existingReservation = "6";
$newReservation = "7";
$SERVICE_ENABLED = "8";





// Get list of subnets for use with other bodies.
$dsSubnets = array();
$dsNetmasks = array();
$dsIDs = array();
$sql = "SELECT `dsSubnet`,`dsNetmask`,`dsID` FROM `dhcpSubnets` ORDER BY `dsID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
        $subnetsExist = "1";
        while($row = $result->fetch_assoc()) {
                $dsSubnets[] = trim(htmlspecialchars($row["dsSubnet"]));
                $dsNetmasks[] = trim(htmlspecialchars($row["dsNetmask"]));
                $dsIDs[] = trim(htmlspecialchars($row["dsID"]));
        }
} else {
        $subnetsExist = "0";
}
$result->free();



// Get list of filenames for use with other bodies.
$dfFileNames = array();
$sql = "SELECT `dfFileName` FROM `dhcpFilenames` ORDER BY `dfID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	$filenamesExist = "1";
	while($row = $result->fetch_assoc()) {
		$dfFileNames[] = trim(htmlspecialchars($row["dfFileName"]));
	}
} else {
	$filenamesExist = "0";
}
$result->free();




// Get DHCP status.
$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey`='DHCP_FAILED' LIMIT 1";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$DHCP_FAILED = trim(htmlspecialchars($row["settingValue"]));
	}
} else {
	$DHCP_FAILED = $NotAvailable;
}
$result->free();






// Get DHCP enabled.
$sql = "SELECT `settingValue` FROM `globalSettings` WHERE `settingKey`='DHCP_SERVICE_ENABLED' LIMIT 1";
$result = $link->query($sql);
if ($result->num_rows > 0) {
        $filenamesExist = "1";
        while($row = $result->fetch_assoc()) {
                $DHCP_SERVICE_ENABLED = trim(htmlspecialchars($row["dfFileName"]));
        }
} else {
        $DHCP_SERVICE_ENABLED = "0";
}
$result->free();





//Display DHCP status and enabled/disabled radio button.
echo "<div>";
echo "DHCP status is ";
if ($DHCP_FAILED == $True) {
	echo "<font color=\"red\"><b>FAILED!</b></font><br>";
} else if ($DHCP_FAILED == $False) {
	echo "<font color=\"green\"><b>ACTIVE!</b></font><br>";
} else if ($DHCP_FAILED == $NotAvailable) {
	echo "<font color=\"yellow\"><b>NOT AVAILABLE!</b></font><br>";
} else {
	echo "<font color=\"red\"><b>NOT IDENTIFIED!</b></font><br>";
}
echo "</div>";



echo "<br><br>";


echo "<div>";
echo "<form action=\"$formAction\" method=\"post\">";
echo "Enable or Disable DHCP manager service:<br>";
echo "<input type=\"radio\" name=\"DHCP_SERVICE_ENABLED\" value=\"1\"";if ($DHCP_SERVICE_ENABLED = "1") {echo " checked";};echo "> Enabled<br>";
echo "<input type=\"radio\" name=\"DHCP_SERVICE_ENABLED\" value=\"0\"";if ($DHCP_SERVICE_ENABLED = "0") {echo " checked";};echo "> Disabled<br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$SERVICE_ENABLED\"><input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";



echo "<br><br>";



// DHCP Global Options.
echo "<div>";
$sql = "SELECT `dgID`,`dgOption` FROM `dhcpGlobals` ORDER BY `dgID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "$globalText option(s):<br>";
        while($row = $result->fetch_assoc()) {
                $dgID = trim(htmlspecialchars($row["dgID"]));
                $dgOption = trim(htmlspecialchars($row["dgOption"]));
		if ($dgOption != "") {
			echo "<form action=\"$formAction\" method=\"post\">";
			echo "<input type=\"hidden\" name=\"type\" value=\"$existingGlobalOption\"><input type=\"hidden\" name=\"id\" value=\"$dgID\"><input type=\"text\" name=\"globalOption\" value=\"$dgOption\"> Delete<input type=\"checkbox\" name=\"delete\" value=\"delete\"> <input type=\"submit\" value=\"Submit\"><br>";
			echo "</form";
		}
        }
} else {
	echo "There are no $globalText options defined.<br>";
}
$result->free();
echo "</div>";





echo "<br><br>";




// DHCP Global Classes
echo "<div>";
$sql = "SELECT `dcID`,`dcClass`,`dcMatch`,`dcMatchOption1`,`dcMatchOption2`,`dcMatchOption3` FROM `dhcpClasses` WHERE dc_dsID = $globalIdentifier ORDER BY `dcID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "$globalText Classe(s):";
	while($row = $result->fetch_assoc()) {
		$dcID = trim(htmlspecialchars($row["dcID"]));
		$dcClass = trim(htmlspecialchars($row["dcClass"]));
		$dcMatch = trim(htmlspecialchars($row["dcMatch"]));
		$dcMatchOption1 = trim(htmlspecialchars($row["dcMatchOption1"]));
		$dcMatchOption2 = trim(htmlspecialchars($row["dcMatchOption2"]));
		$dcMatchOption3 = trim(htmlspecialchars($row["dcMatchOption3"]));
		echo "<form action=\"$formAction\" method=\"post\">";
		echo "<br>";
		echo "<input type=\"hidden\" name=\"type\" value=\"$existingClass\">";
		echo "<input type=\"hidden\" name=\"id\" value=\"$dcID\">";
		echo "<font color=\"$requiredColor\">*</font>class \"<input type=\"text\" name=\"dcClass\" value=\"$dcClass\">\" {<br>";
		echo "$tab<font color=\"$requiredColor\">*</font>Match: <input type=\"text\" name=\"dcMatch\" value=\"$dcMatch\"><br>";
		echo "$tab<font color=\"$requiredColor\">*</font>Match Option 1: <input type=\"text\" name=\"dcMatchOption1\" value=\"$dcMatchOption1\"><br>";
		echo "$tab Match Option 2: <input type=\"text\" name=\"dcMatchOption2\" value=\"$dcMatchOption2\"><br>";
		echo "$tab Match Option 3: <input type=\"text\" name=\"dcMatchOption3\" value=\"$dcMatchOption3\"><br>";
		echo "}<br>";
		echo "Copy to: <input type=\"radio\" name=\"copyOrMove\" value=\"copy\"> Move to: <input type=\"radio\" name=\"copyOrMove\" value=\"move\"> <select name=\"dc_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
		if ($subnetsExist = "1") {
			$i = 0;
			foreach ($dsIDs as $dsID) {
				echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
			$i = $i + 1;
			}
		}
		echo "</select><br>";
		echo "Delete: <input type=\"checkbox\" name=\"delete\" value=\"delete\"><input type=\"submit\" value=\"Submit\"><br>";
		echo "</form>";
	}
} else {
	echo "There are no $globalText classes defined.<br>";
}
$result->free();
echo "</div>";




echo "<br><br>";





// Subnets
echo "<div>";
$sql = "SELECT `dsID`,`dsSubnet`,`dsNetMask`,`dsOptionSubnetMask`,`dsRangeDynamicBootpStart`,`dsRangeDynamicBootpEnd`,`dsDefaultLeaseTime`,`dsMaxLeaseTime`,`dsOptionRouters`,`dsOptionDomainNameServers`,`dsOptionNtpServers`,`dsNextServer`,`dsCustomArea1`,`dsCustomArea2`,`dsCustomArea3` FROM dhcpSubnets ORDER BY `dsID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "$subnetText(s):";
	while($row = $result->fetch_assoc()) {
		$dsID = trim(htmlspecialchars($row["dsID"]));
		$dsSubnet = trim(htmlspecialchars($row["dsSubnet"]));
		$dsNetMask = trim(htmlspecialchars($row["dsNetMask"]));
		$dsOptionSubnetMask = trim(htmlspecialchars($row["dsOptionSubnetMask"]));
		$dsRangeDynamicBootpStart = trim(htmlspecialchars($row["dsRangeDynamicBootpStart"]));
		$dsRangeDynamicBootpEnd = trim(htmlspecialchars($row["dsRangeDynamicBootpEnd"]));
		$dsDefaultLeaseTime = trim(htmlspecialchars($row["dsDefaultLeaseTime"]));
		$dsMaxLeaseTime = trim(htmlspecialchars($row["dsMaxLeaseTime"]));
		$dsOptionRouters = trim(htmlspecialchars($row["dsOptionRouters"]));
		$dsOptionDomainNameServers = trim(htmlspecialchars($row["dsOptionDomainNameServers"]));
		$dsOptionNtpServers = trim(htmlspecialchars($row["dsOptionNtpServers"]));
		$dsNextServer = trim(htmlspecialchars($row["dsNextServer"]));
		$dsCustomArea1 = trim(htmlspecialchars($row["dsCustomArea1"]));
		$dsCustomArea2 = trim(htmlspecialchars($row["dsCustomArea2"]));
		$dsCustomArea3 = trim(htmlspecialchars($row["dsCustomArea3"]));
		echo "<form action=\"$formAction\" method=\"post\">";
		echo "<br>";
		echo "<input type=\"hidden\" name=\"type\" value=\"$existingSubnet\">";
		echo "<input type=\"hidden\" name=\"id\" value=\"$dsID\">";
		echo "<font color=\"$requiredColor\">*</font>$subnetText <input type=\"text\" name=\"dsSubnet\" value=\"$dsSubnet\"> <font color=\"$requiredColor\">*</font>$maskText <input type=\"text\" name=\"dsNetMask\" value=\"$dsNetMask\"> {<br>";
		echo "$tab<font color=\"$requiredColor\">*</font>range dynamic-bootp <input type=\"text\" name=\"dsRangeDynamicBootpStart\" value=\"$dsRangeDynamicBootpStart\"> <input type=\"text\" name=\"dsRangeDynamicBootpEnd\" value=\"$dsRangeDynamicBootpEnd\">;<br>";
		echo "$tab option subnet-mask <input type=\"text\" name=\"dsNetMask\" value=\"$dsNetMask\">;<br>";
		echo "$tab default-lease-time <input type=\"text\" name=\"dsDefaultLeaseTime\" value=\"$dsDefaultLeaseTime\">;<br>";
		echo "$tab max-lease-time <input type=\"text\" name=\"dsMaxLeaseTime\" value=\"$dsMaxLeaseTime\">;<br>";
		echo "$tab option routers <input type=\"text\" name=\"dsOptionRouters\" value=\"$dsOptionRouters\">;<br>";
		echo "$tab option domain-name-servers <input type=\"text\" name=\"dsOptionDomainNameServers\" value=\"$dsOptionDomainNameServers\">;<br>";
		echo "$tab option ntp-servers <input type=\"text\" name=\"dsOptionNtpServers\" value=\"$dsOptionNtpServers\">;<br>";
		echo "$tab next-server <input type=\"text\" name=\"dsNextServer\" value=\"$dsNextServer\">;<br>";
		echo "$tab Custom Area 1: <input type=\"text\" name=\"dsCustomArea1\" value=\"$dsCustomArea1\"><br>";
		echo "$tab Custom Area 2: <input type=\"text\" name=\"dsCustomArea2\" value=\"$dsCustomArea2\"><br>";
		echo "$tab Custom Area 3: <input type=\"text\" name=\"dsCustomArea3\" value=\"$dsCustomArea3\"><br>";
		echo "<font color=\"red\">Delete entire subnet and all associated classes and reservations</font> <input type=\"checkbox\" name=\"delete\" value=\"delete\"> <input type=\"submit\" value=\"Submit\"><br>";
		echo "</form>";



		//Get classes for this subnet.
		$sql = "SELECT `dcID`,`dcClass`,`dcMatch`,`dcMatchOption1`,`dcMatchOption2`,`dcMatchOption3` FROM `dhcpClasses` WHERE dc_dsID = $dsID ORDER BY `dcID` ASC";
		$result2 = $link->query($sql);
		if ($result2->num_rows > 0) {
			echo "<br>";
			echo "$tab Classes inside of $subnetText $dsSubnet $maskText $dsNetMask:";
			while($row2 = $result2->fetch_assoc()) {
				$dcID = trim(htmlspecialchars($row2["dcID"]));
				$dcClass = trim(htmlspecialchars($row2["dcClass"]));
				$dcMatch = trim(htmlspecialchars($row2["dcMatch"]));
				$dcMatchOption1 = trim(htmlspecialchars($row2["dcMatchOption1"]));
				$dcMatchOption2 = trim(htmlspecialchars($row2["dcMatchOption2"]));
				$dcMatchOption3 = trim(htmlspecialchars($row2["dcMatchOption3"]));
				echo "<form action=\"$formAction\" method=\"post\">";
				echo "<br>";
				echo "<input type=\"hidden\" name=\"type\" value=\"$existingClass\">";
				echo "<input type=\"hidden\" name=\"id\" value=\"$dcID\">";
				echo "$tab<font color=\"$requiredColor\">*</font>class \"<input type=\"text\" name=\"dcClass\" value=\"$dcClass\">\" {<br>";
				echo "$tab$tab<font color=\"$requiredColor\">*</font>Match: <input type=\"text\" name=\"dcMatch\" value=\"$dcMatch\"><br>";
				echo "$tab$tab<font color=\"$requiredColor\">*</font>Match Option 1: <input type=\"text\" name=\"dcMatchOption1\" value=\"$dcMatchOption1\"><br>";
				echo "$tab$tab Match Option 2: <input type=\"text\" name=\"dcMatchOption2\" value=\"$dcMatchOption2\"><br>";
				echo "$tab$tab Match Option 3: <input type=\"text\" name=\"dcMatchOption3\" value=\"$dcMatchOption3\"><br>";
				echo "$tab }<br>";
				echo "$tab Copy to: <input type=\"radio\" name=\"copyOrMove\" value=\"copy\"> Move to: <input type=\"radio\" name=\"copyOrMove\" value=\"move\"> <select name=\"dc_dsID\"><option value=\"$dsID\">$subnetText $dsSubnet $maskText $dsNetMask</option>";
				echo "<option value=\"$globalIdentifier\">$globalText</option>";
				if ($subnetsExist = "1") {
					$i = 0;
					foreach ($dsIDs as $classDsID) {
						if ($classDsID != $dsID) {
							echo "<option value=\"$classDsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
							$i = $i + 1;
						}
					}
				}
				echo "</select><br>";
				echo "$tab Delete: <input type=\"checkbox\" name=\"delete\" value=\"delete\"><input type=\"submit\" value=\"Submit\"><br>";
				echo "</form>";
			}
		}
		$result2->free();



		//Get reservations for this subnet.
		$sql = "SELECT `drID`,`drMAC`,`drName`,`drFilename`,`drIP`,`drOptionDomainNameServers`,`drCustomArea1`,`drCustomArea2`,`drCustomArea3` FROM dhcpReservations WHERE `dr_dsID` = $dsID ORDER BY `drID` ASC";
		$result3 = $link->query($sql);
		if ($result3->num_rows > 0) {
			echo "<br>";
			echo "$tab Reservations inside of $subnetText $dsSubnet $maskText $dsNetMask:<br>";
			while($row3 = $result3->fetch_assoc()) {
				$drID = trim(htmlspecialchars($row3["drID"]));
				$drMAC = trim(htmlspecialchars($row3["drMAC"]));
				$drName = trim(htmlspecialchars($row3["drName"]));
				$drFilename = trim(htmlspecialchars($row3["drFilename"]));
				$drIP = trim(htmlspecialchars($row3["drIP"]));
				$drOptionDomainNameServers = trim(htmlspecialchars($row3["drOptionDomainNameServers"]));
				$drCustomArea1 = trim(htmlspecialchars($row3["drCustomArea1"]));
				$drCustomArea2 = trim(htmlspecialchars($row3["drCustomArea2"]));
				$drCustomArea3 = trim(htmlspecialchars($row3["drCustomArea3"]));
				echo "<form action=\"$formAction\" method=\"post\">";
				echo "<br>";
				echo "<input type=\"hidden\" name=\"type\" value=\"$existingReservation\">";
				echo "<input type=\"hidden\" name=\"id\" value=\"$drID\">";
				echo "$tab<font color=\"$requiredColor\">*</font>host <input type=\"text\" name=\"drName\" value=\"$drName\"> {<br>";
				echo "$tab$tab<font color=\"$requiredColor\">*</font>hardware ethernet <input type=\"text\" name=\"drMAC\" value=\"$drMAC\">;<br>";
				echo "$tab$tab fixed-address <input type=\"text\" name=\"drIP\" value=\"$drIP\">;<br>";
				echo "$tab$tab filename \"<input type=\"text\" list=\"filename\" value=\"$drFilename\"><datalist id=\"filename\">";foreach ($dfFileNames as $dfFileName) {echo "<option>$dfFileName</option>";}echo "</datalist>\";<br>";
				echo "$tab$tab option domain-name-servers <input type=\"text\" name=\"drOptionDomainNameServers\" value=\"$drOptionDomainNameServers\">;<br>";
				echo "$tab$tab Custom Area 1 <input type=\"text\" name=\"drCustomArea1\" value=\"$drCustomArea1\"><br>";
				echo "$tab$tab Custom Area 2 <input type=\"text\" name=\"drCustomArea2\" value=\"$drCustomArea2\"><br>";
				echo "$tab$tab Custom Area 3 <input type=\"text\" name=\"drCustomArea3\" value=\"$drCustomArea3\"><br>";
				echo "$tab}<br>";
				echo "$tab Copy to: <input type=\"radio\" name=\"copyOrMove\" value=\"copy\"> Move to: <input type=\"radio\" name=\"copyOrMove\" value=\"move\"> <select name=\"dr_dsID\"><option value=\"$dsID\">$subnetText $dsSubnet $maskText $dsNetMask</option>";
				echo "<option value=\"$globalIdentifier\">$globalText</option>";
				if ($subnetsExist = "1") {
					$i = 0;
					foreach ($dsIDs as $reservationDsID) {
						if ($reservationDsID != $dsID) {
						echo "<option value=\"$reservationDsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
						$i = $i + 1;
						}
					}
				}
				echo "</select><br>";
				echo "$tab Delete: <input type=\"checkbox\" name=\"delete\" value=\"delete\"><input type=\"submit\" value=\"Submit\"><br>";
				echo "</form>";
			}
		}
		$result3->free();
		echo "}<br>";		
	}
} else {
	echo "There are no $subnetText defined.<br>";
}
$result->free();
echo "</div>";






echo "<br><br>";






// DHCP Reservations
echo "<div>";
$sql = "SELECT `drID`,`drMAC`,`drName`,`drFilename`,`drIP`,`drOptionDomainNameServers`,`drCustomArea1`,`drCustomArea2`,`drCustomArea3` FROM dhcpReservations WHERE `dr_dsID` = $globalIdentifier ORDER BY `drID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "$globalText reservation(s):";
	while($row = $result->fetch_assoc()) {
		$drID = trim(htmlspecialchars($row["drID"]));
		$drMAC = trim(htmlspecialchars($row["drMAC"]));
		$drName = trim(htmlspecialchars($row["drName"]));
		$drFilename = trim(htmlspecialchars($row["drFilename"]));
		$drIP = trim(htmlspecialchars($row["drIP"]));
		$drOptionDomainNameServers = trim(htmlspecialchars($row["drOptionDomainNameServers"]));
		$drCustomArea1 = trim(htmlspecialchars($row["drCustomArea1"]));
		$drCustomArea2 = trim(htmlspecialchars($row["drCustomArea2"]));
		$drCustomArea3 = trim(htmlspecialchars($row["drCustomArea3"]));
		echo "<form action=\"$formAction\" method=\"post\">";
		echo "<br>";
		echo "<input type=\"hidden\" name=\"type\" value=\"$existingReservation\">";
		echo "<input type=\"hidden\" name=\"id\" value=\"$drID\">";
		echo "<font color=\"$requiredColor\">*</font>host <input type=\"text\" name=\"drName\" value=\"$drName\"> {<br>";
		echo "$tab<font color=\"$requiredColor\">*</font>hardware ethernet <input type=\"text\" name=\"drMAC\" value=\"$drMAC\">;<br>";
		echo "$tab fixed-address <input type=\"text\" name=\"drIP\" value=\"$drIP\">;<br>";
		echo "$tab filename \"<input type=\"text\" list=\"filename\" value=\"$drFilename\"><datalist id=\"filename\">";foreach ($dfFileNames as $dfFileName) {echo "<option>$dfFileName</option>";}echo "</datalist>\";<br>";
		echo "$tab option domain-name-servers <input type=\"text\" name=\"drOptionDomainNameServers\" value=\"$drOptionDomainNameServers\">;<br>";
		echo "$tab Custom Area 1 <input type=\"text\" name=\"drCustomArea1\" value=\"$drCustomArea1\"><br>";
		echo "$tab Custom Area 2 <input type=\"text\" name=\"drCustomArea2\" value=\"$drCustomArea2\"><br>";
		echo "$tab Custom Area 3 <input type=\"text\" name=\"drCustomArea3\" value=\"$drCustomArea3\"><br>";
		echo "}<br>";
		echo "Copy to: <input type=\"radio\" name=\"copyOrMove\" value=\"copy\"> Move to: <input type=\"radio\" name=\"copyOrMove\" value=\"move\"> <select name=\"dr_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
		if ($subnetsExist = "1") {
			$i = 0;
			foreach ($dsIDs as $dsID) {
				echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
				$i = $i + 1;
			}
		}
		echo "</select><br>";
		echo "Delete: <input type=\"checkbox\" name=\"delete\" value=\"delete\"><input type=\"submit\" value=\"Submit\"><br>";
		echo "</form>";
	}
} else {
	echo "There are no $globalText reservations.<br>";
}
echo "</div>";





echo "<br><br>";




echo "<div>";
echo "<form action=\"$formAction\" method=\"post\">";
echo "New $globalText option:<br><br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$newGlobalOption\"><input type=\"text\" name=\"globalOption\" value=\"\">  <input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";



echo "<br><br>";




// New class
echo "<div>";
echo "<form action=\"$formAction\" method=\"post\">";
echo "New Class:<br><br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$newClass\">";
echo "<font color=\"$requiredColor\">*</font>class \"<input type=\"text\" name=\"dcClass\" value=\"\">\" {<br>";
echo "$tab<font color=\"$requiredColor\">*</font>Match: <input type=\"text\" name=\"dcMatch\" value=\"\"><br>";
echo "$tab<font color=\"$requiredColor\">*</font>Match Option 1: <input type=\"text\" name=\"dcMatchOption1\" value=\"\"><br>";
echo "$tab Match Option 2: <input type=\"text\" name=\"dcMatchOption2\" value=\"\"><br>";
echo "$tab Match Option 3: <input type=\"text\" name=\"dcMatchOption3\" value=\"\"><br>";
echo "}<br>";
echo "$globalText or $subnetText: <select name=\"dc_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
if ($subnetsExist = "1") {
	$i = 0;
	foreach ($dsIDs as $dsID) {
		echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
		$i = $i + 1;
	}
}
echo "</select><br>";
echo "<input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";




echo "<br><br>";




// New subnet
echo "<div>";
echo "<form action=\"$formAction\" method=\"post\">";
echo "New $subnetText:<br><br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$newSubnet\">";
echo "<font color=\"$requiredColor\">*</font>$subnetText <input type=\"text\" name=\"dsSubnet\" value=\"\"> <font color=\"$requiredColor\">*</font>$maskText <input type=\"text\" name=\"dsNetMask\" value=\"\"> {<br>";
echo "$tab<font color=\"$requiredColor\">*</font>range dynamic-bootp <input type=\"text\" name=\"dsRangeDynamicBootpStart\" value=\"\"> <input type=\"text\" name=\"dsRangeDynamicBootpEnd\" value=\"\">;<br>";
echo "$tab option subnet-mask <input type=\"text\" name=\"dsNetMask\" value=\"\">;<br>";
echo "$tab default-lease-time <input type=\"text\" name=\"dsDefaultLeaseTime\" value=\"\">;<br>";
echo "$tab max-lease-time <input type=\"text\" name=\"dsMaxLeaseTime\" value=\"\">;<br>";
echo "$tab option routers <input type=\"text\" name=\"dsOptionRouters\" value=\"\">;<br>";
echo "$tab option domain-name-servers <input type=\"text\" name=\"dsOptionDomainNameServers\" value=\"\">;<br>";
echo "$tab option ntp-servers <input type=\"text\" name=\"dsOptionNtpServers\" value=\"\">;<br>";
echo "$tab next-server <input type=\"text\" name=\"dsNextServer\" value=\"\">;<br>";
echo "$tab Custom Area 1: <input type=\"text\" name=\"dsCustomArea1\" value=\"\"><br>";
echo "$tab Custom Area 2: <input type=\"text\" name=\"dsCustomArea2\" value=\"\"><br>";
echo "$tab Custom Area 3: <input type=\"text\" name=\"dsCustomArea3\" value=\"\"><br>";
echo "}<br>";
echo "<input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";





echo "<br><br>";




// New DHCP Reservation
echo "<div>";
echo "<form action=\"$formAction\" method=\"post\">";
echo "New reservation:<br><br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$newReservation\">";
echo "<font color=\"$requiredColor\">*</font>host <input type=\"text\" name=\"drName\" value=\"\"> {<br>";
echo "$tab<font color=\"$requiredColor\">*</font>hardware ethernet <input type=\"text\" name=\"drMAC\" value=\"\">;<br>";
echo "$tab fixed-address <input type=\"text\" name=\"drIP\" value=\"\">;<br>";
echo "$tab filename \"<input type=\"text\" list=\"filename\"><datalist id=\"filename\">";foreach ($dfFileNames as $dfFileName) {echo "<option>$dfFileName</option>";}echo "</datalist>\";<br>";
echo "$tab option domain-name-servers <input type=\"text\" name=\"drOptionDomainNameServers\" value=\"\">;<br>";
echo "$tab Custom Area 1 <input type=\"text\" name=\"drCustomArea1\" value=\"\"><br>";
echo "$tab Custom Area 2 <input type=\"text\" name=\"drCustomArea2\" value=\"\"><br>";
echo "$tab Custom Area 3 <input type=\"text\" name=\"drCustomArea3\" value=\"\"><br>";
echo "}<br>";
echo "$globalText or $subnetText: <select name=\"dr_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
if ($subnetsExist = "1") {
	$i = 0;
	foreach ($dsIDs as $dsID) {
		echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
		$i = $i + 1;
	}
}
echo "</select><br>";
echo "<input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";



echo "<br><br>";



//-----------begin Temporary code---------------//
echo "</body>";
echo "</html>";
//-------------end temporary code--------------//


?>
