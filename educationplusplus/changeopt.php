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
require 'eppClasses/Student.php';
	
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$opt = optional_param('opt', 0, PARAM_RAW);

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
$PAGE->set_url('/mod/educationplusplus/changeopt.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

echo $OUTPUT->header();
echo $OUTPUT->heading('Education++: Opt In or Out of the Leaderboard');

$changeOptTo = 0;
//echo var_dump($opt);

if (strcmp($opt, "in") == 0 || strcmp($opt, "out") == 0){ //opt in or out
	$student = $DB->get_record('epp_student',array('course_id'=>$course->id,'student_id'=>$USER->id));
	if (strcmp($opt, "in") == 0){
		$changeOptTo = 0;
	}
	else {
		$changeOptTo = 1;
	}
	
	$record 						= new stdClass();
	$record->id						= intval($student->id);
	$record->course_id 				= intval($student->course_id);
	$record->firstname	 			= $student->firstname;
	$record->lastname 				= $student->lastname;
	$record->student_id				= intval($student->student_id);
	$record->currentpointbalance 	= intval($student->currentpointbalance);
	$record->accumulatedpoints		= intval($student->accumulatedpoints);
	$record->leaderboardoptstatus 	= $changeOptTo;
	
	// UPDATE PES
	$DB->update_record('epp_student', $record);
	
	if ($changeOptTo == 0){
		echo $OUTPUT->box('You were successfully opted into the Leaderboard System');
	}
	else if ($changeOptTo == 1){
		echo $OUTPUT->box('You were successfully opted out of the Leaderboard System');
	}
}
else {
	echo "This page cannot be accessed directly";
}

echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>