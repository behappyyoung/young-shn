<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ypark
 * Date: 8/28/13
 * Time: 9:32 AM
 * To change this template use File | Settings | File Templates.
 */


require_once(dirname(__FILE__) . "/classes/H2hra.php");

$token = H2hra::getAdminSession();
$questions =  H2hra::getQuestions($token);

foreach($questions as $sections){

    $QuestionnaireSection = $sections['QuestionnaireSection'];
    $Questionnaire = $sections['Questionnaire'];

    $myquestion[$QuestionnaireSection['id'].'/'.$QuestionnaireSection['id']] = array(
        'h2_question_id'=>$QuestionnaireSection['id'],
        'category' => $QuestionnaireSection['name'],
        'name' =>    $QuestionnaireSection['name'],
        'h2_title' =>    $QuestionnaireSection['name'],
        'h2_desc' =>    $QuestionnaireSection['name'],
        'h2_type' => '0',
        'main' => $QuestionnaireSection['id']
    );
    foreach($Questionnaire as $question){
        switch($question->gender){
            case 'both' : $type = 0;
                break;
            case 'male' : $type = 1;
                break;
            case 'female' : $type = 2;
                break;
			default : $type='0';
				break;
        }
        $myquestion[$QuestionnaireSection['id'].'/'.$question['id']] = array(
            'h2_question_id'=>$question['id'],
            'category' => $QuestionnaireSection['name'],
            'name' =>    $question['slug'],
            'h2_title' =>    $question['title'],
            'h2_desc' =>   str_replace('"', '\'' , $question['description']),
            'h2_type' => $type,
            'main' => $QuestionnaireSection['id']
        );

        $answers = $question['QuestionnaireAnswer'];
        if(!empty($answers)){
            foreach($answers as $answer){

                $myanswer[$answer['id']] = array(
                    'h2_answer_id'=> $answer['id'],
                    'h2_uuid'=> $answer['uuid'],
                    'shn_hra_question_id'=> $answer['questionnair_id'],
                    'h2_desc'=> $answer['answer'],
                    //      'score'=> $answer['score'],
                    'h2_type'=>'select'            // all select for now
                );
            }

        }
    }

}


$qid = H2hra::getLocalQuestionIDs();
$aid = H2hra::getLocalAnswerIDs();

$result = 'OK';
if(!empty($myquestion)) {
foreach($myquestion as $id => $questionArray){
    if(in_array($id, $qid)){  //exists => update
        try{
            $qidmain = explode('/', $id);
            H2hra::updateH2Question($qidmain[1], $qidmain[0], $questionArray);
        }catch (Exception $e){
            $result .= 'Error : '. $e->getMessage();
        }
    }else{  // insert
        try{
            H2hra::saveQuestion($questionArray);
        }catch (Exception $e){
            $result .= 'Error : '. $e->getMessage();
        }


    }
}
}
if(!empty($myanswer)) {
    foreach($myanswer as $id => $answerArray){
        if(in_array($id, $aid)){  //exists => update
            try{
                H2hra::updateH2Answer($id, $answerArray);
            }catch (Exception $e){
                $result .= 'Error : '. $e->getMessage();
            }
        }else{  // insert
            try{
                H2hra::saveAnswer($answerArray);
            }catch (Exception $e){
                $result .= 'Error : '. $e->getMessage();
            }
        }
    }
}

?>
