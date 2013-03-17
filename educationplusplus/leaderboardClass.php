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

add_to_log($course->id, 'educationplusplus', 'leaderboardClass', "leaderboardClass.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/leaderboardClass.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Class Leaderboard</h1>
		<p>This is the Class Leaderboard where you and your classmates can compare how many points you\'ve accumulated</p>
		<p>If you buy a reward from the store don\'t worry; your accumulated point balance won\'t go down</p>
	  </div>';


global $DB;
$students = $DB->get_records('epp_student',array('leaderboardoptstatus'=>0, 'course_id'=>$course->id),'accumulatedpoints DESC');

echo '	<script src="sorttable.js"></script>
		<style>
			table.sortable thead {
				background-color:#eee;
				color:#666666;
				font-weight: bold;
				cursor:default;
			}
		</style>

		<table class="sortable" style="margin: auto;">
		<thead>
			<tr style="border-bottom:thin solid black;">
				<td style="cursor:pointer;cursor:hand;">Ranking</td>
				<td></td>
				<td style="cursor:pointer;cursor:hand;">Name (Click a Name to View all Badges)</td>
				<td style="cursor:pointer;cursor:hand;">Total Points Accumulated</td>
			</tr>
		</thead>
		<tbody>';

$ranking = 0;
foreach ($students as $studentRecord){
	$stuObj = new stdClass();
	$stuObj->id = $studentRecord->student_id;
	$stuObj->courseid = $course->id;

	$ranking++;
	if ($USER->id == $studentRecord->student_id){
		echo '<tr style="background-color:#BCED91;border-bottom:thin solid black;">';
	}
	else {
		echo '<tr style="border-bottom:thin solid black;">';
	}
	echo '  <td style="font-weight:bold;text-align:center;min-width:200px;">' . $ranking . '</td>
			<td style="min-width:50px">'. $OUTPUT->user_picture($stuObj, array('size'=>50)) . '</td>
			<td style="font-weight:bold;min-width:300px;"><a href="leaderboardStudentProfile.php?id='. $cm->id .'&sid='. $studentRecord->student_id .'">' . $studentRecord->firstname . ' ' . $studentRecord->lastname . '</a></td>
			<td style="text-align:right">' . $studentRecord->accumulatedpoints . '</td>
		  </tr>';
}

echo '</tbody>
	  </table>';

echo '<br/><br/><br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
	  
// Finish the page
echo $OUTPUT->footer();
