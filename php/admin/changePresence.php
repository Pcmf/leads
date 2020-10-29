<?php

/* 
 * Altera a presnça do utilizador
 */
require_once '../openCon.php';
$user = json_decode(file_get_contents("php://input"));

echo mysqli_query($con, sprintf("UPDATE cad_utilizadores SET presenca=%s WHERE id=%s", $user->presenca, $user->id));


