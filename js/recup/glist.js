/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appRec').controller('gListController',function($scope,$http,NgTableParams){
    
    $http({
        url:"php/gestor/getEmAnalise.php",
        method:"POST",
        data:sessionStorage.userId
    }).then(function(answer){
       // $scope.dados = answer.data;
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           });       
    });
            //Ordenação por campo
        $scope.predicate = 'lead';
        $scope.sort = function (predicate) {
            $scope.predicate = predicate;
        };
        $scope.isSorted = function (predicate) {
            return ($scope.predicate == predicate);
        };
        
    
});

