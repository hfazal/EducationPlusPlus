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
 * @copyright  2012 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace educationplusplus with the name of your module and remove this line)

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

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all PES
global $DB;

$arrayOfNewNotificationObjects = array();
$arrayOfOldNotificationObjects = array();
$arrayOfIDsForNotificationObjects = array();

// Get id of notification to be dismissed
if ($_GET['notId'])
	$dismissedId = $_GET['notId'];
// Query for notification to be dismissed
$dismissed = $DB->get_record('epp_notification',array('course'=>$course->id, 'student_id'=>$USER->id, 'id'=>$dismissedId));
// Create new record with id of notification to be updated
$record = new stdClass();
$record->id				= $dismissed->id;
$record->student_id  	= intval($dismissed->student_id);
$record->course  		= intval($dismissed->course);
$record->title		 	= $dismissed->title;
$record->content	 	= $dismissed->content;
$record->isread 		= 1;
$datetimeVersionOfExpiryDate = new DateTime ($dismissed->expirydate);
$record->expirydate 	= $datetimeVersionOfExpiryDate->format('Y-m-d H:i:s');
$status = $DB ->update_record('epp_notification', $record, false);
$allNotifications = $DB->get_records('epp_notification',array('course'=>$course->id, 'student_id'=>$USER->id));

// Output starts here
echo $OUTPUT->header();
if ($allNotifications)
{
	foreach ($allNotifications as $notification)
	{
		if ($notification->isread == 0)
		{
			array_push($arrayOfNewNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate)));
			array_push($arrayOfIDsForNotificationObjects, $notification->id);
		}
		else
		array_push($arrayOfOldNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate)));
	}
}

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++: View Your Notifications');

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
		echo '<div style="float:right"><a href="dismissNotification.php?id='. $cm->id .'&notId='. $arrayOfIDsForNotificationObjects[$i] .'" onclick="confirmDelete(' . $i . ')">dismiss</a></div>';
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
	for ($i=count($arrayOfOldNotificationObjects)-1; $i > -1; $i--){
		echo $OUTPUT->box_start();
		echo $arrayOfOldNotificationObjects[$i];
		echo $OUTPUT->box_end();
		echo "<br/>";
	}
}

echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

