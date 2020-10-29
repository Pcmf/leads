<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeleteRegistoChamadas
 *
 * @author pedro
 */
class DeleteRegistoChamadas {

    public function __construct($con, $telefone) {
        
        mysqli_query($con, "DELETE FROM cad_registochamadas WHERE telefone LIKE ".$telefone);
        
    }
}
