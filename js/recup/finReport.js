/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appRec').controller('finReportController',function($scope,$http){
 
    
    var M = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    


            //aceder á BD
            $http({
                url:'php/finReport.php',
                method:'POST',
                data:JSON.stringify({'user': sessionStorage.userId})
            }).then(function(answer){
                var dt=[];
                var labels = [];
                var data = [];
                //Financiados
                (answer.data[0][0]).forEach(function(ln){
                        labels.push(M[ln.label-1]+' '+ln.labelAno);
                        dt.push(ln.data);
                });
                data.push(dt);
                
                console.log(answer.data[1][0]);
                var dt=[]; 
                //Valor Financiado
                (answer.data[1][0]).forEach(function(ln){
                        dt.push(ln.data);
                });    
                data.push(dt);
                
              //dts  $scope.colors = ['Green', 'Red'];
                $scope.labels = labels;
                $scope.series = ['Financiados','Valor (K)'];
                $scope.data = data;
                    
                
                
            });
    
    
});


