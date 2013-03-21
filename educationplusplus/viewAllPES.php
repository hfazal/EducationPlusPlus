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
 * @copyright  2013 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace educationplusplus with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
// Education++ Classes
require 'eppClasses/PointEarningScenario.php';
require 'eppClasses/Requirement.php';
require 'eppClasses/Activity.php';


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$deleted = optional_param('delete', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'viewAllPES', "viewAllPES.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/viewAllPes.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all PES
global $DB;
$allPES = $DB->get_records('epp_pointearningscenario',array('course'=>$course->id));
$arrayOfPESObjects = array();
$arrayOfIDsForPESObjects = array();

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo "<link rel='stylesheet' type='text/css' href='./css/usecaseboxes.css'>
	<div class='floatingdiv'>Use Case Scenario(s): 5.3.5, 5.3.6</div>";

if($isProfessor){
	// Display PES Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Point Earning Scenarios</h1>
			<p>To reward students, you can create scenarios in which they can earn points to spend on rewards. You can <a href="createAPES.php?id='. $cm->id .'">make such a scenario here</a>, or manage already created scenarios below.</p>
		  </div>';
}
else{
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Point Earning Scenarios</h1>
		<p>To earn points that you can spend in the store, check out the scenarios that your Professor has set up below.</p>
		<p>Don\'t see any scenarios? Tell your Professor to make some!</p>
	  </div>';
}


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

//Styles for output: pesName, pesPointValue, pesExpiryDate, pesDescription, pesRequirements

echo "	<style>
			.pesName 			{ font-weight:bold; font-size:x-large; }
			.pesPointValue 		{ font-style:italic; font-size:x-large; }
			.pesExpiryDate		{ color:red; font-size:medium; }
			.pesDescription		{ font-size:medium; }
			.pesRequirements	{  }
		</style>
	";

echo '	<script>
			function confirmDelete(pes){
				var x;
				var r = confirm("Are you sure you want to delete this Scenario? Students will no longer be able to earn points this way.");
				if (r==true){
					pes = "deletePES.php?id=' . $cm->id .'&pes=" + pes;
					window.location = pes;
				}
				else{}
			}
		</script>';
	

if($isProfessor){
	// Create only displayed to professor (not student)
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="createAPES.php?id='. $cm->id .'">create a new scenario in which students can earn points</a></div>');
	echo "<br/>";
}
if ($arrayOfPESObjects){
	for ($i=0; $i < count($arrayOfPESObjects); $i++){
		echo $OUTPUT->box_start();
		if($isProfessor){
			// Edit/Delete only displayed to professor (not student)
			echo '<div style="float:right"><a href="editPES.php?id=' . $cm->id .'&pes=' . $arrayOfIDsForPESObjects[$i] . '">edit</a> | <a href="#" onclick="confirmDelete(' . $arrayOfIDsForPESObjects[$i] . ')">delete</a></div>';
		}
		echo $arrayOfPESObjects[$i];
		echo $OUTPUT->box_end();
		echo "<br/>";
	}
}
else{
	echo $OUTPUT->box('<div style="width:100%;text-align:center;">no scenarios to earn points were found.</div>');
	echo "<br/>";
}

echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

