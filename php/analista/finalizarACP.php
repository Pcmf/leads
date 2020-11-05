<?php
require_once '../openCon.php';
$lead = file_get_contents("php://input");

//Alterar o status da lead para 24 (FinanciadoCCP)

mysqli_query($con, sprintf("UPDATE arq_leads SET status=24, datastatus=NOW() WHERE id=%s", $lead));

return;
