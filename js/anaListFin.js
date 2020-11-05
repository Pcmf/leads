/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListFinController',function($scope,$http,NgTableParams,$modal){

    function getData(){
        $http({
            url:"php/analista/getFinanciados.php",
            method:"POST",
            data:JSON.stringify({'user':sessionStorage.userId})
        }).then(function(answer){
            var data = answer.data;
            $scope.paramsTable = new NgTableParams({
            },{
                dataset:data
            }); 
        });
    }
    
    //Inicializa
    getData();
    
    
    //Botão para descarregar todos os documentos para o ambiente de trabalho
    $scope.descarregarContrato = function(lead){
        $http({
        url:'php/analista/getContratos.php',
        method:'POST',
        data:lead
        }).then(function(answer){
            if(answer.data==''){
                alert("Este processo não tem contratos!");
            } else {
                answer.data.forEach(function(ln){
                    if(ln.tipo=='pdf'){
                       download("data:application/pdf;base64,"+ln.fx64,ln.nomefx);
                   } else {
                       alert("Este ficheiro não está no formato correto. Entre em contacto com o suporte!");
                   }
                });
            }
        });
    };  
    
    
    //Botão para alterar para Aprovado e depois alterar para ACP
    $scope.undoFinanciado = function(lead){
        if(confirm("Vai alterar o status para Aprovado. Isto só deverá ser usado para depois alterar para ACP!")){
            $http({
                url:'php/analista/updateStatusAnalista.php',
                method:'POST',
                data:JSON.stringify({'lead':lead, 'status':16, 'financiamento':'ACP'})
            }).then(function(answer){
                console.log(answer.data);
                window.location.replace("#!/dashboard");
            })
        }
    }
    
        //Botão para Anexar Contrato
    $scope.upContrato = function(lead){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarContrato.html',
            controller: 'modalInstanceAnexarContrato',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function(){
            getData();
        });         
    };
});






/**
 * Modal instance to attach Contrato
 */
angular.module('appAnalist').controller('modalInstanceAnexarContrato', function($scope,$http,$modalInstance,items){
    $scope.lead = items;
    $scope.novonome ="";

  
    $scope.saveAttachedDoc = function(){
      if($scope.file){
            //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url:'php/analista/attachContrato.php',
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