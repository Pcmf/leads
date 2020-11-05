<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$dt = file_get_contents("../doc/credito360.json");

//echo  $dt;



$array = json_decode($dt);

$fieldsArray =  $array->form_response->definition->fields;

$answersArray = $array->form_response->answers;

foreach ($fieldsArray AS $f){
    echo $f->id."<br/>";
    echo 'Questão: '. $f->title."<br/>";
    echo getAnswer($f->id, $answersArray);
    echo '<br/><br/>';
}





function getAnswer($id, $ans) {
    foreach ($ans AS $a){
        if($a->field->id == $id){
            
            if($a->type =='choice')
                return $a->choice->label;
            
            return $a->{$a->type};
     
        }
    }
}


