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

add_to_log($course->id, 'educationplusplus', 'leaderboardOpt', "leaderboardOpt.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/leaderboardOpt.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

//Professor Check
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo "<link rel='stylesheet' type='text/css' href='./css/usecaseboxes.css'>
	<div class='floatingdiv'>Use Case Scenario(s): 5.5.4, 5.5.5</div>";
// Display Notifications Intro
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Leaderboard</h1>
		<p>Below you can opt in or out of the leaderboard system. What does this mean?</p>
		<p>If you opt into the leaderboard you can: take part in the <a href="leaderboardClass.php?id=' . $cm->id . '">class leaderboard</a>, take part in the <a href="leaderboardSchool.php?id=' . $cm->id . '">school leaderboard</a> and have a <a href="leaderboardStudentProfile.php?id=' . $cm->id . '&sid=' . $USER->id .'">student profile</a></p>
		<p>If you do choose to opt out, you can still earn points and purchase rewards</p>
	  </div>';


global $DB;
$studentRecord = $DB->get_record('epp_student',array('student_id'=>$USER->id, 'course_id'=>$course->id));
$studentObject = new Student();
$studentObject->addData( $studentRecord->id, $studentRecord->course_id, $studentRecord->firstname, $studentRecord->lastname, $studentRecord->student_id, $studentRecord->currentpointbalance, $studentRecord->accumulatedpoints, $studentRecord->leaderboardoptstatus );


if ($isProfessor){
	echo $OUTPUT->box('<div style="text-align:center">As a Professor, you are not part of the leaderboard system</div>');
	echo '<br/>';
}
else{
	echo '	<script>
				function optOut(){
					var x;
					var r = confirm("Are you sure you want to Opt Out of the leaderboard system? You will no longer be on the School or Class leaderboard, and your Education++ Profile will not be visible to other students. (You can go back later)");
					if (r==true){
						url = "changeopt.php?id=' . $cm->id . '&opt=out";
						window.location = url;
					}
					else{}
				}
				function optIn(){
					var x;
					var r = confirm("Are you sure you want to Opt In to the leaderboard system? You will be visible on the School and Class leaderboards, and your Education++ Profile will be visible to other students.  (You can go back later)");
					if (r==true){
						url = "changeopt.php?id=' . $cm->id . '&opt=in";
						window.location = url;
					}
					else{}
				}
			</script>';

	echo $OUTPUT->box_start();
	echo '<div style="text-align:center">';
	if ($studentObject->leaderboardOptStatus == 0) {//in
		echo 'Currently, you are <b>opted in</b> to the leaderboard system.<br/><br/>This means your classmates can see how many points you\'ve earned in each class Education++ is available in, and you can compete with them to see who can accumulate the most.<br/><br/>It also means you can see where you rank in the school for number of badges accumulated.<br/><br/>Lastly, students in your school will be able to view your profile to see all badges you\'ve collected.';
		echo '<br/><br/><button type="button" onclick="optOut()">Opt Out</button>';
	}
	else {	// out
		echo 'Currently, you are <b>opted out</b> of the leaderboard system.<br/><br/> This means your classmates <i>cannot</i> see how many points you\'ve earned in any class Education++ is available in, and you <i>cannot</i> compete with them on the leaderboard system.<br/><br/>It also means <i>you are not</i> on the school leaderboard where can see how you rank in the school for number of badges accumulated.<br/><br/>Lastly, students in your school will not be able to view your profile to see all badges you\'ve collected';
		echo '<br/><br/><button type="button" onclick="optIn()">Opt In</button>';
	}
	echo '</div>';
	echo $OUTPUT->box_end();
}

	

echo '<br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
	  
// Finish the page
echo $OUTPUT->footer();
