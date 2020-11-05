/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('appRec').controller('listPesqController',function($scope,$http,$routeParams,NgTableParams){
    
    $scope.user = sessionStorage.userId;
    $http({
        url:'php/glistPesquisa.php',
        method:'POST',
        data:$routeParams.id
    }).then(function(answer){
      // $scope.resultados = answer.data; 
            var data = answer.data;
            $scope.paramsTable = new NgTableParams({
            },{
                dataset:data
            });        
    });

});
