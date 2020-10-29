/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListRecController',function($scope,$http,NgTableParams){
    $http({
        url:"php/analista/getRecusados.php",
        method:"POST",
        data:JSON.stringify({'user':sessionStorage.userId})
    }).then(function(answer){
       // $scope.dados = answer.data;
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
        },{
            dataset:data
        });        
    });
    

});

