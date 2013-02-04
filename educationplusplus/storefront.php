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
require 'eppClasses/Reward.php';
require 'eppClasses/Badge.php';

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$deleted = optional_param('delete', 0, PARAM_INT);
$iid = optional_param('iid', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'createAPES', "createAPES.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/createAPES.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

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

// Find Student Transaction History
$table = 'epp_student_reward';
$table2 = 'epp_student_badge';
$select = "student_id = " . $USER->id . " AND (";

foreach($allIncentives as $courseIncentive){
	$select = $select . "incentive_id = " . $courseIncentive->id . " OR ";
}
$select = substr($select, 0, -4);
$select = $select . ")";
$epp_student_redeemed_rewards = $DB->get_records_select($table,$select);
$epp_student_redeemed_badges = $DB->get_records_select($table2,$select);

//echo var_dump($epp_student_redeemed_rewards);

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

echo $OUTPUT->heading('Education++: Store Front');


echo "	<style>
			.badge 				{ border: thin red solid; width:400px; }
			.badgeName			{ font-weight:bold; }
			.badgePrice			{ }
			.reward 			{ border: thin blue solid; width:400px; }
			.rewardName			{ font-weight:bold; }
			.rewardPrice		{}
			.rewardExpiryDate	{}
			.rewardDescription	{}
		</style>
	";

	
// BUYING HAPPENS HERE
if ($iid){
	$badgeChecker = $DB->get_record('epp_badge',array('incentive_id'=>$iid));
	$rewardChecker = $DB->get_record('epp_reward',array('incentive_id'=>$iid));
	$successful = 0;

	$student_record 					= new stdClass();
	$student_record->student_id 		= intval($USER->id);
	$student_record->incentive_id 		= intval($iid);
	$timerightnow						= new DateTime();
	$student_record->datepurchased		= $timerightnow->format('Y-m-d H:i:s');
	
	if ($badgeChecker){
		$successful = $DB->insert_record('epp_student_badge', $student_record, true);
	}
	else{
		$successful = $DB->insert_record('epp_student_reward', $student_record, true);
	}
	
	if ($successful){
		echo "Purchase Successful!!!!!!!";
	}
	$epp_student_redeemed_rewards = $DB->get_records_select($table,$select);
	$epp_student_redeemed_badges = $DB->get_records_select($table2,$select);
}	


if ($allIncentives){
	foreach ($allIncentives as $rowIncentive){
		$badgeChecker = $DB->get_record('epp_badge',array('incentive_id'=>$rowIncentive->id));
		$rewardChecker = $DB->get_record('epp_reward',array('incentive_id'=>$rowIncentive->id));
		
		if ($badgeChecker){
			$currentBadge = new Badge($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated));
			
			//Find the Quantity
			$counter = 0;
			
			foreach($epp_student_redeemed_badges as $redeemedBadge){
				if (intval($redeemedBadge->incentive_id) == intval($badgeChecker->incentive_id)){
					$counter++;
				}
			}
			
			$allowed = 1;
			$remainingQty = $allowed - $counter;
			
			if ($remainingQty == 0) {
				echo '<div class="badge">' . $currentBadge . '<span style="color:green;font-weight:bold;">ALREADY PURCHASED</span></div>';
			}
			else{
				echo '<div class="badge">' . $currentBadge . '<span style="color:green;font-weight:bold;">AVAILABLE</span><a href="storefront.php?id=' . $cm->id . '&iid=' . $badgeChecker->incentive_id . '"><img style="float:right;border:none;" src="pix/buy.png" alt="Buy" /></a></div>';
			}
			echo '<br/>';
			
		}
		else{ //$rewardChecker
			$currentReward = new Reward($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated), $rewardChecker->prize,  new DateTime($rewardChecker->expirydate));
			//echo var_dump($currentReward);
			
			//Find the Quantity
			$counter = 0;
			
			foreach($epp_student_redeemed_rewards as $redeemedReward){
				if (intval($redeemedReward->incentive_id) == intval($rewardChecker->incentive_id)){
					$counter++;
				}
			}
			
			$allowed = $currentReward->quantityAllowedPerStudent();
			$remainingQty = $allowed - $counter;
			
			if ($remainingQty == 0) {
				echo '<div class="reward">' . $currentReward . '<span style="color:red">SOLD OUT</span></div>';
			}
			else{
				echo '<div class="reward">' . $currentReward . 'Quantity: ' . $remainingQty .  '/' . $allowed . '<a href="storefront.php?id=' . $cm->id . '&iid=' . $rewardChecker->incentive_id . '"><img style="float:right;border:none;" src="pix/buy.png" alt="Buy" /></a></div>';
			}
			echo '<br/>';
		}
	}
}
else{
	echo $OUTPUT->box('<div style="width:100%;text-align:center;">no incentives to display.</div>');
	echo "<br/>";
}

//echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

