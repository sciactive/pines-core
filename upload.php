<?php
header("content-type: text/html"); // the return type must be text/html
define('D_RUN', true);
require('configure.php');
//if file has been sent successfully:
if (isset($_FILES['image']['tmp_name'])) {
	// open the file
	$img = $_FILES['image']['tmp_name'];
	$himage = fopen ( $img, "r"); // read the temporary file into a buffer
	$image = fread ( $himage, filesize($img) );
	fclose($himage);
	//if image can't be opened, either its not a valid format or even an image:
	if ($image === FALSE) {
		echo "{status:'Error Reading Uploaded File.'}";
		return;
	}
	// create a new random numeric name to avoid rewriting other images already on the server...
	$ran = rand ();
	$ran2 = $ran.".";
	// define the uploading dir
	$path = $config->setting_upload;
	// join path and name
	$path = $path . 'images/' . $ran2.'jpg';
	// copy the image to the server, alert on fail
	$hout=fopen($path,"w");
	fwrite($hout,$image);
	fclose($hout);
	//you'll need to modify the path here to reflect your own server.
	$path = $config->full_location . $path;
	echo "{status:'UPLOADED', image_url:'$path'}";
} else {
	echo "{status:'No file was submitted'}";
}
?>
