/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('relGestController',function($scope,$http,NgTableParams){
//        $scope.f = {};
//        $scope.f.allGestores =false;
//        $scope.f.gestor = null;
//        $scope.tml = {};
//        $scope.tml.opc = 'mes';
            
        $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_utilizadores'
        }).then(function(answer){
            $scope.gestores = answer.data;
        });
        
        $scope.toogleGestorAll = function(){
            if($scope.f.allGestores){
                $scope.f.gestor = null;
            }
        }
        $scope.toogleGestor = function(){
            if($scope.f.gestor){
                $scope.f.allGestores = false;
            }
        }    
        
   
        //botão para limpar filtros
        $scope.limparFiltro = function(){
            $scope.f = {};
            $scope.tml = {};
            $scope.tml.opc = 'mes';
        }
        
        //botão de Aplicar filtro 
        $scope.aplicar = function(){
            
            var sl = $scope.tml;
             if(sl.data1){
                var dia= sl.data1.getDate();
                var mes = sl.data1.getMonth()+1;
                var ano = sl.data1.getFullYear();
                sl.data11 = (ano+'-'+mes+'-'+dia).toLocaleString();
            } else{
                sl.data11 =null;
            }
            if(sl.data2){
                var dia= sl.data2.getDate();
                var mes = sl.data2.getMonth()+1;
                var ano = sl.data2.getFullYear();
                sl.data22 = (ano+'-'+mes+'-'+dia).toLocaleString();                
            } else{
                sl.data22 =null;
            }  
            
            $http({
                url:'relatorios/php/getRelatorioData.php ',
                method:'POST',
                data:JSON.stringify({'params':$scope.f, 'tml':$scope.tml, 'data1':sl.data11,'data2':sl.data22})
            }).then(function(answer){
                    var data = answer.data.anuladas;
                    $scope.paramsTable = new NgTableParams({
                   },{
                       dataset:data
                   }); 
                   
                    var data2 = answer.data.agendadas;
                    $scope.paramsTable2 = new NgTableParams({
                   },{
                       dataset:data2
                   }); 
                   
            });
        };
    
});

