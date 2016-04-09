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
$maskText = "mask";
$existingGlobalOption = "0";
$newGlobalOption = "1";
$existingClass = "2";
$newClass = "3";
$existingSubnet = "4";
$newSubnet = "5";
$existingReservation = "6";
$newReservation = "7";


// DHCP Global Options.
echo "<div>";
$sql = "SELECT `dgID`,`dgOption` FROM `dhcpGlobals` ORDER BY `dgID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "Global options:";
        while($row = $result->fetch_assoc()) {
                $dgID = trim(htmlspecialchars($row["dgID"]));
                $dgOption = trim(htmlspecialchars($row["dgOption"]));
		if ($dgOption != "") {
			echo "<form action=\"$formAction\" method=\"post\">";
			echo "<input type=\"hidden\" name=\"type\" value=\"$existingGlobalOption\"><input type=\"hidden\" name=\"id\" value=\"$dgID\"><input type=\"text\" name=\"globalOption\" value=\"$dgOption\"> Delete<input type=\"checkbox\" name=\"delete\" value=\"delete\"> <input type=\"submit\" value=\"Submit\"><br>";
			echo "</form";
		}
        }
}
$result->free();
echo "<form action=\"$formAction\" method=\"post\">";
echo "New Global Option:<br>";
echo "<input type=\"hidden\" name=\"type\" value=\"$newGlobalOption\"><input type=\"text\" name=\"globalOption\" value=\"\">  <input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";


echo "<br><br>";



// DHCP Global Classes

//	get list of subnets.
$dsSubnets = array();
$dsNetmasks = array();
$dsIDs = array();
$sql = "SELECT `dsSubnet`,`dsNetmask`,`dsID` FROM dhcpSubnets ORDER BY `dsID` ASC";
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

echo "<div>";
$sql = "SELECT `dcID`,`dcClass`,`dcMatch`,`dcMatchOption1`,`dcMatchOption2`,`dcMatchOption3` FROM `dhcpClasses` WHERE dc_dsID = $globalIdentifier ORDER BY `dcID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
	echo "Global Classes:";
	while($row = $result->fetch_assoc()) {
		$dcID = trim(htmlspecialchars($row["dcID"]));
		$dcClass = trim(htmlspecialchars($row["dcClass"]));
		$dcMatch = trim(htmlspecialchars($row["dcMatch"]));
		$dcMatchOption1 = trim(htmlspecialchars($row["dcMatchOption1"]));
		$dcMatchOption2 = trim(htmlspecialchars($row["dcMatchOption2"]));
		$dcMatchOption3 = trim(htmlspecialchars($row["dcMatchOption3"]));
		if ($dcClass != "" && $dcMatch != "" && $dcMatchOption1 != "") {
                        echo "<form action=\"$formAction\" method=\"post\">";
                        echo "<input type=\"hidden\" name=\"type\" value=\"$existingClass\"><input type=\"hidden\" name=\"id\" value=\"$dcID\">Class: <input type=\"text\" name=\"dcClass\" value=\"$dcClass\">br>Match: <input type=\"text\" name=\"dcMatch\" value=\"$dcMatch\"><br>Match Option 1: <input type=\"text\" name=\"dcMatchOption1\" value=\"$dcMatchOption1\"><br>Match Option 2: <input type=\"text\" name=\"dcMatchOption2\" value=\"$dcMatchOption2\"><br>Match Option 3: <input type=\"text\" name=\"dcMatchOption3\" value=\"$dcMatchOption3\"><br>Global or Subnet: <select name=\"dc_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
			if ($subnetsExist = "1") {
				$i = 0;
				foreach ($dsIDs as $dsID) {
					echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
				$i = $i + 1;
				}
			}
			echo "</select><br>Delete <input type=\"checkbox\" name=\"delete\" value=\"delete\"> <input type=\"submit\" value=\"Submit\"><br>";
                        echo "</form>";
                }
	}
}
$result->free();



// New class
echo "New Class:";
echo "<form action=\"$formAction\" method=\"post\">";
echo "<input type=\"hidden\" name=\"type\" value=\"$newClass\">Class: <input type=\"text\" name=\"dcClass\" value=\"\"><br>Match: <input type=\"text\" name=\"dcMatch\" value=\"\"><br>Match Option 1: <input type=\"text\" name=\"dcMatchOption1\" value=\"\"><br>Match Option 2: <input type=\"text\" name=\"dcMatchOption2\" value=\"\"><br>Match Option 3: <input type=\"text\" name=\"dcMatchOption3\" value=\"\"><br>Global or Subnet: <select name=\"dc_dsID\"><option value=\"$globalIdentifier\">$globalText</option>";
if ($subnetsExist = "1") {
	$i = 0;
	foreach ($dsIDs as $dsID) {
		echo "<option value=\"$dsID\">$subnetText $dsSubnets[$i] $maskText $dsNetmasks[$i]</option>";
		$i = $i + 1;
	}
}
echo "</select><br> <input type=\"submit\" value=\"Submit\"><br>";
echo "</form>";
echo "</div>";



// Subnets
echo "<div>";
$sql = "SELECT `dsSubnet`,`dsNetmask`,`dsID` FROM dhcpSubnets ORDER BY `dsID` ASC";
$result = $link->query($sql);
if ($result->num_rows > 0) {
        $subnetsExist = "1";
        while($row = $result->fetch_assoc()) {
                $dsSubnets[] = trim(htmlspecialchars($row["dsSubnet"]));
                $dsNetmasks[] = trim(htmlspecialchars($row["dsNetmask"]));
                $dsIDs[] = trim(htmlspecialchars($row["dsID"]));
        }
}





echo "</div>";










echo "</body>";
echo "</html>";
?>
