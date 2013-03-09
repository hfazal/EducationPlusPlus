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

$iid = null;
if (isset($_POST['buy'])){
	$iid = intval($_POST['buy']);
}

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

$PAGE->set_url('/mod/educationplusplus/storefront.php', array('id' => $cm->id));
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
if($allIncentives){
	$epp_student_redeemed_rewards = $DB->get_records_select($table,$select);
	$epp_student_redeemed_badges = $DB->get_records_select($table2,$select);
}
else{
	$epp_student_redeemed_rewards = null;
	$epp_student_redeemed_badges = null;
}
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

	
// Find Student's Point Balance
$eppStudentRecord = $DB->get_record('epp_student',array('course_id'=>$course->id, 'student_id'=>$USER->id));
//$eppStudentRecord->currentpointbalance
	
// BUYING HAPPENS HERE
// SUPER SECURE VALIDATION RIGHT HERE!
/*	Things to check
	Error 1. Fake IID
	Error 2. Incentive Selected is for Wrong Course
	Error 3. Store Visibility
	Error 4. Deleted Status
	Error 5. Expired (Reward ONLY)
	Error 6. Quantity Status
	Error 7. Point Balance (Not enough in point Balance)*/

if ($iid){
	//Get that Incentive
	$incentive = $DB->get_record('epp_incentive',array('id'=>$iid));
	$badgeChecker = $DB->get_record('epp_badge',array('incentive_id'=>$iid));
	$rewardChecker = $DB->get_record('epp_reward',array('incentive_id'=>$iid));
	
	//Vars to check against for errors
	$courseid = -1;
	$storeVisibility = 0;	// 0 means NOT VISIBLE
	$deletedByProf = 1;		// 0 means NOT DELETED
	$expirydateFormatted = null;
	$quantity = -1;
	$cost = -1;
	$userHasPurchasedThisIncentiveThisManyTimes = 0;
	
	//1. CHECK FOR FAKE IID
	if ($badgeChecker || $rewardChecker){
		if ($badgeChecker){
			// Make Instance of Badge/Incentive
			$badgeToBuy = new Badge($incentive->name, intval($incentive->qtyperstudent), intval($incentive->storevisibility), intval($incentive->priceinpoints), $incentive->icon, intval($incentive->deletebyprof), new DateTime($incentive->datecreated));
			
			//Details of this Badge
			$courseid = intval($incentive->course_id);
			$storeVisibility = $badgeToBuy->parentGetter("storeVisibility");
			$deletedByProf = $badgeToBuy->parentGetter("deletedByProf");
			// $expirydateFormatted = $badgeToBuy->expirydate;		//Commented out as badges do not have expiry dates
			$quantity = $badgeToBuy->parentGetter("qtyPerStudent");
			$cost = $badgeToBuy->parentGetter("priceInPoints");
			//Find the Quantity
			foreach($epp_student_redeemed_badges as $redeemedBadge){
				if (intval($redeemedBadge->incentive_id) == intval($iid)){
					$userHasPurchasedThisIncentiveThisManyTimes++;
				}
			}
		}
		else{	// ($rewardChecker)
			// Make Instance of Reward/Incentive
			$rewardToBuy = new Reward($incentive->name, intval($incentive->qtyperstudent), intval($incentive->storevisibility), intval($incentive->priceinpoints), $incentive->icon, intval($incentive->deletebyprof), new DateTime($incentive->datecreated), $rewardChecker->prize,  new DateTime($rewardChecker->expirydate));
			
			//Details of this Reward
			$courseid = intval($incentive->course_id);
			$storeVisibility = $rewardToBuy->parentGetter("storeVisibility");
			$deletedByProf = $rewardToBuy->parentGetter("deletedByProf");
			$expirydateFormatted = $rewardToBuy->getExpiryDateString();
			$quantity = $rewardToBuy->parentGetter("qtyPerStudent");
			$cost = $rewardToBuy->parentGetter("priceInPoints");
			//Find the Quantity
			foreach($epp_student_redeemed_rewards as $redeemedRewards){
				if (intval($redeemedRewards->incentive_id) == intval($iid)){
					$userHasPurchasedThisIncentiveThisManyTimes++;
				}
			}
		}
		
		//Find Todays Date and Put it in the Same Format as
		$todayFormatted = strtotime(date("Y-m-d"));
		$expirydateFormatted = strtotime($expirydateFormatted);
		
		// Now that we have all data needed, check against requirements 2-7
		if (($courseid == $course->id) && (intval($storeVisibility) == 1) && ($deletedByProf == 0) && ($badgeChecker || ($expirydateFormatted >= $todayFormatted) ) && ($quantity > $userHasPurchasedThisIncentiveThisManyTimes) && ($eppStudentRecord->currentpointbalance >= $cost && $cost != -1 )){
			// Make the Purchase
			$successful = 0;
			$student_record 					= new stdClass();
			$student_record->student_id 		= intval($USER->id);
			$student_record->incentive_id 		= intval($iid);
			$timerightnow						= new DateTime();
			$student_record->datepurchased		= $timerightnow->format('Y-m-d H:i:s');
			$student_record->priceofpurchase	= $cost;
			
			if ($badgeChecker){
				// Deduct Points
				$eppStudentRecord->currentpointbalance = $eppStudentRecord->currentpointbalance - $cost;
				$DB->update_record('epp_student', $eppStudentRecord);
				$successful = $DB->insert_record('epp_student_badge', $student_record, true);
			}
			else{
				//Add Reporting Columns
				$student_record->reportdismissed	= 0;
				$student_record->reportnew			= 1;
				
				// Deduct Points
				$eppStudentRecord->currentpointbalance = $eppStudentRecord->currentpointbalance - $cost;
				$DB->update_record('epp_student', $eppStudentRecord);
				$successful = $DB->insert_record('epp_student_reward', $student_record, true);
			}
			if ($successful){
				echo "Purchase Successful!!!!!!!";
			}
			
			// For the sake of refreshing quantity.....
			$epp_student_redeemed_rewards = $DB->get_records_select($table,$select);
			$epp_student_redeemed_badges = $DB->get_records_select($table2,$select);
		}
		else {	//AN ERROR HAPPENED
			if ($courseid != $course->id){
				echo "Error 2: Incorrect Course";
			}
			else{
				if ((intval($storeVisibility) != 1) || ($deletedByProf != 0) || (!$badgeChecker && ($expirydateFormatted < $todayFormatted))){
					echo "Error 3, 4 or 5: Incentive is no longer available";
				}
				else {
					if ($quantity <= $userHasPurchasedThisIncentiveThisManyTimes){
						echo "Error 6: This incentive is Sold Out!";
					}
					else{
						echo "Error 7: You do not have enough points to purchase this incentive!";
					}
				}
			}
		}
	}
	else {	//FAKE IID
		echo "Error 1: Fake IID";
	}
}	


// Repull all incentives, but this time ignore deleted and hidden incentives
$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id, 'deletebyprof'=>0, 'storevisibility'=>1));
// Display Store Inventory
if ($allIncentives){
	echo '<span style="color:red;font-weight:bold;">';
	$disableBuy = false;
	if (!$eppStudentRecord){
		echo 'You are not a student, so you will not be able to buy anything';
		$disableBuy = true;
	}
	else{
		echo $eppStudentRecord->currentpointbalance . ' is your Point Balance';
	}
	echo '</span>';
	
	echo '<div id="storewrapper" style="height:500px;width:900px;overflow:auto;margin:0 auto;">';
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
			
			echo $currentBadge->getPurchaseTile($remainingQty, $cm->id, $badgeChecker->incentive_id, $disableBuy);
			//<input style="float:right;border:none;" type="submit" name="purchase" id="purchase" value="Buy" />
		}
		else{ //$rewardChecker
			$currentReward = new Reward($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated), $rewardChecker->prize,  new DateTime($rewardChecker->expirydate));
			
			//Find the Quantity
			$counter = 0;
			
			foreach($epp_student_redeemed_rewards as $redeemedReward){
				if (intval($redeemedReward->incentive_id) == intval($rewardChecker->incentive_id)){
					$counter++;
				}
			}
			
			$allowed = $currentReward->quantityAllowedPerStudent();
			$remainingQty = $allowed - $counter;
			
			echo $currentReward->getPurchaseTile($remainingQty, $cm->id, $rewardChecker->incentive_id, $disableBuy);
			//<input style="float:right;border:none;" type="submit" name="purchase" id="purchase" value="Buy" />
		}
	}
	echo '</div>';
}
else{
	echo $OUTPUT->box('<div style="width:100%;text-align:center;">no incentives to display.</div>');
}

echo '<br/><br/><br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

