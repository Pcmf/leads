<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeleteLogEmail
 *
 * @author pedro
 */
class DeleteLogEmail {

    public function __construct($con, $email) {
                mysqli_query($con, "DELETE FROM arq_logemail WHERE email LIKE ".$email);
    }
}
