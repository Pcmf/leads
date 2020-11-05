/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('appGest').controller('callAskForDocController', function ($scope, $rootScope, $http, $modal, $routeParams) {
    
    $scope.mkCall = true;

    $scope.lead=$routeParams.lead;
    getDados( $scope.lead);
    
    $scope.voltar = function() {
        if(!$scope.mkCall){
            terminarChamada();
        }
        window.location.replace('#!/dashboard');
    }
    
    $scope.alterar = function(email) {
        $http({
            url:'php/gestor/updateEmail.php',
            method:'post',
            data:JSON.stringify({'lead':$scope.lead, 'email':email})
        }).then(function(answer){
            alert('Email alterado!');
        });
    }
    
    $scope.naoAtende = function() {
        if(confirm("Vai registar como chamada não atendida! Pretende continuar?")){
            terminarChamada();
             cleanAgendaDoc();
         }
    }
    
    
     //Button para fazer a chamada
    $scope.makeCall = function () {
        if ($scope.mkCall) {
            $scope.mkCall = !$scope.mkCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": $scope.dl.telefone, "lead": $scope.dl.lead})
            }).then(function (answer) {
                if (answer.data.failure) {
                    alert("Erro na operação. Entre em contacto com o suporte!\n" + answer.data.results[0].error);
                }
            });
        } else {
            terminarChamada();
        }
    };

    //Agendar nova data
    $scope.agendaData = function(){
        if($scope.dataAgenda){
            var dataAgenda = $scope.dataAgenda.getFullYear() + '-' + ($scope.dataAgenda.getMonth()+1) + '-' + $scope.dataAgenda.getDate();
            $http({
                url:'php/gestor/agendaNovaData.php',
                method:'POST',
                data:JSON.stringify({'user': sessionStorage.userId, 'lead': $scope.lead, 'data':dataAgenda})
            }).then(function(answer){
                console.log(answer);
                cleanAgendaDoc();
            });
        }
    };
    
    //Ver documento
    $scope.verDoc = function(doc){
        doc.lead = $scope.lead;
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewDoc_',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
    };
    //Remover um documento da lista
    $scope.removerDoc = function(d){
        if(confirm("Vai remover este documento da lista! \nPretende continuar?")){
            $http({
                url:'php/gestor/removerDoc.php',
                method:'post',
                data: JSON.stringify({'lead': $scope.lead, 'doc':d})
            }).then(function(answer){
                getDados($scope.lead);
            });
        }
    }
    
    // Alterar os documentos a pedir
    $scope.editDocuments = function() {
        //abrir modal com lista de documentação a pedir
        var modalInstance = $modal.open({
            templateUrl: 'modalPedirDocs.html',
            controller: 'modalInstancePedirDocs',
            size: 'lg',
            resolve: {items: function () {
                    return $scope.lead;
                }
            }
        });
        modalInstance.result.then(function(answer){
            //window.location.replace('#!/dashboard');
            getDados($scope.lead);
        });        
    };
    
    //Enviar um novo pedido
    $scope.sendEmail = function(){
        //Enviar o pedido da documentação em falta
                $http({
                    url:"php/gestor/sendEmailMissingDocsA_1.php",
                    method:'POST',
                    data:JSON.stringify({'lead':$scope.lead, 'docFalta': '', outroDoc: '','user':JSON.parse(sessionStorage.userData)})
                }).then(function(answer){
                    alert('Enviado novo pedido de documentação!');
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace("");                    
                });
    };        
    
    
    //Cancelar o processo
    $scope.cancelProcess = function() {
        if(confirm("ATENÇÃO!!\nVai anular esta lead! Pretende continuar?")){
            var modalInstance = $modal.open({
                templateUrl: 'modalRejeitar.html',
                controller: 'modalInstanceRejeitarLead',
                size: 'lg',
                resolve: {items: function () {
                        return $scope.lead;
                    }
                }
            });      
        }
    }
    
    
    function getDados(lead) {
        $http({
        url:'php/gestor/callAskForDoc.php',
        method:'POST',
        data: lead
    }).then(function(answer){
        $scope.dl = answer.data.lead;
        $scope.docs = answer.data.docs;
    });
    }
    
    
    function terminarChamada() {
            $scope.mkCall = !$scope.mkCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
                alert(answer.data.results[0].error);
            });
    }
    
    function cleanAgendaDoc() {
        //Registar o não atende - vai desativar no agendadoc
        $http({
            url:'php/gestor/updateAgendaDoc.php',
            method:'POST',
            data:JSON.stringify({'lead':$scope.lead, 'ativa':0})
        }).then(function(answer){
            //volta para o dashboard
           sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
           window.location.replace('#!/dashboard');
        });        
    }
    
});


/**
 * Modal instance to enviar email a pedir Documentos
 */
angular.module('appGest').controller('modalInstancePedirDocs', function($scope,$modalInstance,$http,items){
    $scope.lead = items;
    $scope.d = {};
    $scope.outroDoc ='';
    //Obter lista de documentação 
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_docnecessaria'
    }).then(function(answer){
        $scope.docs = answer.data;
        
        //Pedir a documentação selecionada
        $scope.enviarPedidoDoc = function(d){
            if(d){
                $http({
                    url:"php/sendEmailMissingDocsA.php",
                    method:'POST',
                    data:JSON.stringify({'lead':$scope.lead,'docFalta':d.docs,'outroDoc':$scope.outroDoc,'user':JSON.parse(sessionStorage.userData)})
                }).then(function(answer){
                   alert(answer.data);
 //                   $modalInstance.close('OK');
                     sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace("");
                });
            }
        };
        //Enviar o pedido da documentação em falta
        $scope.enviarPedidoDocEmFalta = function(lead,d){
            if(d){
                $http({
                    url:"php/gestor/sendEmailMissingDocsA_1.php",
                    method:'POST',
                    data:JSON.stringify({'lead':lead,'docFalta':d.docs,'outroDoc':$scope.outroDoc,'user':JSON.parse(sessionStorage.userData)})
                }).then(function(answer){
                    alert(answer.data);
//                    $modalInstance.close('OK');
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace("");
                });
            }
        };
    });
    

    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to register Rejection
 */
angular.module('appGest').controller('modalInstanceRejeitarLead', function($scope,$http,$modalInstance,items){
    $scope.m ={};
    $scope.rejeitar = function(){
      if(!$scope.r){
          alert("Tem de selecionar um motivo ou descrever!");
      } else {
                
                var param = {};
                var lead = {'id':items};
                param.user = JSON.parse(sessionStorage.userData);
                param.lead = lead;
                param.motivo = $scope.r;
                $http({
                    url:'php/registarRejeicao.php',
                    method:'POST',
                    data:JSON.stringify(param)
                }).then(function(answer){
                    console.log(answer);
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';  
                  window.location.replace("");
          });
            }
    };
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to view document. 
 */
angular.module('appGest').controller('modalInstanceViewDoc_', function($scope,$http,$modalInstance,items,$rootScope, $sce){
    $scope.nomedoc = items.nomedoc;
 
      $rootScope.prograssing = true;  
      $http({
          url:'php/getDocBase64.php',
          method:'POST',
          data:JSON.stringify(items)
      }).then(function(answer){

            if(answer.data.tipo =='jpg'){
                console.log('JPG: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + answer.data.fx64);
            } else if (answer.data.tipo =='jpeg'){
                console.log('JPEG: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpeg;base64,' + answer.data.fx64);
            } else {
                console.log('PDF: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
            }
          $rootScope.prograssing = false;
      }); 

    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  


});