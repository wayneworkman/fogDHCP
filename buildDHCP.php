<?php








// Write commands for this user, for this setting, to the setting's file.
$file = $PathToSMBShares . $JaneSettingsNickName . ".ps1";
if (file_exists($file)) {
	$current = file_get_contents($file);
	$current = $current . $ThisCOMMAND;	
} else {
	$current = $ThisCOMMAND;
}
file_put_contents($file, $current);
?>
