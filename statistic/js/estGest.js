/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appMain').controller('estGestController',function($scope,$http){
 
    $scope.timeLine ='mes'
    //LEADS
    $scope.tml = {};
    $scope.tml.opc = "mes";
    getInfoG($scope.tml);
    //Analise
    
    $scope.getInfoG = function(tm){
        $scope.timeLine = tm;
        getInfoG(tm);
    };
    
    
    $scope.getInfo = function(tm){
        getInfoG(tm);
    };
    $scope.clearFilter = function(){
         $scope.tml = {};
    };
    $scope.clearDatas = function(){
        $scope.tml.data1 =null;
        $scope.tml.data2 =null;
        $scope.tml.data11 =null;
        $scope.tml.data22 =null;        
    };
    $scope.applyFilter = function(tm){
        if(tm.data1){
            $scope.tml.opc=null;
        }
        getInfoG(tm);
    };


    
    
    
    
    //Select data timeline
    function getInfoG(tm){
                    //aceder á BD
            if(tm.data1){
                var dia= tm.data1.getDate();
                var mes = tm.data1.getMonth()+1;
                var ano = tm.data1.getFullYear();
                tm.data11 = (ano+'-'+mes+'-'+dia).toLocaleString();
            } else{
                tm.data11 =null;
            }
            if(tm.data2){
                var dia= tm.data2.getDate();
                var mes = tm.data2.getMonth()+1;
                var ano = tm.data2.getFullYear();
                tm.data22 = (ano+'-'+mes+'-'+dia).toLocaleString();                
            } else{
                tm.data22 =null;
            }    

            //aceder á BD
            $http({
                url:'statistic/getStatisticsByGestor.php',
                method:'POST',
                data:JSON.stringify(tm)
            }).then(function(answer){

                $scope.byGestor = answer.data;
                
                
            });
        }
    
    
});

