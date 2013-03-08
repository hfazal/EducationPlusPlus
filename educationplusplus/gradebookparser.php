<?php

function swerve($DB, $course){
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

                                //Update the epp_student table
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

                                //Make a record in the epp_student_pes table
                                $record = new stdClass();
                                $record->student_id   = intval($student->student_id);
                                $record->pes_id       = intval($pointearningscenaio->id);
    							$dateTimeCurrent = new DateTime();
    							$record->dateearned   = $dateTimeCurrent->format('Y-m-d H:i:s');
    							$record->pointsearned = intval($pointearningscenaio->pointvalue);
    							$DB->insert_record('epp_student_pes', $record, false);

                                //Make a record in the Notification Table
                                $record                 = new stdClass();
                                $record->student_id     = intval($student->student_id);
                                $record->course         = intval($course->id);
                                $record->title          = "Unlocked \"" . $pointearningscenaio->name . "\"";
                                $notificationExpiryDate = new DateTime();
                                $notificationExpiryDate->add(new DateInterval('P90D'));
                                $record->content        = 'You earned ' . $pointearningscenaio->pointvalue . ' points';
                                $record->isread         = 0;
                                $record->expirydate     = $notificationExpiryDate->format('Y-m-d H:i:s');
                                $id = $DB->insert_record('epp_notification', $record, true);
                            }
                }
            }   
        }
    }
}

