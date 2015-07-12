<?php
	/*
		This package is distributed with a composer.json file.
		If you have composer installed, you have to install the contents of the file
		It creates an autoload class that contains the Unirest Class.
		For users without composer, you need to download the Unirest class provided by Mashape.
		You can find it on Github.

		After getting the necessary autoloads, you need to require the vendor/autoload.php file
		so that the Unirest class can be included in your script. Then include the photoinfo.class.php
		class too.
	*/

	require_once("vendor/autoload.php");
	require_once("photoinfo.class.php");

	error_reporting(0); //suppress notices and warnings;

	use Unirest\Request; //We use the Request class thats created in the Unirest namespace to make life easier later on

	$photoInfo = new PhotoInfo(new Request()); //Instantiating the PhotoInfo class. The Request Class imported from the unirest namespace must be injected as a dependency into the constructor of the PhotoInfo class.

	$photo = "http://static.ddmcdn.com/gif/recipes/future-faces-04-200-130611.jpg"; //URL of the image you want to analyze

	$queryString = "SELECT * FROM $photo";

	$process = $photoInfo->execute($queryString);
	/*
		The execute method accepts a string as parameter. This string must be constructed exactly as a normal sql query
		would be constructed or writtten.
		
		the $photo variable (thats, the url of the image to be analyzed) acts as the table in this case and the features you want to get acts as the table columns. Features such as the age, gender, race, smiling, and so on of the person in the picture
		can be retrieved.
		For instance, you could say:
				"SELECT age, gender, race, smiling, pose FROM http://ngm.nationalgeographic.com/2013/10/changing-faces/img/01-williams-kelly.jpg";
		Using the * symbol means you want to select all features.
		You can print_r or var_dump the $process variable to get a full look at the values returned by the execute method
	*/

	echo "Predicted Age: ".$photoInfo->getAge()["value"]."<br/>";
	echo "Age Range: ".$photoInfo->getAge()["range"]."<br/>";
	/*
		The getAge method returns an array with the following keys 'value' and 'range'.
		the value key holds the predicted age of the candidate in the supplied photo
		while the range speicifes 'a range' that the candidate could fall in age-wise
	*/
	echo "Race: ".$photoInfo->getRace()["value"]."<br/>";
	echo "Confidence That the candidate is ".$photoInfo->getRace()["value"].": ".$photoInfo->getRace()["confidence"]."<br/>";
	/*
		The getRace method also returns an array with the following keys 'value' and 'confidence'.
		the value key holds the predicted race (black, asian or white) of the candidate in the supplied photo
		while the confidence specifies how sure the algorithm is that the predicted race is correct
	*/

	/* All other methods in the class follows this pattern. These methods are:
		=> getGlass() : returns a value that helps to determine whether the candidate is wearing glasses
		=> isSmiling() : returns a percentage indicating whether the candidate is smiling or not
		=> getGender() : returns the gender of the candidate in the supplied photo
		=> getPose() : returns a list of values for determining the candidate's pose in the photo.
		=> getImageHeight(): returns the height of the image
		=> getWidthHeight(): returns the width of the imageRace: Black
Confidence That the candidate is Black: 87.326
		=> getImageSrc(): returns the url of the image
		=> getImageID(): returns a unique ID that can be used for identifying this image. (probably in a database or directory)

	You can get more details about this methods and the class entirely from the photoinfo.class.php file.
	*/


	echo "Glasses: ".$photoInfo->getGlass()["value"].", ";
	echo "Confidence : ".$photoInfo->getGlass()["confidence"]."<br/>";

	echo "Gender: ".$photoInfo->getGender()["value"].", ";
	echo "Confidence : ".$photoInfo->getGender()["confidence"]."<br/>";

	echo "<img src = '".$photoInfo->getImageSrc()."' width='30%' height='300px' />";

	