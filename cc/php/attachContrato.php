<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../php/openCon.php';
$json = file_get_contents("php://input");
$dt = json_decode($json);


    
        $query = sprintf("UPDATE cad_cartaocredito SET enviadoparceiro=NOW(), fx64='%s', nomefx='%s' WHERE lead=%s" ,$dt->file->base64, $dt->file->filename, $dt->lead);
        $result =mysqli_query($con, $query);
        if($result){
            
                echo 'ok';
        } else {
            echo $query;
        }

  