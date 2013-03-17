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
// Education++ Classes
require 'eppClasses/Notification.php';

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$deleted = optional_param('delete', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'viewNotifications', "viewNotifications.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/createAPES.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

global $DB;
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

//Professor Check
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

// Display Notifications Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Notifications</h1>
			<p>Below you can view all Notification that you\'ve been sent. Notifications include new Point Earning Scenarios, Point Scenarios you\'ve unlocked, new Rewards in the Store and Notifications the Professor has sent you. Note that Notifications will be deleted 90 days after recieving them.</p>
		  </div>';

if ($isProfessor){
	echo '<div style="text-align:center">As a Professor, you will not recieve any notifications</div><br/>	';
}
else{
	$allNotifications = $DB->get_records('epp_notification',array('course'=>$course->id, 'student_id'=>$USER->id));
	$arrayOfNewNotificationObjects = array();
	$arrayOfOldNotificationObjects = array();
	$arrayOfIDsForNotificationObjects = array();

	// Output starts here
	if ($allNotifications){
		foreach ($allNotifications as $notification){
			$expirydate = new DateTime($notification->expirydate);	//YYYY-MM-DD HH:MM:SS
			$current = new DateTime();
			if ($current > $expirydate){
				$DB->delete_records('epp_notification', array('id'=>$notification->id));
			}
			else{
				if ($notification->isread == 0){
					array_push($arrayOfNewNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate)));
					array_push($arrayOfIDsForNotificationObjects, $notification->id);
				}
				else{
					array_push($arrayOfOldNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate)));
				}
			}
		}
	}



	//Styles for output: notificationTitle, notificationContents, notificationExpiryDate
	echo "	<style>
				.notificationTitle		{ font-weight:bold; font-size:x-large}
				.notificationContents	{ font-style:italic; font-size:x-large}
				.notificationExpiryDate	{ font-size:medium; color: red}
			</style>
		";
		
	echo '	<script>
				function confirmDelete(index){
					var x;
					var r = confirm("Are you sure you want to dismiss this notification?");
					if (r==true){
						var newNotification = document.getElementById("new"+index);
						newNotification.setAttribute("style", "color: black; background-color: white");
					}
					else{}
				}
			</script>';	

	if ($arrayOfNewNotificationObjects){
		for ($i=0; $i < count($arrayOfNewNotificationObjects); $i++){
			echo $OUTPUT->box_start();
			echo '<div id = "new'.$i .'" style="color: white; background-color: black">';
			echo '<div style="float:right"><a style="color:red" href="dismissNotification.php?id='. $cm->id .'&notId='. $arrayOfIDsForNotificationObjects[$i] .'" onclick="confirmDelete(' . $i . ')">dismiss</a></div>';
			echo $arrayOfNewNotificationObjects[$i];
			echo $OUTPUT->box_end();
			echo "</div>";
			echo "<br/>";
		}
	}
	else{
		echo $OUTPUT->box('<div style="width:100%;text-align:center;">No new notifications were found.</div>');
		echo "<br/>";
	}

	if ($arrayOfOldNotificationObjects)
	{
		for ($i=0; $i < count($arrayOfOldNotificationObjects); $i++){
			echo $OUTPUT->box_start();
			echo $arrayOfOldNotificationObjects[$i];
			echo $OUTPUT->box_end();
			echo "<br/>";
		}
	}
}

echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

