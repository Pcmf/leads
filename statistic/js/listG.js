/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('listGController',function($scope,$http,$routeParams,NgTableParams){
    var tml = JSON.parse($routeParams.tm);
    switch (tml.opc) {
        case "mes":
            $scope.timeLine = "Este Mês";
            break;
        case "dia":
            $scope.timeLine = "Hoje" ;
        default:
            if(tml.data22){
                $scope.timeLine = "De "+tml.data11+" até "+tml.data22;
            } else{
                $scope.timeLine = "De "+tml.data11;
            }
            break;
    }
    
    
    $http({
        url:'statistic/listG.php',
        method:'POST',
        data:JSON.stringify($routeParams)
    }).then(function(answer){
//        alert(answer.data);
        var data = answer.data;
        var total=0.0;
        answer.data.forEach(function(ln){
            total += parseInt(ln.montante);
        });
        $scope.total = total;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           }); 
    });
    
            //Ordenação por campo
        $scope.predicate = 'id';
        $scope.sort = function (predicate) {
            $scope.predicate = predicate;
        };
        $scope.isSorted = function (predicate) {
            return ($scope.predicate == predicate);
        };    
    
});

