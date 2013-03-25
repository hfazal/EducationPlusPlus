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
require 'eppClasses/Badge.php';
	
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$IncentiveID = optional_param('badge', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'deleteBadge', "deleteBadge.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/educationplusplus/deleteBadge.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

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

if($isProfessor){
	global $DB;
	//$newIncentive = new Incentive($incentiveName, $incentiveQty, $storevisTrue, $incentiveType, $incentivePrice, $incentiveImg, false);

	//if($incentiveType == "reward"){
			$record                       = new stdClass();
			$record->id                   = intval($IncentiveID);
			$record->deletebyprof         = 1;
			$record->storevisibility      = 0;  

			$DB->update_record('epp_incentive', $record);

			redirect("viewAllIncentives.php?id=" . $cm->id);



	//}
}
else{
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Rewards</h1>
			<p><a href="storefront.php?id='. $cm->id .'">Visit the Store here</a></p>
		  </div><br/>';
}
echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>