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

add_to_log($course->id, 'educationplusplus', 'createANotification', "createANotification.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/createANotification.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Retrieve from DB all Notifications
global $DB;

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Display Notifications Intro
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Notifications</h1>
		<p>Below you can send a Notification to all students in your class, be it about upcoming rewards, badges or anything!</p>
		<p>Please note that the students will see it the next time they log into Education++, and that Notifications expire and delete 90 days after they\'re created</p>
	  </div>';


//Professor Check
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

if ($isProfessor){
	echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script>

	function validate(){
		var title       = document.forms["notificationForm"]["notificationTitle"].value;
		var description = document.forms["notificationForm"]["notificationContent"].value;
		var pass 		= true;
		
		$("#titleReq").css("display", "none");
		$("#contentReq").css("display", "none");
		
		if (title==null || title==""){
			$("#titleReq").css("display", "inline");
			pass = false;
		}
		if (description==null || description==""){
			$("#contentReq").css("display", "inline");
			pass = false;
		}

		return pass;
	}
	</script>';
	
	echo $OUTPUT->box('<div id="form" style="width:400px;overflow:auto;margin-bottom:30px;">
						<form id="notificationForm" name="notificationForm" method="post" onsubmit="return validate()" action="persistNotification.php?id='. $cm->id .'" style="padding-left:10px;padding-right:10px;">
							<h3>Notification</h3>
							<table>
								<tr>
									<td style="width:100px">Title</td>
									<td><input type="text" class="required" style="margin-right:10px;width:200px;" id="notificationTitle" name="notificationTitle"><br/><span id="titleReq" style="color:red;display:none;">You Must Specify the Title of the Notification</span></td>
								</tr>
								<!--<tr>
									<td>Expiry Date</td>
									<td><input type="date" class="required" style="margin-right:10px;width:200px;" id="notificationExpiryDate" name="notificationExpiryDate"><br/><span id="expReq" style="color:red;display:none;">You Must Specify an Expiry Date</span></td>
								</tr>-->
								<tr>
									<td style="vertical-align:top;">Content</td>
									<td><textarea class="required" name="notificationContent" id="notificationContent" style="margin-right:10px;width:200px;" style></textarea><br/><span id="contentReq" style="color:red;display:none;">You Must Specify a Description</span></td>
								</tr>
							</table>
							<hr/><input name="Submit" type="submit" style="float:right; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" value="Create a new Notification"/>
						</form>
					</div>');
	echo '<br/>';
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++: Main Page (Cancel Creation of this Notification)</a></div>');
}
else{
	echo '<div style="text-align:center">As a Student, you cannot send any notifications</div><br/>';
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
}

// Finish the page
echo $OUTPUT->footer();