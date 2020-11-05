<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
	header('Access-Control-Allow-Headers: token, Content-Type');
	die();
}
 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once './class/Leads.php';
require_once './class/User.php';
require_once './class/Docs.php';
require_once './class/Data.php';
require_once './class/COFID.php';
require_once './class/Mural.php';
require_once './class/Users.php';
require_once './class/Client.php';
require_once './class/Report.php';
require_once './class/MakeCall.php';
require_once './class/ProcessoForm.php';

    /*
     ** POSTS
     * *
     */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);
    
    if ($_GET['url'] == "login") {
        $ob = new User();
        $resp = $ob->checkuser1( $postBody->username, $postBody->password );
        if($resp){
            echo json_encode($resp);  
            http_response_code(200);
            } else {
                echo null;
                http_response_code(200);
            }

        } elseif ($_GET['url'] == "cltlogin") {
            $ob = new Client();
            $resp = $ob->login($postBody);
            echo json_encode($resp);  
            http_response_code(200);

        }  elseif($_GET['url'] == 'leads'){
            $ob = new Leads();
            $resp = $ob->setLead($_GET['user'], $postBody );
            if($resp !="Erro"){
                 echo $resp;
                http_response_code(200);
            } else {
                echo $resp;
                http_response_code(204);
            }
        
        } elseif($_GET['url'] == 'savedocs'){
            $ob = new Docs();
            echo json_encode($ob->saveDocs($_GET['lead'], $postBody ));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'cltdocs'){
            $ob = new Client();
            echo json_encode($ob->anexaDoc($postBody ));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'processos'){
            $ob = new COFID();
            echo json_encode($ob->setCOFD( $postBody ));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'mural'){
            $ob = new Mural();
            echo json_encode($ob->setMsg( $postBody ));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'call'){
            echo json_encode( new MakeCall($postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'notas'){
            $ob = new Leads();
            echo json_encode( $ob->saveNotes($postBody));
            http_response_code(200);
        
        } elseif ($_GET['url'] == 'cltupimg') {
            $ob = new Client();
            echo json_encode($ob->anexaFotoAsDoc( $postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'cltcomp') {
            $ob = new Client();
            echo json_encode($ob->anexaComp( $postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'cltupcomp') {
            $ob = new Client();
            echo json_encode($ob->anexaCompImg( $postBody));
            http_response_code(200);
            
        } elseif($_GET['url'] == 'agendar'){
            $ob = new Leads();
            echo json_encode( $ob->agendar($postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'askdocs'){
            $ob = new COFID();
            echo json_encode( $ob->askDocs($_GET['user'], $_GET['lead'], $postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'ccview'){
            $ob = new Client();
            echo json_encode($ob->registaCCPortal($postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'processform'){
            $ob = new ProcessoForm();
            echo json_encode($ob->create($postBody));
            http_response_code(200);
        
        } else {
        http_response_code(400);
    }
   
   //fim do POST 
    
} 
/**
 * GETS
 */
elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
    if($_GET['url']=='leads'){
        $ob = new Leads();
        if(isset($_GET['id'])){
            echo json_encode($ob->getOne($_GET['user'], $_GET['id']));
        } else {
            echo json_encode($ob->getAll($_GET['user']));
        }
        http_response_code(200);
        
    } elseif($_GET['url'] == 'docsnec'){
            $ob = new Docs();
            echo json_encode( $ob->getDocsNeeds($_GET['lead']));
            http_response_code(200);
        
    } elseif($_GET['url']=='lead'){
        $ob = new Client();
            echo json_encode($ob->getLead($_GET['lead']));
            http_response_code(200);
            
    } elseif($_GET['url']=='leadsts'){
        $ob = new Leads();
            echo json_encode($ob->getLeadStatus($_GET['lead']));
            http_response_code(200);
            
    } elseif($_GET['url']=='docs'){
        $ob = new Docs();
        if(isset($_GET['lead'])){
            echo json_encode($ob->getDocs($_GET['lead']));
            http_response_code(200);
        } else {
            echo json_encode($ob->getAll());
            http_response_code(200);            
        }
        
    }  elseif($_GET['url']=='docsp'){
        $ob = new Docs();
            echo json_encode($ob->getDocPedida($_GET['lead']));
            http_response_code(200);
            
    }  elseif($_GET['url']=='doc'){
        $ob = new Docs();
        echo json_encode($ob->getDoc($_GET['lead'], $_GET['linha']));
        http_response_code(200);
        
    }  elseif($_GET['url']=='cltdocs'){
        $ob = new Client();
            echo json_encode($ob->getDocs($_GET['lead']));
            http_response_code(200);
            
    }  elseif($_GET['url']=='cltcomp'){
        $ob = new Client();
        if( isset($_GET['linha']) ){
            echo json_encode($ob->getOneComp($_GET['lead'], $_GET['linha']));
        } else {
            echo json_encode($ob->getComp($_GET['lead']));
        }
            http_response_code(200);
            
    }  elseif($_GET['url']=='cltdocped'){
        $ob = new Client();
            echo json_encode($ob->getDocPedido($_GET['lead'], $_GET['linha']));
            http_response_code(200);
            
    }  elseif($_GET['url']=='getdata'){
        $ob = new Data();
        echo json_encode($ob->getAll($_GET['tabela']));
        http_response_code(200);
        
    } elseif($_GET['url']=='processo'){
        $ob = new Client();
        echo json_encode($ob->getProcessoByLead($_GET['lead']));
        http_response_code(200);
        
    } elseif($_GET['url']=='processos'){
        $ob = new COFID();
        if(!isset($_GET['lead'])){
                echo json_encode($ob->getProcessos($_GET['user']));
        } else {
                echo json_encode($ob->getProcessoByLead($_GET['lead']));
        }
        http_response_code(200);
        
    } elseif($_GET['url']=='dash'){
        $ob = new COFID();
        echo json_encode($ob->getDashData($_GET['user']));
        http_response_code(200);
        
    } elseif($_GET['url']=='dashboard'){
        $ob = new Leads();
        echo json_encode($ob->getDashInfo($_GET['user']));
        http_response_code(200);
        
    } elseif($_GET['url']=='mural'){
        $ob = new Mural();
        echo json_encode($ob->getAll($_GET['user']));
        http_response_code(200);
        
    } elseif($_GET['url']=='users'){
        $ob = new Users();
        echo json_encode($ob->getMuralUsers());
        http_response_code(200);
        
    } elseif($_GET['url']=='search'){
        $ob = new COFID();
        echo json_encode($ob->getAllSerch($_GET['type'], $_GET['data']));
        http_response_code(200);
        
    } elseif($_GET['url']=='cofidis'){
        $ob = new COFID();
        echo json_encode($ob->getCofidis($_GET['user']));
        http_response_code(200);
        
    } elseif($_GET['url']=='list'){
        $ob = new COFID();
        echo json_encode($ob->getList($_GET['user'], $_GET['tipo']));
        http_response_code(200);
        
    }   elseif($_GET['url']=='dashdir'){
        $ob = new COFID();
        echo json_encode($ob->getDashDirData($_GET['user']));
        http_response_code(200);
        
    }  elseif($_GET['url']=='cltcr'){
        $ob = new Client();
        echo json_encode($ob->cltcr( $_GET['lead']));
        http_response_code(200);
        
    }  elseif($_GET['url']=='dgest'){
        $ob = new Client();
        echo json_encode($ob->getDadosGestor( $_GET['lead']));
        http_response_code(200);
        
    } elseif($_GET['url']=='prtpro'){
        $ob = new COFID();
        echo json_encode($ob->getProcessoByLead($_GET['lead']));
        http_response_code(200);
        
    } elseif($_GET['url']=='report'){
        $ob = new Report();
        echo json_encode($ob->getReport($_GET['forn'], $_GET['data1'], $_GET['data2']));
        http_response_code(200);
        
    } elseif($_GET['url']=='reportlist'){
        $ob = new Report();
        !isset($_GET['motivo']) ? $motivo='' : $motivo= $_GET['motivo'];
        echo json_encode($ob->getReportList($_GET['forn'], $_GET['data1'], $_GET['data2'], $_GET['tipo'], $motivo));
        http_response_code(200);
        
    } elseif($_GET['url']=='processform'){
        $ob = new ProcessoForm();
        echo json_encode($ob->getOne($_GET['lead']));
        http_response_code(200);
        
    } else {
        http_response_code(400);
    }
    
    
    //fim dos GET
} 
/**
 * PUTS
 */
elseif ($_SERVER['REQUEST_METHOD'] == "PUT") {
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);   
    
      if($_GET['url'] == 'upleads'){
            $ob = new Leads();
            $resp = $ob->upLead($_GET['lead'], $postBody );
            if($resp !="Erro"){
                 echo $resp;
                http_response_code(200);
            } else {
                echo $resp;
                http_response_code(204);
            }
        
        } elseif ($_GET['url'] == 'updocs') {
            if($_GET['func']=='rec'){
                $ob = new Docs();
                echo json_encode($ob->upDocs($postBody));
                http_response_code(200);
            } else {
                $ob = new COFID();
                echo json_encode($ob->cofidisContacto($postBody));
                http_response_code(200);
            }
            
        } elseif ($_GET['url'] == 'autoag') {
            $ob = new COFID();
            echo json_encode($ob->agendaAutomatica($postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'agendar') {
            $ob = new COFID();
            echo json_encode($ob->agendaManual($postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'anular') {
            $ob = new COFID();
            echo json_encode($ob->anularPeloGR($_GET['user'], $_GET['lead'],$postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'cltpass') {
            $ob = new Client();
            echo json_encode($ob->pedirPass($_GET['type'], $postBody));
            http_response_code(200);
            
        } elseif ($_GET['url'] == 'cltchg') {
            $ob = new Client();
            echo json_encode($ob->changePass($_GET['lead'], $postBody));
            http_response_code(200);
            
        } elseif($_GET['url'] == 'saveform'){
            $ob = new Client();
            echo json_encode( $ob->saveForm($_GET['lead'], $postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'upform'){
            $ob = new Client();
            echo json_encode( $ob->updateForm($_GET['lead'], $postBody));
            http_response_code(200);
        
        } elseif($_GET['url'] == 'upstatus'){
            $ob = new Client();
            echo json_encode( $ob->updateLeadStatus( $postBody->lead, $postBody->status));
            http_response_code(200);        
        } elseif($_GET['url'] == 'savedocs'){
            $ob = new Docs();
            echo json_encode($ob->saveDocs($_GET['lead'], $postBody ));
            http_response_code(200);
        
        }
        
    //fim dos PUT
}
/**
 * DELETES
 */
elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    
    if($_GET['url'] == 'docs'){
        $ob = new Docs();
        echo $ob->delete($_GET['lead'], $_GET['tipodoc']);
        http_response_code(200);
    } elseif($_GET['url'] == 'doc'){
        $ob = new Docs();
        echo $ob->deleteById($_GET['lead'], $_GET['linha']);
        http_response_code(200);
    }
    
    //fim dos DELETE
} else {
    http_response_code(405);
}