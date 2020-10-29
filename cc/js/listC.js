/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('appAnalist').controller('listCController',function($scope,$http,$interval,$rootScope, $routeParams, NgTableParams){
    
    $scope.sts = $routeParams.id;
            $http({
                url:'cc/php/getListC.php',
                method:'PHP',
                data:JSON.stringify({'user':sessionStorage.userId, 'sts': $scope.sts})
            }).then(function(answer){
                var data = answer.data;
                $scope.paramsTable = new NgTableParams({
                },{
                    dataset:data
                });            
            });
});