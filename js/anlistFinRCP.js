/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListFinRCPController',function($scope,$http,NgTableParams,$modal){
    
function getData(){
    $http({
        url:"php/analista/getFinanciadosRCP.php",
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
    
    getData();
    
    //Botão para descarregar todos os documentos para o ambiente de trabalho
//    $scope.descarregarContrato = function(lead){
//        $http({
//        url:'php/analista/getContratos.php',
//        method:'POST',
//        data:lead
//        }).then(function(answer){
//            if(answer.data==''){
//                alert("Este processo não tem contratos!");
//            } else {
//                answer.data.forEach(function(ln){
//                    if(ln.tipo=='pdf'){
//                       download("data:application/pdf;base64,"+ln.fx64,ln.nomefx);
//                   } else {
//                       alert("Este ficheiro não está no formato correto. Entre em contacto com o suporte!");
//                   }
//                });
//            }
//        });
//    };  
    
    
        //Botão para listar os comprovativos a anexar
    $scope.anexarComprovativos = function(lead){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarComprovativosList.html',
            controller: 'modalInstanceAnexarComprovativosList',
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
    
    
    //Botão para anular por falta de comprovativos
    $scope.anularPorFaltaComprovativo = function(lead){
        if(confirm("Atenção, vai ANULAR esta lead por falta de comprovativos!\nPretende continuar?")){
            $http({
                url:'php/analista/anulaPorFaltaComprovativos.php',
                method:'POST',
                data:lead
            }).then(function(answer){
                getData();
            })
        }
    } 
    
});




/**
 * Modal instance to list Comprovativos
 */
angular.module('appAnalist').controller('modalInstanceAnexarComprovativosList', function($scope,$http,$modal, $modalInstance,items){
    $scope.lead = items;
    $scope.novonome ="";
    
    getList($scope.lead);

    //Botão para abrir modal para  anexar comprovativo
    $scope.anexarComprovativo = function(c){
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarComprovativo.html',
            controller: 'modalInstanceAnexarComprovativo',
            size: 'lg',
            resolve: {items: function () {
                    return c;
                }
            }
        });
        modalInstance.result.then(function(){
            getList($scope.lead);
        });         
    };
    
    //Botão para visualizar comprovativo
    $scope.verComprovativo = function(c){
        //open modal to view comprovativo
        var modalInstance = $modal.open({
            templateUrl: 'modalViewComp.html',
            controller: 'modalInstanceViewComp',
            size: 'lg',
            resolve: {items: function () {
                    return c;
                }
            }
        });
        modalInstance.result.then(function(){
            getList($scope.lead);
        });        
    };
    
    //Botão para Remover o pedido de comprovativo
    $scope.removerPedidoComprovativo = function(c){
        if(confirm("Atenção! Vai remover o pedido deste comprovativo! \nPretende continuar?")){
            $http({
                url:'php/analista/removerPedidoComprovativo.php',
                method:'POST',
                data:JSON.stringify({ 'c': c})
            }).then(function(answer){
                getList($scope.lead);
            });
        }
    };

    //Botão para Remover o comprovativo anexado
    $scope.removerComprovativo = function(c){
        if(confirm("Atenção! Vai remover este comprovativo! \nPretende continuar?")){
            $http({
                url:'php/analista/removerComprovativo.php',
                method:'POST',
                data:JSON.stringify({ 'c': c})
            }).then(function(answer){
                getList($scope.lead);
            });
        }
    };
    
     //Botão para descarregar o comprovativo para o ambiente de trabalho
    $scope.descarregarComprovativo = function(c){
        download("data:application/pdf;base64,"+c.documento,c.nomedoc);
    };    
    
    //Botão para Finalizar
    $scope.finalizar = function(lead){
        if(confirm("Vai fechar esta lead como confirmada o pagamento.\n Continuar?")){
            $http({
                url:'php/analista/finalizarACP.php',
                method:'POST',
                data:lead
            }).then(function(answer){
                $modalInstance.close();
            });
        }
    };
    
    //Close modal
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };     
    
    
    //Function
    function getList(lead){
        $http({
        url:'php/analista/getComprovativosList.php',
        method:'POST',
        data:items
    }).then(function(answer){
        $scope.comprovativos = answer.data;
    });
    }
    
});


/**
 * Modal instance to attach Comprovativos
 */
angular.module('appAnalist').controller('modalInstanceAnexarComprovativo', function($scope,$http,$modalInstance,items){
    $scope.lead = items.lead;
    $scope.linha = items.linha;
    
  
    $scope.saveAttachedComprovativo = function(){
      if($scope.file){
          if($scope.file.filetype!='application/pdf'){
              alert("Apenas é permitida a anexação de PDF!");
          } else {
            //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.linha = $scope.linha;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url:'php/analista/saveComprovativo.php',
                    method:'POST',
                    data:JSON.stringify(parm)
                }).then(function(answer){
                    $modalInstance.close();
                });
            }  
        }
    };    
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };      
});



/**
 * Modal instance to view Comprovativo. 
 */
angular.module('appAnalist').controller('modalInstanceViewComp', function($scope, $modalInstance, items, $sce){
        $scope.nomedoc = items.instituicao;
  
            if(items.tipodoc =='jpg'){
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + items.documento);
            } else {
                $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.documento);
            }
     
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
});


