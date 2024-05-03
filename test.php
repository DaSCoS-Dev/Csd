<?php
$csvFile = fopen('modeData.csv', 'r');
$data = array();

while (($row = fgetcsv($csvFile, null, ",")) !== false) {
	$rowData = array();
	for ($i = 0; $i < count($row); $i++) {
		$rowData[$i] = trim($row[$i]);
	}
	$data[] = $rowData;
}
fclose($csvFile);
$modes = array("A","B","C","D");
foreach ($data as $value)
{
	$selectedValue = $value[1]; // Always correct.
	echo $selectedValue;
	echo '<div id="row">';
	echo "Mode ";
	echo '<select name="options" id="options">';
	for($i = 0;$i < count($modes);$i++)
	{
		// This
		if($selectedValue == $modes[$i]) {
			echo "<option selected='selected' value='$modes[$i]'>$modes[$i]</option>";
		} else {
			echo "<option value='$modes[$i]'>$modes[$i]</option>";
		}
	}
	echo '</select>';
	echo '<span style="display: inline-block; width: 1ch;">&#9;</span>';
	echo "<span id='name'>$value[0]</span>";
	echo '</div>';
}
?>