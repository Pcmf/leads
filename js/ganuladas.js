/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('ganuladasController',function($scope,$http,$rootScope,NgTableParams, $routeParams){
    $rootScope.prograssing = true;
    $http({
        url:'php/gestor/ggetAnuladas.php',
        method:'POST',
        data:JSON.stringify({'userId':sessionStorage.userId, 'type': $routeParams.id})
    }).then(function(answer){
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
               },{
                   dataset:data
               });
    //    $scope.anuladas = answer.data;
    $rootScope.prograssing = false;
    });

    
});

