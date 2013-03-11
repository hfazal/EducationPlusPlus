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
$incentiveName = $_POST["incentiveName"];
$incentiveQty = intval($_POST["incentiveQty"]);
$incentivePrice = $_POST["incentivePrice"];
$storevisTrue = $_POST["storevis"];
$rewardExpiryDate = $_POST["rewardExpiryDate"];
$rewardDescription = $_POST["rewardDescription"];

if (isset($_FILES["incentiveImg"])) {
    $incentiveImg = file_get_contents($_FILES["incentiveImg"]["tmp_name"]);
    $incentiveImg = base64_encode($incentiveImg);
}

//echo $incentiveImg;
//if ($incentiveType == 'reward')




global $DB;
$allIncentives = $DB->get_records('epp_incentive',array('course_id'=>$course->id));
$arrayOfIDsForRewardObjects = array();
$arrayOfReward = array();

if($allIncentives){
    foreach ($allIncentives as $rowIncentive){
        $allRewards = $DB->get_record('epp_reward',array('incentive_id'=>$rowIncentive->id));
        if ($allRewards){
            foreach($allRewards as $rowReward){
                array_push($arrayOfReward, new Reward($rowIncentive->name, intval($rowIncentive->qtyperstudent), intval($rowIncentive->storevisibility), intval($rowIncentive->priceinpoints), $rowIncentive->icon, intval($rowIncentive->deletebyprof), new DateTime($rowIncentive->datecreated), $allRewards->prize, new DateTime($allRewards->expirydate)  ));
                array_push($arrayOfIDsForRewardObjects, $rowIncentive->id);
                break;
            }
        }
    } 
}

$newReward = new Reward($incentiveName, $incentiveQty, $storevisTrue, $incentivePrice, $incentiveImg, 0, new DateTime(), $rewardDescription, new DateTime($rewardExpiryDate));
//DUPLICATE CHECK
$duplicateFound = false;
for($i=0; $i < count($arrayOfReward); $i++){
    if (strcmp($newReward->parentGetter("name"), $arrayOfReward[$i]->parentGetter("name")) == 0){
        echo $i;
        $duplicateFound = true;
    }
}
if ($duplicateFound == false){
        $record                 = new stdClass();
        $record->course_id         = intval($course->id);
        $record->name           = $incentiveName;
        $record->priceinpoints     = intval($incentivePrice);
        $record->qtyperstudent    = intval($incentiveQty);
        $record->storevisibility    = intval($storevisTrue);
        $record->icon = $incentiveImg;
        $record->deletebyprof =  0;
        $datetimeVersionOfDateCreated = new DateTime();
        $record->datecreated = $datetimeVersionOfDateCreated->format('Y-m-d H:i:s');
        $idOfIncentive = $DB->insert_record('epp_incentive', $record, true);

        $record_rew = new stdClass(); 
        $record_rew->incentive_id = intval($idOfIncentive);
        $record_rew->prize = $rewardDescription;
        $datetimeVersionOfExpiryDate = new DateTime ($rewardExpiryDate);
        $record_rew->expirydate     = $datetimeVersionOfExpiryDate->format('Y-m-d H:i:s');
        $DB->insert_record('epp_reward', $record_rew, false);

}
else{
    
}
// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');
    
if ($duplicateFound == false){
    echo $OUTPUT->box('The following Reward was successfully saved:<br/><br/>' . $newReward);
    
    $enrolment = $DB->get_record('enrol',array('courseid'=>$course->id, 'status'=>0));
    $userIds = $DB->get_records('user_enrolments',array('enrolid'=>$enrolment->id));
    
    foreach ($userIds as $user)
    {
            //PERSIST TO epp_notification
            $record                 = new stdClass();
            $record->student_id     = intval($user->userid);
            $record->course         = intval($course->id);
            $record->title          = "New Reward";
            $record->content        = 'A new reward was created: '.$newReward->parentGetter("name") . 'Price ' .$newReward->parentGetter("priceInPoints"). 'Description: '.$newReward->prize;
            $record->isread         = 0;
            $datetimeVersionOfExpiryDate = new DateTime();
            $datetimeVersionOfExpiryDate->add(new DateInterval('P90D'));
            $record->expirydate     = $datetimeVersionOfExpiryDate->format('Y-m-d H:i:s');
            $id = $DB->insert_record('epp_notification', $record, true);
        
    }
}
else { // ($duplicateFound == true)
    echo $OUTPUT->box('The followingReward was <strong>NOT</strong> saved as a reward with the same name already exists:<br/><br/>' . $newReward);
}

echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

?>