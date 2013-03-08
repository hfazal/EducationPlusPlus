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
require 'eppClasses/Badge.php';
require 'eppClasses/Reward.php';
	
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
$badgeName = $_POST["badgeName"];
$badgePrice = $_POST["badgePrice"];
$storevisTrue = $_POST["storevis"];

if (isset($_FILES["badgeImg"])) {
    $badgeImg = file_get_contents($_FILES["badgeImg"]["tmp_name"]);
    $badgeImg = base64_encode($badgeImg);
}

//echo $badgeImg;
//if ($incentiveType == 'reward')




global $DB;

$allIncentive = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
$arrayOfIncentiveObjects = array();
$arrayOfIDsForIncentiveObjects = array();

if ($allIncentive){
	foreach ($allIncentive as $rowIncentive){
		array_push($arrayOfIncentiveObjects, new Incentive($rowIncentive->name, intval($rowIncentive->qtyperstudent), $rowIncentive->storevisibility, intval($rowIncentive->priceinpoints), $rowIncentive->icon, $rowIncentive->deletebyprof, $rowIncentive->datecreated));
		array_push($arrayOfIDsForIncentiveObjects, $rowIncentive->id);
	}
}


//$newIncentive = new Incentive($badgeName, $incentiveQty, $storevisTrue, $incentiveType, $badgePrice, $badgeImg, false);

//if($incentiveType == "reward"){
        $record                 = new stdClass();
        $record->course_id         = intval($course->id);
        $record->name           = $badgeName;
        $record->priceinpoints     = intval($badgePrice);
        $record->qtyperstudent    = 1;
        $record->storevisibility    = intval($storevisTrue);
        $record->icon = $badgeImg;
        $record->deletebyprof =  0;
        $datetimeVersionOfDateCreated = new DateTime();
        $record->datecreated = $datetimeVersionOfDateCreated->format('Y-m-d H:i:s');
        $idOfIncentive = $DB->insert_record('epp_incentive', $record, true);

        $record_rew = new stdClass(); 
        $record_rew->incentive_id = intval($idOfIncentive);
        $DB->insert_record('epp_badge', $record_rew, false);

//}

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');
    


echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>