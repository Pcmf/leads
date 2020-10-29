/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appMain').controller('estFornController',function($scope,$http){
 
    //LEADS
    $scope.tml={};
    $scope.tml.opc = 'mes';
    getInfo($scope.tml);
    //Analise
    
    $scope.cleanOpc = function(){
        $scope.tml.opc='';
    }
    
    $scope.clearDatas = function(){
        $scope.tml.data1='';
        $scope.tml.data2='';
    }
    $scope.applyFilter = function(tml){
//        if(tm.data1){
//            $scope.tml.opc=null;
//        }
        getInfo(tml);
    };
    
    $scope.clearFilter = function(){
        $scope.tml={};
    }

    //Botão para abrir listagem das leads nas condições selecionadas
    $scope.detFLNomeStatus =function(fol){
        window.location.replace("#!/list/"+$scope.fornecedorId+"/"+$scope.origem+"/"+fol.id+"/"+$scope.timeLine,);
    };

    
    
    
    
    //Select data timeline
    function getInfo(tml){
            //aceder á BD
            if(tml.data1){
                var dia= tml.data1.getDate();
                var mes = tml.data1.getMonth()+1;
                var ano = tml.data1.getFullYear();
                tml.data11 = (ano+'-'+mes+'-'+dia).toLocaleString();
            } else{
                tml.data11 =null;
            }
            if(tml.data2){
                var dia= tml.data2.getDate();
                var mes = tml.data2.getMonth()+1;
                var ano = tml.data2.getFullYear();
                tml.data22 = (ano+'-'+mes+'-'+dia).toLocaleString();                
            } else{
                tml.data22 =null;
            }           
            $http({
                url:'statistic/estForn.php',
                method:'POST',
                data:JSON.stringify(tml)
            }).then(function(answer){
                $scope.byFornecedor = answer.data.byFornecedor;
                $scope.total = answer.data.totalRecebidas
            });
    };
    
    
});

//    //Selecionar por Fornecedor
//    $scope.detListByForn = function(f){
//        $scope.fornecedor = f.ForNome;
//        $scope.fornecedorId = f.id;
//
//        $http({
//            url:'statistic/listByFornecedor.php',
//            method:'POST',
//            data:JSON.stringify({'tm':$scope.timeLine,'fornecedor':f})
//        }).then(function(answer){
//            $scope.fornList = answer.data;
//        });
//    };
//    //Mostrar detalhos de um fornecedor/origem por status
//    $scope.detFLNome = function(fl){
//        $scope.origem = fl.nomelead;
//        $http({
//            url:'statistic/listByFornecedorOrigem.php',
//            method:'POST',
//            data:JSON.stringify({'tm':$scope.timeLine,'fornecedor':$scope.fornecedorId,'origem':fl.nomelead})
//        }).then(function(answer){
//            $scope.fornOrigList = answer.data;
//        });        
//    };
//    //Mostrar os detalhes para um fornecedor + tipo
//    $scope.detFLNomeTipo = function(fl){
//        $scope.origem = fl.nomelead;
//        $scope.tipo = fl.tipo;
//        $http({
//            url:'statistic/listByFornecedorOrigem.php',
//            method:'POST',
//            data:JSON.stringify({'tm':$scope.timeLine,'fornecedor':$scope.fornecedorId,'origem':fl.nomelead,'tipo':$scope.tipo})
//        }).then(function(answer){
//            $scope.fornOrigList = answer.data;
//        });         
//    };