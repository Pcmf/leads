/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListSuspController',function($scope,$http,NgTableParams){
    $http({
        url:"php/analista/getSuspensos.php",
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
    
    $scope.ativar = function(lead){
            $http({
                url: 'php/analista/suspenderLeadAprovada.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'status':16})
            }).then(function(answer){
                window.location.replace('#/listApr');
            });
    }

});

