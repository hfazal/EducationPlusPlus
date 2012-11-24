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
 * @copyright  2011 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace educationplusplus with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require 'eppClasses/PointEarningScenario.php';
require 'eppClasses/Requirement.php';
require 'eppClasses/Activity.php';
	
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

//Process PES
$pesName = $_POST["pesName"];
$pesPointValue = intval($_POST["pesPointValue"]);
$pesDescription = $_POST["pesDescription"];
$pesExpiryDate = $_POST["pesExpiryDate"];
$requirementsActivity = array();
$requirementsCondition = array();
$requirementsPercentToAchieve = array();
$requirementsCount;
$formedRequirements = array();

$reqAct = $_POST["reqAct"];
$reqCond = $_POST["reqCond"];
$reqGradeToAchieve = $_POST["reqGradeToAchieve"];

foreach ($reqAct as $eachInput) {
	array_push($requirementsActivity, $eachInput);
}

foreach ($reqCond as $eachInput) {
	array_push($requirementsCondition, intval($eachInput));
}

foreach ($reqGradeToAchieve as $eachInput) {
	array_push($requirementsPercentToAchieve, intval($eachInput));
}

$requirementsCount = count($requirementsActivity);

for ($i = 0; $i < $requirementsCount; $i++){
	$act = new Activity($requirementsActivity[$i], null);	//Fix this to draw info from DB
	$req = new Requirement($act, $requirementsCondition[$i], $requirementsPercentToAchieve[$i]);
	array_push($formedRequirements, $req);
}

$newPES = new PointEarningScenario($pesName, $pesPointValue, $pesDescription, $formedRequirements, new DateTime($pesExpiryDate), false);


// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');

echo $OUTPUT->box('A new Point Earning Scenario was successfully made [into a class, not saved YET]<br/>' . $newPES);
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'&newpes=1">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>