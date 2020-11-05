<?php
//require_once 'DB.php';
require_once 'passwordHash.php';

/**
 * Description of loginCheck
 *
 * @author pedro
 */
class loginCheck {
    //contruct receive username and passwor and return response: user not exist, password wrong, idHash
    function __construct($user,$db) {
        //verify if user exist
        return $db->query("SELECT tipo FROM cad_utilizadores WHERE nome=:user",array(':user'=>$user));

    }
    
}
