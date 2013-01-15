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

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

echo '<div id="eppContainer" style="width:900px;margin:0 auto;">';
	echo '<img style="float:left; margin-left:30px;margin-right:30px;" src="pix/logo.png" />';
	if ($isProfessor){
		echo '<div style="float:left;margin:30px;">
			<h2 style="font-size:large">Administrator</h2>
			<h3>Point Earning Scenerio Tools</h3>
			<a href="viewAllPES.php?id='. $cm->id .'">Manage Scenarios in which Students can Earn Points</a><br/>
			<s><a href="gradebookparser.php?id='. $cm->id .'">Scan Gradebook to detect Met Scenarios (Will auto trigger)</a></s><br/>
			<br/>
			<h3>Reward Tools</h3>
			<a href="viewAllRewards.php?id='. $cm->id .'">Manage Rewards in which Students can Spend Points on</a><br/>
			<br/>
			<h3>Student Points List</h3>
			<a href="studentpoints.php?id='. $cm->id .'">View list of Students and their Points Balance</a><br/>
			<br/>
			<h3>Notifications</h3>
			<a href="createANotification.php?id='. $cm->id .'">Create a New Notification for all Users in this Class</a><br/>
			<a href="viewNotifications.php?id='. $cm->id .'">View all Notifications Sent Out</a><br/>
			<br/>
			<h3>View the Leaderboard</h3>
			<a href="leaderboardClass.php?id='. $cm->id .'">View the Class Leaderboard</a><br/>
			<a href="leaderboardSchool.php?id='. $cm->id .'">View the Schoolwide Leaderboard</a><br/>
		</div>';
	}
	else{
		//Since Student, Obtain Point Balance
		global $DB;
		$eppStudentRecord = $DB->get_record('epp_student',array('course_id'=>$course->id, 'student_id'=>$USER->id));
		//echo var_dump($eppStudentRecord);
		echo '<span style="color:red;font-weight:bold;">';
		if (!$eppStudentRecord){
			echo '0 Points';
		}
		else{
			echo $eppStudentRecord->currentpointbalance . ' Points';
		}
		echo '</span>';
	
		//Options
		echo '<div style="float:left;margin:30px;">
			
			<h2 style="font-size:large">Student</h2>
			<h3>Point Earning Scenerios</h3>
			<a href="viewAllPES.php?id='. $cm->id .'">View All the Ways you Can Earn Points</a><br/>
			<a href="earnedPES.php?id='. $cm->id .'">View All the Ways you Have Already Earned Points</a><br/>
			<br/>
			<h3>Rewards</h3>
			<a href="storeFront.php?id='. $cm->id .'">View the Store to Purchase Rewards</a><br/>
			<a href="storePurchases.php?id='. $cm->id .'">View Your Purchases</a><br/>
			<br/>
			<h3>View the Leaderboard</h3>
			<a href="leaderboardClass.php?id='. $cm->id .'">View the Class Leaderboard</a><br/>
			<a href="leaderboardSchool.php?id='. $cm->id .'">View the Schoolwide Leaderboard</a><br/>
			<a href="leaderboardOpt.php?id='. $cm->id .'">Opt in or out of the Leaderboard</a><br/>
		</div>';
	}
echo '</div>';
echo 'Things to add to this (view.php):<br/>1. If student, check if they have an entry in epp_student, if not make them one.<br/>2. If gradebook has been updated since last check, rescan for achieved PES<br/>3. New Notifications should show here somehow';
// Finish the page
echo $OUTPUT->footer();
