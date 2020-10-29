/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('reanaliseDashController',function($scope,$http, NgTableParams){
    $http({
        'url':'AUDIT/php/reanalise.php',
        'method': 'POST',
    }).then(function(answer){
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           }); 
    });
});

