/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appMain').controller('adashboardController',function($scope,$http,$interval){
 
    $scope.timeLine ='mes'

    getInfoF($scope.timeLine);
    
    var M = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    
    $scope.getInfo = function(tm){
        getInfoF(tm);
    };
    
    
    
    
    
    
    //Select data timeline
    function getInfoF(tm){
        if(tm=='periodo'){
            alert('Irá abrir modal para selecionar um periodo');
        } else{
            //aceder á BD
            $http({
                url:'php/admin/adashboardInfo.php',
                method:'POST',
                data:$scope.timeLine
            }).then(function(answer){
                var dt=[];
                var labels = [];
                var data = [];
                
                //recebidas
                (answer.data[0][0]).forEach(function(ln){
                        labels.push(M[ln.label-1]+' '+ln.labelAno);
                        dt.push(ln.data);
                });
                data.push(dt);
                
                var dt=[]; 
                //Anuladas
                (answer.data[3][0]).forEach(function(ln){
                        dt.push(ln.data);
                });    
                data.push(dt);
                
                var dt=[];
                 //Recusados/Não Aprovados/Desistencias
                (answer.data[4][0]).forEach(function(ln){
                        dt.push(ln.data);
                });
                data.push(dt);

                var dt=[]; 
                 //financiadas
                (answer.data[1]).forEach(function(ln){
                        dt.push(ln.data);
                });                
                data.push(dt);   
                var dt=[]; 
                 //Valor
                (answer.data[2]).forEach(function(ln){
                        dt.push(ln.data);
                });                
                data.push(dt);                 
               
         //        $scope.colors = ['Blue', 'Red', 'Orange','Green', 'Black'];
                  $scope.labels = labels;
                  $scope.series = ['Recebidas','Anuladas','Não Aprovadas','Financiadas', 'Valor (K)'];
                  $scope.data = data;
                    
                
            });
        }
    };
    
    
});

