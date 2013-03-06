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
// Education++ Classes


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

add_to_log($course->id, 'educationplusplus', 'gradebookparser', "gradebookparser.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/gradebookparser.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all Activities
global $DB;
$table_assign = 'assign';
$table_assign_grade = 'assign_grades';
$table_pes = 'epp_pointearningscenario';
$table_req = 'epp_requirement';
$table_student = 'epp_student';
$table_assign_submission = 'assign_submission';
$now = new DateTime();
$assign = $DB->get_records($table_assign,array('course'=>$course->id));
$students = $DB->get_records($table_student,array('course_id'=>$course->id));
$pes = $DB->get_records($table_pes,array('course'=>$course->id ));


    
//$submission =  $DB->get_records($table_assign_submission,array('assignment'=>$assign->id)); TBD

$constructedSelectOptions = "";
// Output starts here
//echo var_dump($result);
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');

if ($assign) {
    foreach ($pes as $pointearningscenaio){
        $req = $DB->get_records($table_req,array('pointearningscenario'=>$pointearningscenaio->id));
        $numberOfReq = count($req);
        foreach ($students as $student){
			$awardStatus = 0;
            $result = $DB->get_records($table_assign_grade,array('userid'=>$student->student_id));
            if($DB->count_records('epp_student_pes',array('student_id'=>$student->student_id , 'pes_id'=>$pointearningscenaio->id)) == 0){
                foreach ($result as $row){
                    //$student_es = $DB->get_record('epp_student_pes',array('student_id'=>$row->userid));
                        foreach ($req as $requirement){
                            if (($row->assignment == $requirement->activity) ){
                                switch($requirement->cond){
                                case 0: // Complete
                                    $awardStatus = $awardStatus + 1;
                                    break;
                                case 1: // >
                                    if ($row->grade > $requirement->percenttoachieve){
                                    $awardStatus = $awardStatus + 1;

                                    }
                                    break;
                                case 2: // >=
                                    if ($row->grade >= $requirement->percenttoachieve){
                                    $awardStatus = $awardStatus + 1;

                                    }                   
                                    break;
                                case 3: // =
                                    if ($row->grade == $requirement->percenttoachieve){
                                    $awardStatus = $awardStatus + 1;

                                    } 
                                    break;
                                default:
                                    $stringToReturn = "ERROR in DB"; //Some sort of db storing error of condition
                                }
                            }
                        }
                }
                        if ($awardStatus == $numberOfReq){
                         //   echo "HELLO";
                           // $student->currentpointbalance += $pointearningscenaio->pointvalue;
                            $record_stu = new stdClass();
                            $record_stu->id                        = intval($student->id);
                            $record_stu->course_id                 = intval($student->course_id);
                            $record_stu->firstname                 = $student->firstname;
                            $record_stu->lastname                  = $student->lastname;
                            $record_stu->student_id                = $student->student_id;
                            $record_stu->currentpointbalance       = intval($student->currentpointbalance + $pointearningscenaio->pointvalue);
                            $record_stu->accumulatedpoints         = intval($student->accumulatedpoints + $pointearningscenaio->pointvalue);
                            $record_stu->leaderboardoptstatus      = $student->leaderboardoptstatus;

                            $DB->update_record('epp_student', $record_stu, false);

                            //$student->accumulatedpoints += $pointearningscenaio->pointvalue;

                            $record = new stdClass();
                            $record->student_id   = intval($student->student_id);
                            $record->pes_id       = intval($pointearningscenaio->id);
							$dateTimeCurrent = new DateTime();
							$record->dateearned   = $dateTimeCurrent->format('Y-m-d H:i:s');
							$record->pointsearned = intval($pointearningscenaio->pointvalue);
							$DB->insert_record('epp_student_pes', $record, false);
							echo $OUTPUT->box('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>');
                            echo $OUTPUT->box_start();
                            echo '<div style="width:50%; margin:0 auto;">' .$student->firstname  .' ' . $student->lastname . ' has been awarded: '.$pointearningscenaio->pointvalue . ' Points </div>';
                            echo $OUTPUT->box_end();
                        }
            }
        }   
    }
}

echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
// Finish the page
echo $OUTPUT->footer();

