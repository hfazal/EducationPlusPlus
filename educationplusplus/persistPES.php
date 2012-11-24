<?php
	require 'epp_classes.php';

	$pesName = $_POST["pesName"];
	$pesPointValue = intval($_POST["pesPointValue"]);
	$pesDescription = $_POST["pesDescription"];
	$pesExpiryDate = $_POST["pesExpiryDate"];
	$requirementsActivity = array();
	$requirementsCondition = array();
	$requirementsPercentToAchieve = array();
	$requirementsCount;
	$formedRequirements = array();
	
	$reqAct = $_POST["reqAct"];
	$reqCond = $_POST["reqCond"];
	$reqGradeToAchieve = $_POST["reqGradeToAchieve"];
	
	foreach ($reqAct as $eachInput) {
		array_push($requirementsActivity, $eachInput);
	}
	
	foreach ($reqCond as $eachInput) {
		array_push($requirementsCondition, intval($eachInput));
	}
	
	foreach ($reqGradeToAchieve as $eachInput) {
		array_push($requirementsPercentToAchieve, intval($eachInput));
	}
	
	$requirementsCount = count($requirementsActivity);

	for ($i = 0; $i < $requirementsCount; $i++){
		$act = new Activity($requirementsActivity[$i], null);	//Fix this to draw info from DB
		$req = new Requirement($act, $requirementsCondition[$i], $requirementsPercentToAchieve[$i]);
		array_push($formedRequirements, $req);
	}
	
	$newPES = new PointEarningScenario($pesName, $pesPointValue, $pesDescription, $formedRequirements, new DateTime($pesExpiryDate), false);
	print $newPES;
?>