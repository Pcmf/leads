/* 
 * Listar Od Processos financiados por Utilizador
 */
angular.module('appRec').controller('listFinController',function($scope,$http,NgTableParams){
    $http({
        url:'php/gestor/getListFin.php',
        method:'POST',
        data:sessionStorage.userId
    }).then(function(answer){
       // $scope.dados = answer.data;
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           });       
    });
    
});