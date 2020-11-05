/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('appGest').controller('gagendadasController',function($scope,$http,NgTableParams){
    
    $http({
        url:'php/gestor/ggetAgendadas.php',
        method:'POST',
        data:sessionStorage.userId
    }).then(function(answer){
        //$scope.anuladas = answer.data;
        console.log(answer.data);
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           });
    });
    
    $scope.ativar = function(lead){
        $http({
            url:'php/updateLeadStatus.php',
            method:'POST',
            data:JSON.stringify({'lead': lead, status: 8, userId: sessionStorage.userId})
        }).then(function(answer){
            window.location.replace("#!detLead/"+lead);
        });
    }
});
