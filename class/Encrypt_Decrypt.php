<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Encrypt_Decrypt
 *
 * @author pedro
 */
class Encrypt_Decrypt {

    private $returnString;

    /**
     * simple method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     * 
     * @param string $action: can be 'encrypt' or 'decrypt'
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     * 
     */
    public function __construct($action, $string, $inkey) {
        if ($string != '' && $string) {
            $encrypt_method = "AES-256-CBC";
//    $secret_key = 'This is my secret key';
            $secret_iv = 'secret';

// hash
            $key = hash('sha256', $inkey);

// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);

            if ($action == 'encrypt') {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } else if ($action == 'decrypt') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }

            $this->returnString = $output;
        } else {
            $this->returnString = '';
        }
    }

    public function getReturnString(){
        return $this->returnString;
    }
}

//$plain_txt = "This is my plain text";
//echo "Plain Text =" .$plain_txt. "\n";
//
//$encrypted_txt = encrypt_decrypt('encrypt', $plain_txt);
//echo "Encrypted Text = " .$encrypted_txt. "\n";
//
//$decrypted_txt = encrypt_decrypt('decrypt', $encrypted_txt);
//echo "Decrypted Text =" .$decrypted_txt. "\n";
//
//if ( $plain_txt === $decrypted_txt ) echo "SUCCESS";
//else echo "FAILED";
//
//echo "\n";
//
//
