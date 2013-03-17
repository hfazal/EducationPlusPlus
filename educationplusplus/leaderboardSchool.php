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

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$added = optional_param('newpes', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'leaderboardSchool', "leaderboardSchool.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/leaderboardSchool.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> School Leaderboard</h1>
		<p>This is the School Leaderboard where you and everyone at your school can compare how many badges you\'ve accumulated!</p>
		<p>Start accumulating your badges and cement your place in your school\'s history!</p>
	  </div>';

global $DB;
$studentIds = array();
$studentIdsUnique = array();

$students = $DB->get_records('epp_student',array('leaderboardoptstatus'=>0));

foreach ($students as $entry){
	array_push($studentIds, $entry->student_id);
}

$studentIdsUnique = array_unique($studentIds);

echo '	<script src="sorttable.js"></script>
		<style>
			table.sortable thead {
				background-color:#eee;
				color:#666666;
				font-weight: bold;
				cursor:default;
			}
			.badge{
				-webkit-border-radius: 10px;
				-khtml-border-radius: 10px;	
				-moz-border-radius: 10px;
				border-radius: 10px;
				width:50px;
				height:50px;
				margin-left:10px;
				vertical-align:middle;
			}
		</style>

		<table class="sortable" style="margin: auto;">
		<thead>
			<tr style="border-bottom:thin solid black;">
				<td style="cursor:pointer;cursor:hand;">Ranking</td>
				<td style="cursor:pointer;cursor:hand;">Name (Click a Name to View all Badges)</td>
				<td style="cursor:pointer;cursor:hand;">Badges Accumulated (Most Recent Shown) </td>
			</tr>
		</thead>
		<tbody>';

$allIncentives = $DB->get_records('epp_incentive');

$ranking = 0;
foreach ($studentIdsUnique as $epp_student){
	$allRecords = $DB->get_records('epp_student',array('student_id'=>$epp_student));
	
	foreach ($allRecords as $firstrecord){	
		$ranking++;
		
		if ($USER->id == $firstrecord->student_id){
			echo '<tr style="background-color:#BCED91;border-bottom:thin solid black;">';
		}
		else {
			echo '<tr style="border-bottom:thin solid black;">';
		}
		
		echo '  <td style="font-weight:bold;text-align:center;min-width:200px;">' . $ranking . '</td>
				<td style="font-weight:bold;min-width:300px;"><a href="leaderboardStudentProfile.php?id='. $cm->id .'&sid='. $firstrecord->student_id .'">' . $firstrecord->firstname . ' ' . $firstrecord->lastname . '</a></td>
				<td style="text-align:left;min-width:250px;height:70px;">';

		$thisStudentsBadges = $DB->get_records('epp_student_badge',array('student_id'=>$firstrecord->student_id), "datePurchased DESC");
		echo count($thisStudentsBadges) . " "; // displays number of badges earned
		
		$badgeCount = 0;
		foreach ($thisStudentsBadges as $earnedBadge){
			if ($badgeCount >= 3){ break;}
			foreach ($allIncentives as $incentive){
				if ($incentive->id == $earnedBadge->incentive_id && $badgeCount < 3){
					echo '<img class="badge" src="data:image/jpg;base64,' . $incentive->icon . '" alt="' . $incentive->name . '" />';
					$badgeCount++;
				}
			}
		}
		
				
		echo '</td></tr>';
		break;
	}
}

echo '</tbody>
	  </table>';

echo '<br/><br/><br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
	  
// Finish the page
echo $OUTPUT->footer();
