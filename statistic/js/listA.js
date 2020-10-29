/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('listAController',function($scope,$http,$routeParams,NgTableParams){
    $http({
        url:'statistic/listA.php',
        method:'POST',
        data:JSON.stringify($routeParams)
    }).then(function(answer){
        var data = answer.data;
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

