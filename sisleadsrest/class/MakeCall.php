<?php
require_once '../restful/pushNotificationFunction.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MakeCall
 *
 * @author pedro
 */
class MakeCall {
    private $db;
    
    public function __construct($params) {
        $msg = array("telefone"=>$params->telefone);
        return sendPushNotificationToFCM($params->deviceId, $msg);
    }
}
