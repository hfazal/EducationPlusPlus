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

// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading('Education++: Opt In or Out of the Leaderboard');

global $DB;
$studentRecord = $DB->get_record('epp_student',array('student_id'=>$USER->id, 'course_id'=>$course->id));
$studentObject = new Student();
$studentObject->addData( $studentRecord->id, $studentRecord->course_id, $studentRecord->firstname, $studentRecord->lastname, $studentRecord->student_id, $studentRecord->currentpointbalance, $studentRecord->accumulatedpoints, $studentRecord->leaderboardoptstatus );

echo '	<script>
			function optOut(){
				var x;
				var r = confirm("Are you sure you want to Opt Out of the leaderboard system? You will no longer be on the School or Class leaderboard (You can go back later)");
				if (r==true){
					url = "changeopt.php?id=' . $cm->id . '&opt=out";
					window.location = url;
				}
				else{}
			}
			function optIn(){
				var x;
				var r = confirm("Are you sure you want to Opt In to the leaderboard system? You will be visible on the School and Class leaderboards (You can go back later)");
				if (r==true){
					url = "changeopt.php?id=' . $cm->id . '&opt=in";
					window.location = url;
				}
				else{}
			}
		</script>';


echo '<div style="text-align:center">';
if ($studentObject->leaderboardOptStatus == 0) {//in
	echo 'Currently, you are <b>opted in</b> to the leaderboard system.<br/><br/>This means your classmates can see how many points you\'ve earned in each class Education++ is available in, and you can compete with them to see who can accumulate the most.<br/><br/>It also means you can see where you rank in the school for number of badges accumulated.';
	echo '<br/><br/><button type="button" onclick="optOut()">Opt Out</button>';
}
else {	// out
	echo 'Currently, you are <b>opted out</b> of the leaderboard system.<br/><br/> This means your classmates <i>cannot</i> see how many points you\'ve earned in any class Education++ is available in, and you <i>cannot</i> compete with them on the leaderboard system.<br/><br/>It also means <i>you are not</i> on the school leaderboard where can see how you rank in the school for number of badges accumulated.';
	echo '<br/><br/><button type="button" onclick="optIn()">Opt In</button>';
}
echo '</div>';

echo '<br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
	  
// Finish the page
echo $OUTPUT->footer();
