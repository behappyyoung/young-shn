<?php

require_once(dirname(__FILE__) . "/classes/H2hra.php");

elgg_register_event_handler('init','system','hra_init');

function hra_init() {

    elgg_register_page_handler('hra', 'hra_page_handler');
    $item = new ElggMenuItem('hra', elgg_echo('My Track'), 'hra/');
    elgg_register_menu_item('site', $item);

    $action_base_path = elgg_get_plugins_path() . 'hra/actions';
    elgg_register_action("hra/save_basic", "$action_base_path/patient/save_basic.php");
    elgg_register_action("hra/save_form", "$action_base_path/patient/save_form.php");
    elgg_register_action("hra/save_life", "$action_base_path/patient/save_life.php");
    elgg_register_action("hra/save_finish", "$action_base_path/patient/save_finish.php");
    elgg_register_action("hra/patch_questions", "$action_base_path/admin/patch_questions.php");


}

function hra_public($hook, $handler, $return, $params) {
    $pages = array('hra');
    return array_merge($pages, $return);
}

function hra_page_handler($page) {
    $user = elgg_get_logged_in_user_entity();
    $guid =  $user->getGUID();
    $isadmin= $user->get('admin');
    $username= $user->get('username');
    $email= $user->get('email');
    $name= explode(' ', $user->get('name'));
    $lastname = (sizeof($name)==3)? $name[2] : $name[1];
    $patientsMetadata = elgg_get_metadata(array(
        "metadata_names" =>  array("gender"),
        'metadata_owner_guids' =>  array($guid)
    ));

    foreach($patientsMetadata as $currentMetadata){
        $meta_export= $currentMetadata->export();
        $gender = $meta_export->getBody();
    }

    $patientinfo = array('guid'=>$guid,
                         'username'=> $username,
                            'firstname'=>$name[0],
                            'lastname'=>$lastname,
                            'gender'=>$gender,
                            'email'=>$email);



    if (!isset($page[0])) {
        $page[0] = 'index';
    }
    $page_type = $page[0];
    $hratitle = '<img src="'.elgg_get_site_url().'/mod/hra/views/default/images/assessment_icon.png" /> Health Assessment';
    switch ($page_type) {
        case 'index':
            if($isadmin=='yes'){
                $form = elgg_view('hra_admin');
                $hratitle = '<img src="'.elgg_get_site_url().'/mod/hra/views/default/images/assessment_icon.png" /> Health Assessment Status';
            }else{
                $form = elgg_view('hra_patient', array('patientinfo'=>$patientinfo));
            }
            break;
        case 'basic':
            $form = elgg_view('hra_form_basic', array('guid'=>$guid, 'hra_id'=>$page[1]));
            break;
        case 'life' ;
            $form = elgg_view('hra_form_life', array('guid'=>$guid, 'hra_id'=>$page[1]));
            break;
        case 'form' ;
            $form = elgg_view('hra_form', array('guid'=>$guid, 'current_survey' => $page[1], 'h2_hra_id'=>$page[2]));
            break;
        case 'finish' ;
            $form = elgg_view('hra_finish', array('guid'=>$guid, 'h2_hra_id'=>$page[1]));
            break;
    }


    $header = elgg_view('page/layouts/content/header', array(
        "title" => $hratitle
    ));

    $filter='';
    // Format page
    $body = elgg_view_layout("content",array(
        "content" => $form,
        "filter" => $filter,
        "header" => $header
    ));

  //  $body = elgg_view_layout('one_column', array('content' => $area));
    // Draw it
    echo elgg_view_page(elgg_echo('HRA'), $body);

    return true;
}

