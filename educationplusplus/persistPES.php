<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of educationplusplus
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage educationplusplus
 * @copyright  2012 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace educationplusplus with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require 'eppClasses/PointEarningScenario.php';
require 'eppClasses/Requirement.php';
require 'eppClasses/Activity.php';
	
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('educationplusplus', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $educationplusplus  = $DB->get_record('educationplusplus', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $educationplusplus  = $DB->get_record('educationplusplus', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $educationplusplus->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('educationplusplus', $educationplusplus->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'educationplusplus', 'view', "view.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

//Process PES
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

global $DB;

$allPES = $DB->get_records('epp_pointearningscenario',array('course'=>$course->id));
$arrayOfPESObjects = array();
$arrayOfIDsForPESObjects = array();

if ($allPES){
	foreach ($allPES as $rowPES){
		$allRequirements = $DB->get_records('epp_requirement',array('pointearningscenario'=>$rowPES->id));
		$arrayOfRequirements = array();
		
		foreach ($allRequirements as $rowRequirements){
			$activity = $DB->get_record('assign',array('id'=>$rowRequirements->activity));	
		
			array_push($arrayOfRequirements, new Requirement(new Activity($activity->name,null), intval($rowRequirements->cond), intval($rowRequirements->percenttoachieve)));
			// ( $activity, $condition, $percentToAchieve )
		}
		
		array_push($arrayOfPESObjects, new PointEarningScenario($rowPES->name, intval($rowPES->pointvalue), $rowPES->description, $arrayOfRequirements, new DateTime($rowPES->expirydate)));
		//( $name, $pointValue, $description, $requirementSet, $expiryDate, $deletedByProf )
		array_push($arrayOfIDsForPESObjects, $rowPES->id);
	}
}

/* CREATE PES OBJECT */
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
	$activityName = $DB->get_record('assign',array('id'=>intval($requirementsActivity[$i])));	
	$act = new Activity($activityName->name, null);	//Fix this to draw info from DB
	$req = new Requirement($act, $requirementsCondition[$i], $requirementsPercentToAchieve[$i]);
	array_push($formedRequirements, $req);
}

$newPES = new PointEarningScenario($pesName, $pesPointValue, $pesDescription, $formedRequirements, new DateTime($pesExpiryDate));

//CHECK FOR DUPLICATES
$duplicateFound = false;
for ($a=0; $a < count($arrayOfPESObjects); $a++){
	if (count($arrayOfPESObjects[$a]->requirementSet == count($newPES->requirementSet))){
		$matchesFoundCount = 0;
		for ($b=0; $b < count($arrayOfPESObjects[$a]->requirementSet); $b++){			
			$matchfound = false;
			for ($c=0; $c < count($newPES->requirementSet); $c++){
				if (strcmp(($newPES->requirementSet[$c]->activity->name), ($arrayOfPESObjects[$a]->requirementSet[$b]->activity->name))==0 &&
					strcmp(($newPES->requirementSet[$c]->condition), ($arrayOfPESObjects[$a]->requirementSet[$b]->condition))==0 &&
					strcmp(($newPES->requirementSet[$c]->percentToAchieve), ($arrayOfPESObjects[$a]->requirementSet[$b]->percentToAchieve))==0){
					$matchfound = true;
				}
			}
			if ($matchfound == true){
				$matchesFoundCount++;
			}
		}
		if ($matchesFoundCount == count($arrayOfPESObjects[$a]->requirementSet)){
			//match
			$duplicateFound = true;
		}
		else {
			//no match, wrong reqs
		}
	}
	else{
		//no match, wrong number of req
	}
}

if ($duplicateFound == false){
	//PERSIST TO epp_pointearningscenario AND epp_requirements
		$record 				= new stdClass();
		$record->course  		= intval($course->id);
		$record->name	 		= $pesName;
		$record->pointvalue 	= intval($pesPointValue);
		$record->description	= $pesDescription;
		$datetimeVersionOfExpiryDate = new DateTime ($pesExpiryDate);
		$record->expirydate 	= $datetimeVersionOfExpiryDate->format('Y-m-d H:i:s');
		$idOfPES = $DB->insert_record('epp_pointearningscenario', $record, true);
		
		for ($i = 0; $i < count($requirementsActivity); $i++){
			$newRequirement 						= new stdClass();
			$newRequirement->pointearningscenario	= intval($idOfPES);
			$newRequirement->activity 				= intval($requirementsActivity[$i]);
			$newRequirement->cond	 				= intval($requirementsCondition[$i]);
			$newRequirement->percenttoachieve		= intval($requirementsPercentToAchieve[$i]);
			$DB->insert_record('epp_requirement', $newRequirement, false);
		}
}
else{
	//Nothing Saved
}
// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');
//Styles for output: pesName, pesPointValue, pesExpiryDate, pesDescription, pesRequirements

echo "	<style>
			.pesName 			{ font-weight:bold; font-size:x-large; }
			.pesPointValue 		{ font-style:italic; font-size:x-large; }
			.pesExpiryDate		{ color:red; font-size:medium; }
			.pesDescription		{ font-size:medium; }
			.pesRequirements	{  }
		</style>
	";
if ($duplicateFound == false){
	echo $OUTPUT->box('The following Point Earning Scenario was successfully saved:<br/><br/>' . $newPES);
}
else { // ($duplicateFound == true)
	echo $OUTPUT->box('The following Point Earning Scenario was <strong>NOT</strong> saved as a scenario with the same requirements already exists:<br/><br/>' . $newPES);
}
echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>