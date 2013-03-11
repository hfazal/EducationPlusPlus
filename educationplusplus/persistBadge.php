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

$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
$arrayOfBadge = array();
$arrayOfIDsForBadgeObjects = array();

if($allIncentives){
    foreach ($allIncentives as $rowIncentive){
        $allBadges = $DB->get_record('epp_badge',array('incentive_id'=>$rowIncentive->id));
        if($allBadges){
            foreach($allBadges as $rowBadge){
                array_push($arrayOfBadge, new Badge($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated)));  
                array_push($arrayOfIDsForBadgeObjects, $rowIncentive->id);
                break;
            }
        }
    }
    
}

$newBadge = new Badge($badgeName, 1, $storevisTrue, $badgePrice, $badgeImg, 0, new DateTime());
//DUPLICATE CHECK
$duplicateFound = false;
for($i=0; $i < count($arrayOfBadge); $i++){
    if (strcmp($newBadge->parentGetter("name"), $arrayOfBadge[$i]->parentGetter("name")) == 0){
        echo $i;
        $duplicateFound = true;
    }
}

if ($duplicateFound == false){
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
}


// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');
    
if ($duplicateFound == false){
    echo $OUTPUT->box('The following Badge was successfully saved:<br/><br/>' . $newBadge);
    
    $enrolment = $DB->get_record('enrol',array('courseid'=>$course->id, 'status'=>0));
    $userIds = $DB->get_records('user_enrolments',array('enrolid'=>$enrolment->id));
    
    foreach ($userIds as $user)
    {
            //PERSIST TO epp_notification
            $record                 = new stdClass();
            $record->student_id     = intval($user->userid);
            $record->course         = intval($course->id);
            $record->title          = "New Badge";
            $record->content        = 'A new badge was created: '.$newBadge->parentGetter("name") . 'Price: ' .$newBadge->parentGetter("priceInPoints"). ' ';
            $record->isread         = 0;
            $datetimeVersionOfExpiryDate = new DateTime();
            $datetimeVersionOfExpiryDate->add(new DateInterval('P90D'));
            $record->expirydate     = $datetimeVersionOfExpiryDate->format('Y-m-d H:i:s');
            $id = $DB->insert_record('epp_notification', $record, true);
        
    }
}
else { // ($duplicateFound == true)
    echo $OUTPUT->box('The following Badge was <strong>NOT</strong> saved as a badge with the same name already exists:<br/><br/>' . $newBadge);
}

echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>