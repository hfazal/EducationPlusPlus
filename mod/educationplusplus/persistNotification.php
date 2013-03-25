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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require 'eppClasses/Notification.php';
	
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

add_to_log($course->id, 'educationplusplus', 'persistNotification', "persistNotification.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/persistNotification.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo "<link rel='stylesheet' type='text/css' href='./css/usecaseboxes.css'>
	<div class='floatingdiv'>Use Case Scenario(s): 5.6.2</div>";
// Display Notifications Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Notifications</h1>
			<p>Sending the Notification to all Students of the Class</p>
		  </div>';

//Professor Check
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

if ($isProfessor){
	//Process Notification
	$notificationTitle = $_POST["notificationTitle"];
	$notificationContent = $_POST["notificationContent"];
	$notificationExpiryDate = new DateTime();
	$notificationExpiryDate->add(new DateInterval('P90D'));

	global $DB;

	$enrolment = $DB->get_record('enrol',array('courseid'=>$course->id, 'status'=>0));
	$userIds = $DB->get_records('user_enrolments',array('enrolid'=>$enrolment->id));

	/* CREATE NOTIFICATION OBJECT */
	$newNotification = new Notification(0, $notificationTitle, $notificationContent, 1, $notificationExpiryDate);

	foreach ($userIds as $user)
	{
		if ($user)
		{
			//PERSIST TO epp_notification
			$record 				= new stdClass();
			$record->student_id  	= intval($user->userid);
			$record->course  		= intval($course->id);
			$record->title		 	= $notificationTitle;
			$record->content	 	= $notificationContent;
			$record->isread 			= 0;
			$record->expirydate 	= $notificationExpiryDate->format('Y-m-d H:i:s');
			$id = $DB->insert_record('epp_notification', $record, true);
		}
	}

	//Styles for output: notificationTitle, notificationContents, notificationExpiryDate
	echo "	<style>
				.notificationTitle		{ font-weight:bold; font-size:x-large; }
				.notificationContents	{ font-style:italic; font-size:x-large; }
				.notificationExpiryDate	{ color:red; font-size:medium; }
			</style>
		";

	if ($id > 0){
		echo $OUTPUT->box('The following Notification was successfully sent to all students:<br/><br/>' . $newNotification);
	}
}
else{
	echo '<div style="text-align:center">As a Student, you cannot send any notifications</div>';
}

echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>