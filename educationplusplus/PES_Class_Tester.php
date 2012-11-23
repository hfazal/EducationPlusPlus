<!DOCTYPE HTML>
<?php require 'epp_classes.php'; ?>
<html>
<head>
	<title>Point Earning Scenario Class Tester</title>
</head>
<body>
<?php
	//TESTER AREA TO TEST YOUR CLASSES
	//Activity 1
	$act1 = new Activity("Test 1", 20);
	
	//Activity 2
	$act2 = new Activity("Test 2", 20);
	
	//Requirement1
	$req1 = new Requirement($act1, ">", 50);
	
	//Requirement2
	$req2 = new Requirement($act2, ">", 50);
	
	//Array of Requirement Objects for a PES
	$arrayOfRequirements = array($req1, $req2);
	
	//PointEarningScenario
	$pes = new PointEarningScenario("Pass all Tests", 1000, "Pass all tests in this course and earn 1000 points", $arrayOfRequirements, new DateTime("now"), false);
	
	// View the PES
	print $pes;
?>
</body>
</html>

