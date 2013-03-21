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
require 'eppClasses/Badge.php';
	
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$IncentiveID = optional_param('badge', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'persistUpdatedBadge', "persistUpdatedBadge.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/persistUpdatedBadge.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
	echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo "<link rel='stylesheet' type='text/css' href='./css/usecaseboxes.css'>
	<div class='floatingdiv'>Use Case Scenario(s): 5.2.6</div>";

// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

if($isProfessor){
	// Display Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Persist Badge</h1>
			<p>To reward students, you can create incentives for them to purchase such as a Badge</p>
			<p>A Badge is a trophy that students can purchase and display on the school leaderboard for bragging rights</p>
			<p>Saving your Badge</p>
		  </div>';

	//Process Badge
	$incentiveName = $_POST["incentiveName"];
	$incentivePrice = $_POST["incentivePrice"];

	if (isset($_POST['storevis'])) {
		$storevisTrue = 1;
	}
	else{
		$storevisTrue = 0;
	
	}

	$incentiveImg = null;
	if ((!empty($_FILES["incentiveImg"])) && ($_FILES['incentiveImg']['error'] == 0)) {
   		 $incentiveImg = file_get_contents($_FILES["incentiveImg"]["tmp_name"]);
 		 $incentiveImg = base64_encode($incentiveImg);
	}

	if (isset($_POST["incentiveName"]) && isset($_POST["incentivePrice"]) && isset($_FILES["incentiveImg"])){
		//echo $incentiveImg;
		//if ($incentiveType == 'reward')




		global $DB;

		$allIncentive = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
		$arrayOfIncentiveObjects = array();
		$arrayOfIDsForIncentiveObjects = array();

		if ($allIncentive){
			foreach ($allIncentive as $rowIncentive){
				array_push($arrayOfIncentiveObjects, new Incentive($rowIncentive->name, intval($rowIncentive->qtyperstudent), $rowIncentive->storevisibility, intval($rowIncentive->priceinpoints), $rowIncentive->icon, $rowIncentive->deletebyprof, $rowIncentive->datecreated));
				array_push($arrayOfIDsForIncentiveObjects, $rowIncentive->id);
			}
		}


		//$newIncentive = new Incentive($incentiveName, $incentiveQty, $storevisTrue, $incentiveType, $incentivePrice, $incentiveImg, false);

		//if($incentiveType == "reward"){
				$record                       = new stdClass();
				$record->id                   = intval($IncentiveID);
				$record->course_id            = intval($course->id);
				$record->name                 = $incentiveName;
				$record->priceinpoints        = intval($incentivePrice);
				$record->storevisibility      = intval($storevisTrue);

				if ($incentiveImg!=null){
				$record->icon                 = $incentiveImg;
				}

				$record->deletebyprof         =  0;
				$datetimeVersionOfDateCreated = new DateTime();
				$record->datecreated          = $datetimeVersionOfDateCreated->format('Y-m-d H:i:s');

				$DB->update_record('epp_incentive', $record);

		//}
		echo $OUTPUT->box('The Badge was updated');
	}
	else{
		echo $OUTPUT->box('Incomplete Data');
	}
	echo "<br/>";
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="viewAllIncentives.php?id='. $cm->id .'">Return to the Education++: Manage Incentives Page</a></div>');
}
else{
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Rewards</h1>
			<p><a href="storefront.php?id='. $cm->id .'">Visit the Store here</a></p>
		  </div><br/>';
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
}
// Finish the page
echo $OUTPUT->footer();

?>