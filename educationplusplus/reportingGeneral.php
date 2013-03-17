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
require 'eppClasses/RewardTransaction.php';
// HTML2PDF
require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');

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
add_to_log($course->id, 'educationplusplus', 'reportingGeneral', "reportingGeneral.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/reportingGeneral.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
	echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Reward Tracking</h1>';
if ($isProfessor){
	echo '	<p>Below you can keep track of who you\'ve awarded rewards to and generate a PDF for your own records.</p>
	  </div>';
}
else{
	echo '	<p>Only Professors can access this page</p>
	  </div>';
}

if ($isProfessor){
	// Check for save process
	$saved = false;
	if (isset($_POST["completion"])){
		$savedContent = $_POST["completion"];
		$saved = true;
		
		$saveAllIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
		$table1 = 'epp_student_reward';
		$select1 = "";
		foreach($saveAllIncentives as $ci){
			$select1 = $select1 . "incentive_id = " . $ci->id . " OR ";
		}
		$select1 = substr($select1, 0, -4);
		$allRewardTransactions = $DB->get_records_select($table1,$select1);
		
		foreach ($allRewardTransactions as $r){
			$record 				= new stdClass();
			$record->id				= intval($r->id);
			$record->incentive_id	= intval($r->incentive_id);
			$record->student_id	 	= intval($r->student_id);
			$datetimeversion = new DateTime ($r->datepurchased);
			$record->datepurchased 	= $datetimeversion->format('Y-m-d H:i:s');
			$record->priceofpurchase= intval($r->priceofpurchase);
			$dismiss = false;
			foreach ($savedContent as $s){
				if (intval($s) == intval($r->id)){
					$dismiss = true;
				}
			}
			$record->reportdismissed= ($dismiss == true ? 1 : 0);
			$record->reportnew		= intval($r->reportnew);
			
			// UPDATE PES
			$DB->update_record($table1, $record);
		}
	}

	// Build Report
	$allStudents = $DB->get_records('epp_student',array('course_id'=>$course->id));
	$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));

	$table = 'epp_student_reward';
	$select = "";
	foreach($allIncentives as $courseIncentive){
		$select = $select . "incentive_id = " . $courseIncentive->id . " OR ";
	}
	$select = substr($select, 0, -4);
	$allRewardTransactions = $DB->get_records_select($table,$select);

	$giantArrayOfRewardTransactions = array();
	$student = null;

	foreach($allRewardTransactions as $rewardTransaction){
		$name = "";
		foreach($allIncentives as $courseIncentive){
			if ($rewardTransaction->incentive_id == $courseIncentive->id){
				$name = $courseIncentive->name;
			}
		}		
		foreach($allStudents as $studentRecord){
			if ($studentRecord->student_id == $rewardTransaction->student_id){
				$student = new Student();
				$student->addData($studentRecord->id, $studentRecord->course_id, $studentRecord->firstname, $studentRecord->lastname, $studentRecord->student_id, $studentRecord->currentpointbalance, $studentRecord->accumulatedpoints, $studentRecord->leaderboardoptstatus);
				foreach($allIncentives as $courseIncentive){
					$select = $select . "incentive_id = " . $courseIncentive->id . " OR ";
				}
				array_push($giantArrayOfRewardTransactions, new RewardTransaction($rewardTransaction->id, $name, $rewardTransaction->priceofpurchase, new DateTime($rewardTransaction->datepurchased), $student, $rewardTransaction->reportdismissed));
			}
		}
	}
	
	$maintable = '<div id="maintable"><table border="1" class="sortable" style="width:' . (isset($_POST["viewPDF"]) ? "100" : "70") . '%;margin:0 auto;">
					<thead><tr>
						<td style="cursor:pointer;cursor:hand;">Date</td>
						<td style="cursor:pointer;cursor:hand;">Student Name</td>
						<td style="cursor:pointer;cursor:hand;">Reward Purchased</td>
						<td style="cursor:pointer;cursor:hand;">Awarded Status</td>
				 </tr></thead><tbody>';
	foreach ($giantArrayOfRewardTransactions as $transaction){
		$maintable = $maintable . $transaction; 
	}
	$maintable = $maintable . "</tbody></table></div><!--maintable--><br/>";

	//echo var_dump($course);

	if (isset($_POST["viewPDF"])){	//Generate PDF
		$maintable = preg_replace('#<input(.*?)>#is', '', $maintable);	//Gets rid of checkboxes
		$maintable = "<div style='margin:50px;'><table border='0'><tr>
						<td align='top'>
							<img src='./pix/logo.png' style='width:120px;margin-right:50px;' />
						</td>
						<td align='top'>
							<h1 style='margin-bottom: 0;'>Education++ Rewards Report</h1>
							<h4 style='margin-bottom: 0;'>" . $course->fullname . " (" . $course->shortname . ")</h4>
							<h4 style='margin-bottom: 0;'>Generated on " . date('l F dS Y \a\t h:i A') . "</h4>
						</td>
					 </tr></table><br/><br/>" . $maintable . "</div>";
		$reportGenerator = new HTML2PDF('P','A4','en');
		$reportGenerator->WriteHTML($maintable);
		$reportGenerator->Output('report.pdf');
	}
	else{
		// Output starts here
		if ($isProfessor){
			global $DB;
			
			echo "<style>
						table, tr, td{
							border-bottom:1px solid #000;
						}
						.rewardDate{
							font-style:italic;
							color:black;
						}
						.rewardName{
							color:black;
							font-weight:bold;
						}
						.rewardStudent{
							color:black;
							font-weight:bold;
						}
						table.sortable thead {
							background-color:#eee;
							color:#666666;
							font-weight: bold;
							cursor:default;
						}
					</style><script src='sorttable.js'></script>";
			if ($saved == true){
				echo "<div style='width:100%;text-align:center;color:gray;'><h2 style='margin:0 auto'>Awarded Statuses Successfully Saved</h2></div><br/>";
			}
			echo '<form action="reportingGeneral.php?id=' . $cm->id . '" method="post">';
			echo $maintable;
			echo '<table style="width:70%;margin:0 auto;border:0;">';
			echo '	<tr style="border:0">
						<td style="border:0;width:100%;text-align:right;"><input type="submit" value="Save Update Awarded Statuses"></td>
					</tr></table></form><br/>';
			echo '<form action="reportingGeneral.php?id=' . $cm->id . '" method="post"><table style="width:70%;margin:0 auto;border:0;">';
			echo '	<tr style="border:0">
						<td style="border:0;width:100%;text-align:right;">
							<input type="text" name="viewPDF" style="display:none;"><input type="submit" value="View this Report as PDF (Save your changes first)">
						</td>
					</tr></table></form>';	
			echo '<br/><br/>';
			echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

			// Finish the page
			echo $OUTPUT->footer();
		}
	}
}
else{
	echo '<br/><br/>';
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

	// Finish the page
	echo $OUTPUT->footer();
}	
