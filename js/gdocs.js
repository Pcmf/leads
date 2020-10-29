/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('gdocsController',function($scope,$http,$modal,$routeParams,NgTableParams){
    $scope.docType = $routeParams.id;
    $scope.processes ={};
    //Obter processos com status = 1 ou 3 para o user
    var parm = {};
    parm.userId = sessionStorage.userId;
    parm.sts = $scope.docType;
    //Obter dados
    getList(parm);
    
    //Send a new request of documentation
    $scope.sendNewPedido2 = function(p){
        //enviar um email a pedir a documentação em falta
        var param = {};
        param.gestor = JSON.parse(sessionStorage.userData);
        var cliente ={};
        cliente.nome = p.nome;  //contem a informação sobre o cliente
        cliente.email = p.email;  //contem a informação sobre o cliente
        param.cliente = cliente;
        param.lead = p.lead;  //usa a lead para obter a documentação em falta
        
        $http({
            url:'php/gestor/sendEmailMissingDocs.php',
            method:'POST',
            data:JSON.stringify(param)
        }).then(function(answer){
            alert(answer.data);
            getList(parm);
        });
        
    };
    
    
    //Button to Attach documentation and send to analise
    $scope.anexarDocs = function(p){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocs.html',
            controller: 'modalInstanceAnexarDocs',
            size: 'lg',
            resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function(){
            getList(parm);
        });
        
    };
    
        //Button to reject LEAD
    $scope.anularLead = function(lead){
        //Agendamento and go to dashboard
            var modalInstance = $modal.open({
                templateUrl: 'modalRejeitar.html',
                controller: 'modalInstanceRejeitar2',
                size: 'lg',
                resolve: {items: function () {
                        return lead;
                    }
                }
            });  
    };
    
    //Botão para fazer um novo agendamento para a rececção dos documentos
    $scope.newDataAgendaDoc = function(lead){
        //abrir modal para escolher nova data
            var modalInstance = $modal.open({
                templateUrl: 'modalNovaData.html',
                controller: 'modalInstanceNovaData',
                size: 'md',
                resolve: {items: function () {
                        return lead;
                    }
                }
            });  
           modalInstance.result.then(function(){
                 getList(parm);
            });
    }
    
    //FUNCTIONS
    function getList(param){
        $http({
            url:'php/gestor/getProcessBySit.php',
            method:'POST',
            data:JSON.stringify(param)
        }).then(function(answer){
           // $scope.processes = answer.data;
            var data = answer.data;
            $scope.paramsTable = new NgTableParams({
               },{
                   dataset:data
               });
        });
    }
    
});


/**
 * Modal instance to attach documents and send to analise. 
 */
angular.module('appGest').controller('modalInstanceAnexarDocs', function($scope,$http,$modalInstance,items){
    $scope.lead = items.lead;
    $scope.docs = {};
    $scope.da ={};
    $scope.files = [];
    //Get Documentation necessary (already asked for)
    $http({
        url:'php/gestor/getDocPedidos.php',
        method:'POST',
        data:items.lead
    }).then(function(answer){
        $scope.docs = answer.data;
    });
    
    
    //Apenas quando faz a anexação dos documentos no momento
    $scope.sendToAnalise2 = function(dn,da,sts){
        //guardar o processo/lead, lista da documentação pedida e os fx anexados
        if(da.docs==undefined || da.docs.length==0){
            alert('Tem de selecionar pelo menos um documento anexado!');
        } else {
            //Gravar os dados do formulario
            var parm = {};
            parm.lead = items.lead;
            parm.docsAnx = da;
            parm.status = sts;
            parm.userId = sessionStorage.userId;
            parm.files = $scope.files;
            $http({
                url:'php/gestor/saveAttachDocs.php',
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
 * Modal instance to register Rejection
 */
angular.module('appGest').controller('modalInstanceRejeitar2', function($scope,$http,$modalInstance,items){
    $scope.m ={};
    
    $scope.rejeitar = function(){
      if(!$scope.r){
          alert("Tem de selecionar um motivo ou descrever!");
      } else {
          if($scope.motivoComum=='RGPD'){
            $http({
                url:'php/delRGPD.php',
                method:'POST',
                data:JSON.stringify({'lead':items,'user':sessionStorage.userId})
            }).then(function(answer){
                alert("Toda a informação pessoal foi eleminada!");
                window.location.replace('#!/dashboard');
            });              
          } else {
                var param = {};
                param.user = JSON.parse(sessionStorage.userData);
                param.lead = items;
                param.motivo = $scope.r;
                $http({
                    url:'php/registarRejeicao.php',
                    method:'POST',
                    data:JSON.stringify(param)
                }).then(function(answer){
              //        console.log(answer);
                  window.location.replace("");
                });
            }
        }
    };
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance para agendar uma nova data para receber documentação
 */
angular.module('appGest').controller('modalInstanceNovaData', function($scope,$http,$modalInstance,items){
    $scope.lead = items;
    
    $scope.novaData = function(data){
        //converter a data
        if (data) {
            var dia = data.getDate();
            var mes = data.getMonth() + 1;
            var ano = data.getFullYear();
            data = (ano + '-' + mes + '-' + dia).toLocaleString();
            var novaData = data;
            //atualizar o agendamento
            $http({
                url:'php/gestor/novaDataDoc.php',
                method:'POST',
                data:JSON.stringify({'lead':items, 'user': sessionStorage.userId,'data':novaData})
            }).then(function(answer){
                $modalInstance.close();
            });
            
        } else {
           alert("Tem de selecionar uma data!"); 
        }
        
    }
    
        $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});