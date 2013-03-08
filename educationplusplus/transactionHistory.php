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
require 'eppClasses/Student.php';
require 'eppClasses/BadgeTransaction.php';
require 'eppClasses/RewardTransaction.php';
require 'eppClasses/PESTransaction.php';

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
add_to_log($course->id, 'educationplusplus', 'transactionHistory', "transactionHistory.php?id={$cm->id}", $educationplusplus->name, $cm->id);
/// Print the page header
$PAGE->set_url('/mod/educationplusplus/transactionHistory.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

//TRANSACTIONS PAGE CODE
$sid = -1;	//Initialize

// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

//If prof has selected a student to pull....
if (isset($_POST['studentselection']) && $isProfessor){
	$sid = intval($_POST['studentselection']);
}

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

echo $OUTPUT->heading('Education++: Transaction History');

$theStudent = new Student();

global $DB;
// Prof inital landing on the page. Built the select statement, then set the first 
if ($isProfessor && $sid == -1){
	$allStudents = $DB->get_records('epp_student',array('course_id'=>$course->id),'firstname DESC');
	$first = true;
	echo '<div style="width:300px;margin: 0 auto;text-align:center;"><span style="font-weight:bold">Change Student</span><form action="transactionHistory.php?id=' . $cm->id . '" method="post"><select id="studentselection" name="studentselection" style="width:200px;">';
	foreach ($allStudents as $currentStudent){
		if ($first){
			echo '\n<option selected="selected" value="' . $currentStudent->id . '">' . $currentStudent->firstname . ' ' . $currentStudent->lastname . '</option>';
			$sid = $currentStudent->id;
			$theStudent->addData($currentStudent->id, $currentStudent->course_id, $currentStudent->firstname, $currentStudent->lastname, $currentStudent->student_id, $currentStudent->currentpointbalance, $currentStudent->accumulatedpoints, $currentStudent->leaderboardoptstatus);
		}
		else{
			echo '\n<option value="' . $currentStudent->id . '">' . $currentStudent->firstname . ' ' . $currentStudent->lastname . '</option>';
		}
		$first = false;
	}
	echo '</select><input type="submit" name="reload" id="reload" value="Go" /></form></div>';
}
// Prof has selected a student from the select and the page relaoded. Built the select statement, then set that student 
else if ($isProfessor){
	$allStudents = $DB->get_records('epp_student',array('course_id'=>$course->id),'firstname DESC');
	echo '<div style="width:300px;margin:0 auto;text-align:center;"><span style="font-weight:bold">Change Student</span>  <form action="transactionHistory.php?id=' . $cm->id . '" method="post"><select id="studentselection" name="studentselection" style="width:200px;">';
	foreach ($allStudents as $currentStudent){
		if (intval($currentStudent->id) == intval($sid)){
			echo '\n<option selected="selected" value="' . $currentStudent->id . '">' . $currentStudent->firstname . ' ' . $currentStudent->lastname . '</option>';
			$sid = $currentStudent->id;
			$theStudent->addData($currentStudent->id, $currentStudent->course_id, $currentStudent->firstname, $currentStudent->lastname, $currentStudent->student_id, $currentStudent->currentpointbalance, $currentStudent->accumulatedpoints, $currentStudent->leaderboardoptstatus);
		}
		else{
			echo '\n<option value="' . $currentStudent->id . '">' . $currentStudent->firstname . ' ' . $currentStudent->lastname . '</option>';
		}
	}
	echo '</select><input type="submit" name="reload" id="reload" value="Go" /></form></div>';
}
// Student (View their own transactions, no select)
else{
	$sid = intval($USER->id);
	$allStudents = $DB->get_records('epp_student',array('course_id'=>$course->id,'student_id'=>$sid));
	foreach ($allStudents as $currentStudent){
		$theStudent->addData($currentStudent->id, $currentStudent->course_id, $currentStudent->firstname, $currentStudent->lastname, $currentStudent->student_id, $currentStudent->currentpointbalance, $currentStudent->accumulatedpoints, $currentStudent->leaderboardoptstatus);
	}
}

echo '<br/><br/><h2 style="margin:0 auto; text-align:center;">Viewing Transactions for ' . $theStudent->firstName . ' ' . $theStudent->lastName . '</h2><br/>';

// Student Transaction History
	// Part 1a: Badges and Rewards
		$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
		$table = 'epp_student_reward';
		$table2 = 'epp_student_badge';
		$select = "student_id = " . $theStudent->studentId . " AND (";
		foreach($allIncentives as $courseIncentive){
			$select = $select . "incentive_id = " . $courseIncentive->id . " OR ";
		}
		$select = substr($select, 0, -4);
		$select = $select . ")";
		
		if ($allIncentives){
			$epp_student_redeemed_rewards = $DB->get_records_select($table,$select);
			$epp_student_redeemed_badges = $DB->get_records_select($table2,$select);
			//echo var_dump($select);
		}
		else {
			$epp_student_redeemed_rewards = null;
			$epp_student_redeemed_badges = null;
		}

	// Part 1b: Point Earning Scenarios
		$allPES = $DB->get_records('epp_pointearningscenario',array('course'=>$course->id));
		$table3 = 'epp_student_pes';
		$select3 = "student_id = " . $theStudent->studentId . " AND (";
		foreach($allPES as $coursePES){
			$select3 = $select3 . "pes_id = " . $coursePES->id . " OR ";
		}
		$select3 = substr($select3, 0, -4);
		$select3 = $select3 . ")";
		if ($allPES){
			$epp_student_earned_pes= $DB->get_records_select($table3,$select3);
			//echo var_dump($select3);
		}
		else {
			$epp_student_earned_pes= null;
		}

	// Part 2: Make a Transaction object for all reward purchases, badge purchases and PES earnings then throw them in a giant array
		$giantArrayOfTransactions = array();
		//contstuctor( $id, $name, $pointsInvolved, $dateOfTransaction )

		if ($epp_student_redeemed_rewards != null){
			foreach ($epp_student_redeemed_rewards as $rewardTransaction){
				$theName = "";
				foreach($allIncentives as $i){
					if ($i->id == $rewardTransaction->incentive_id){
						$theName = $i->name;
					}
				}
				array_push($giantArrayOfTransactions, new RewardTransaction($rewardTransaction->id, $theName, $rewardTransaction->priceofpurchase, new DateTime($rewardTransaction->datepurchased), null, null));
			}
		}
		if ($epp_student_redeemed_badges != null){
			foreach ($epp_student_redeemed_badges as $badgeTransaction){
				$theName = "";
				foreach($allIncentives as $i){
					if ($i->id == $badgeTransaction->incentive_id){
						$theName = $i->name;
					}
				}
				array_push($giantArrayOfTransactions, new BadgeTransaction($badgeTransaction->id, $theName, $badgeTransaction->priceofpurchase, new DateTime($badgeTransaction->datepurchased)));
			}
		}
		if ($epp_student_earned_pes != null){
			foreach ($epp_student_earned_pes as $pesTransaction){
				$theName = "";
				foreach($allPES as $p){
					if ($p->id == $pesTransaction->pes_id){
						$theName = $p->name;
					}
					//echo var_dump($p);
					//echo var_dump($pesTransaction);
				}
				array_push($giantArrayOfTransactions, new PESTransaction($pesTransaction->id, $theName, $pesTransaction->pointsearned, new DateTime($pesTransaction->dateearned)));
			}
		}
	// Part 3: CSS to Organize Table
	echo   "<style>
				table, tr, td{
					border-bottom:1px solid #000;
				}
				.badgeDate{
					font-style:italic;
					color:black;
				}
				.badgeName{
					color:black;
				}
				.badgeIndent{
					text-indent:50px;
				}
				.badgePoints{
					color:red;
				}
				.rewardDate{
					font-style:italic;
					color:black;
				}
				.rewardName{
					color:black;
				}
				.rewardIndent{
					text-indent:50px;
				}
				.rewardPoints{
					color:red;
				}
				.pesDate{
					font-style:italic;
					color:black;
				}
				.pesName{
					color:black;
				}
				.pesIndent{
				
				}
				.pesPoints{
					color:green;
				}
				table.sortable thead {
					background-color:#eee;
					color:#666666;
					font-weight: bold;
					cursor:default;
				}
			</style>
			<script src='sorttable.js'></script>";
			
	// Part 4: Sort giant array from Part 2, call Transaction's toString
		function cmp($a, $b){
			return strcmp($a->parentGetter("dateOfTransaction")->format('Y-m-d'), $b->parentGetter("dateOfTransaction")->format('Y-m-d'));
		}

		usort($giantArrayOfTransactions, "cmp");
		
		echo '<table border="1" class="sortable" style="width:70%;margin:0 auto;">';
		echo '<thead><tr>	<td style="cursor:pointer;cursor:hand;">Date</td>
							<td style="cursor:pointer;cursor:hand;">Name</td>
							<td style="cursor:pointer;cursor:hand;">Points</td>
					 </tr></thead><tbody>';
		foreach ($giantArrayOfTransactions as $transaction){
			echo $transaction;
		}
		if (count($giantArrayOfTransactions) == 0){
			echo '<tr><td colspan="3" style="text-align:center;">(this student has not had any transactions)</td></tr>';
		}
		
		echo '</tbody></table><br/>';
		
		echo '<table border="1" style="width:70%;margin:0 auto;">';
		echo '<tr><td colspan="3" style="text-align:right">Current Point Balance: ' . $theStudent->currentPointBalance . '</td></tr>';
		echo '<tr><td colspan="3" style="text-align:right">Accumulated Point Total: ' . $theStudent->accumulatedPoints . '</td></tr>';
		echo '</table><br/>';
	
echo '<br/><br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

