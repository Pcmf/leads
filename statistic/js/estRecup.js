/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appMain').controller('estRecupController',function($scope,$http){
 
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
        getInfo(tml);
    };
    
    $scope.clearFilter = function(){
        $scope.tml={};
    }

 

    
    
    
    
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
                url:'statistic/estRecup.php',
                method:'POST',
                data:JSON.stringify(tml)
            }).then(function(answer){
                $scope.d = answer.data;
         
            });
    };
    
    
});
