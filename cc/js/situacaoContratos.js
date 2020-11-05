/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('listContrController',function($scope,$http,NgTableParams,$rootScope, $modal){
    
    getInfo();
    
    //Registar o envio para o cliente
    $scope.enviadoParaCliente = function(lead){
        $http({
            url:'cc/php/registarEnvioCliente.php',
            method:'POST',
            data: lead
        }).then(function(answer){
            getInfo();
        });
    }
    
    //Anexar o contrato e marcar como enviado para parceiro
    $scope.anexarContrato = function(lead){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarContratoCC.html',
            controller: 'modalInstanceAnexarContratoCC',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function(){
            getInfo();
        });         
    };        

    //Aprovação final ou recusa
    $scope.aprovacaoFinal = function(lead, opc){
        $http({
            url:'cc/php/respostaFinal.php',
            method:'POST',
            data:JSON.stringify({'lead': lead, 'opc': opc})
        }).then(function(answer){
            getInfo();
        });
    }

    //Registar que o cartão foi Ativado
    $scope.ativado = function(lead, opc){
        $http({
            url:'cc/php/ativado.php',
            method:'POST',
            data:JSON.stringify({'lead': lead, 'opc': opc})
        }).then(function(answer){
            getInfo();
        });
    }    
    
        //Cancelar CC
    $scope.anularCC = function(lead){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalCancelarCC.html',
            controller: 'modalInstanceCancelarCC',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function(){
            getInfo();
        });         
    };    
    
    
    function getInfo(){
           //Get  information
            $http({
                url:'cc/php/getSituacaoContratos.php',
                method:'POST',
                data:sessionStorage.userId
            }).then(function(answer){
                var data = answer.data;
                    $scope.paramsTable = new NgTableParams({
                   },{
                       dataset:data
                   });
            });
    }
    
});



/**
 * Modal instance to attach Contrato
 */
angular.module('appAnalist').controller('modalInstanceAnexarContratoCC', function($scope,$http,$modalInstance,items){
    $scope.lead = items;

  
    $scope.saveAttachedDoc = function(){
      if($scope.file){
            //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.file = $scope.file;
                $http({
                    url:'cc/php/attachContrato.php',
                    method:'POST',
                    data:JSON.stringify(parm)
                }).then(function(answer){
                    $modalInstance.close();
                });
        
      }  
    };
    
    
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
});

/**
 * Modal instance para cancelar e registar a justificação
 */
angular.module('appAnalist').controller('modalInstanceCancelarCC', function($scope,$http,$modalInstance,items){
    $scope.lead = items;

  
    $scope.guardarCancelado = function(){
            //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.motivo = $scope.motivo;
                $http({
                    url:'cc/php/cancelarCC.php',
                    method:'POST',
                    data:JSON.stringify(parm)
                }).then(function(answer){
                    $modalInstance.close();
                });
        
    };
    
    
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
});