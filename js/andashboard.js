/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('andashboardController',function($scope,$http,$interval,$rootScope){
    $scope.s={};
    $scope.alerta = 'default';
    $scope.presenca = JSON.parse(sessionStorage.userData).presenca;
    console.log($scope.presenca);
    //Get parceiros
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_parceiros'
    }).then(function(answer){
        $scope.parceiros = answer.data;
    });
    //Get utilizadores
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        $scope.utilizadores = answer.data;
    });  
    //Get LEADS information
    getInfo();
    $interval(getInfo,120000);
   //    $scope.hoje = answer.data.hoje;
  
    //Mural
    $scope.selectDestino = function(conv){
        if(!$scope.clicked || $scope.clicked!=conv.id){
            $scope.clicked = conv.id;
            $scope.destino = conv.origem;
        } else{
            $scope.clicked =0;
            $scope.destino = 0;
        }
        $rootScope.flagMural="";
        $scope.alerta = 'default';
    }
    //Botão enviar para
    $scope.enviarPara = function(u){
        conversa = {'id':'', 'origem': sessionStorage.userId, 'destino': u.id, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto':'' , 'status':0, 'sentido': 'msg-out' };
        $http({
            url:'php/enviarParaMural.php',
            method:'POST',
            data:JSON.stringify(conversa)
        }).then(function(answer){
            getInfo();
        });
        $scope.assunto='';
        $scope.clicked=0;
    }
    //Enviar resposta para o selecionado
    $scope.enviarResposta = function(){
        if($scope.clicked && $scope.clicked!=sessionStorage.userId){
            conversa = {'id':'', 'origem': sessionStorage.userId, 'destino': $scope.destino, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto':'' , 'status':0, 'sentido': 'msg-out' };
            $http({
                url:'php/enviarParaMural.php',
                method:'POST',
                data:JSON.stringify(conversa)
            }).then(function(answer){
                getInfo();
            });
            $scope.assunto='';
            $scope.clicked=0;
        }
    }
    
    $scope.seeDetailAnalise = function(lead) {
        window.location.href = "#!/form1/"+ lead;
    };
        
    function getInfo(){
           //Get LEADS information
            $http({
                url:'php/analista/andashboardinfo.php',
                method:'POST',
                data:sessionStorage.userData
            }).then(function(answer){
               $scope.paraAnalise = answer.data.paraAnalise;
               $scope.ativa = answer.data.ativa;
               $scope.emAnalise = answer.data.emAnalise;
               $scope.listaEmAnalise = answer.data.listaEmAnalise;
               $scope.aprovados = answer.data.aprovados;
               $scope.listaAprovados = answer.data.listaAprovados;
               $scope.listaResultados = answer.data.listaResultados;
               $scope.financiados = answer.data.financiados;
               $scope.financiadosACP = answer.data.financiadosACP;
               $scope.financiadosRCP = answer.data.financiadosRCP;
               $scope.suspensos = answer.data.suspensos;
               $scope.alertaACP = answer.data.alertaACP;
               $scope.docOk = answer.data.docOk;
               if(answer.data.alertaACP>0){
                    $rootScope.alertaACP = '!!';
                } else {
                    $rootScope.alertaACP = '';
                }
               $rootScope.valorFinanciado = answer.data.valorFinanciado;
               $scope.recusados = answer.data.recusados;
                //Mural
                $scope.conversas = answer.data.conversas;               
                var convArray = answer.data.conversas;
                $rootScope.flagMural = "";
                var now = Date.now();
                for(var i= convArray.length-1; i > 0; i--){
                    if(convArray[i].sentido == "msg-in" && Math.floor((now - Date.parse(convArray[i].dataenvio))/60000)<2){
                        $rootScope.flagMural = "[msg]" ;
                        $scope.alerta = 'danger';
                        break;
                    } 
                }                           
            });
    }
    
    
    $scope.searchLead = function(s){
        if(!s.lead && !s.nome && !s.telefone && !s.email && !s.nif && !s.process && !s.parceiro && !s.leadorig){
            alert("Tem de preencher pelo menos um campo!");
        } else{
            window.location.replace("#!/listPesq/"+JSON.stringify(s));
        }
        
    };
        
    //Clear fields
    $scope.clearSearch = function(){
        $scope.s ={};
    };

});

