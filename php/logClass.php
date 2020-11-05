<?php
/**
 * Description of logClass
 *
 * @author AsusE2
 */
class logClass {
    
    public static function log($con,$u,$log,$t) {
        mysqli_query($con,sprintf("INSERT INTO arq_log(log,user,tipo) "
                . " VALUES('%s',%s,'%s')", $log, $u, $t));
    }

}
