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
require 'eppClasses/Incentive.php';
require 'eppClasses/Reward.php';
require 'eppClasses/Badge.php';


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

add_to_log($course->id, 'educationplusplus', 'viewAllIncentives', "viewAllIncentives.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/viewAllIncentives.php', array('id' => $cm->id));
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
$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
$arrayOfIDsForRewardObjects = array();
$arrayOfIDsForBadgeObjects = array();
$arrayOfReward = array();
$arrayOfBadge = array();

// Output starts here
echo $OUTPUT->header();

if($allIncentives){
	foreach ($allIncentives as $rowIncentive){
		$allRewards = $DB->get_record('epp_reward',array('incentive_id'=>$rowIncentive->id));
		$allBadges = $DB->get_record('epp_badge',array('incentive_id'=>$rowIncentive->id));
		if ($allRewards){
			foreach($allRewards as $rowReward){
				array_push($arrayOfReward, new Reward($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated), $allRewards->prize, new DateTime($allRewards->expirydate)  ));
				array_push($arrayOfIDsForRewardObjects, $rowIncentive->id);
				break;
			}
		}
		if($allBadges){
			foreach($allBadges as $rowBadge){
				array_push($arrayOfBadge, new Badge($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated)));	
				array_push($arrayOfIDsForBadgeObjects, $rowIncentive->id);
				break;
			}
		}
	}
	
}
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

if($isProfessor){
	// Display Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Rewards</h1>
			<p>To reward students, you can create incentives for them to purchase. You can <a href="createAReward.php?id='. $cm->id .'">create a reward</a>, <a href="createABadge.php?id='. $cm->id .'">create a badge</a>, or manage already created rewards below.</p>
			<p>A Badge is a trophy that students can purchase and display on the school leaderboard for bragging rights, while a Reward would be something tangeable like dropping their lowest quiz</p>
		  </div>';
}
else{
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Rewards</h1>
			<p><a href="storefront.php?id='. $cm->id .'">Visit the Store here</a></p>
		  </div>';
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
			function confirmDeleteReward(pes){
				var x;
				var r = confirm("Are you sure you want to delete this Reward? Students will no longer be able to purchase this Reward");
				if (r==true){
					pes = "deleteReward.php?id=' . $cm->id .'&reward=" + pes;
					window.location = pes;
				}
				else{}
			}
		</script>';

echo '	<script>
			function confirmDeleteBadge(pes){
				var x;
				var r = confirm("Are you sure you want to delete this Badge? Students will no longer be able to purchase this Badge");
				if (r==true){
					pes = "deleteBadge.php?id=' . $cm->id .'&badge=" + pes;
					window.location = pes;
				}
				else{}
			}
		</script>';

if($isProfessor){
	// Create only displayed to professor (not student)
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="createAnIncentive.php?id='. $cm->id .'">Create a new incentive</a></div>');
	echo "<br/>";

	if ($arrayOfReward){
		for ($i=0; $i < count($arrayOfReward); $i++){
			if ($arrayOfReward[$i]->parentGetter("deletedByProf") == 0){
				echo '<div style="width:500px;">';
				echo $OUTPUT->box_start();
				if($isProfessor){
					// Edit/Delete only displayed to professor (not student)
					echo '<div style="float:right"><a href="editReward.php?id=' . $cm->id .'&reward=' . $arrayOfIDsForRewardObjects[$i] . '">edit</a> | <a href="#" onclick="confirmDeleteReward(' . $arrayOfIDsForRewardObjects[$i] . ')">delete</a></div>';
				}
				echo $arrayOfReward[$i];
				echo $OUTPUT->box_end();
				echo '</div>';
				echo "<br/>";
			}
		}
	}

	if ($arrayOfBadge){
		for ($i=0; $i < count($arrayOfBadge); $i++){
			if ($arrayOfBadge[$i]->parentGetter("deletedByProf") == 0){
				echo '<div style="width:500px;">';
				echo $OUTPUT->box_start();
				if($isProfessor){
					// Edit/Delete only displayed to professor (not student)
					echo '<div style="float:right"><a href="editBadge.php?id=' . $cm->id .'&badge=' . $arrayOfIDsForBadgeObjects[$i] . '">edit</a> | <a href="#" onclick="confirmDeleteBadge(' . $arrayOfIDsForBadgeObjects[$i] . ')">delete</a></div>';
				}
				echo $arrayOfBadge[$i];
				echo $OUTPUT->box_end();
				echo '</div>';
				echo "<br/>";
			}
		}
	}
	else{
		echo $OUTPUT->box('<div style="width:100%;text-align:center;">no scenarios to earn points were found.</div>');
		echo "<br/>";
	}
}
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

