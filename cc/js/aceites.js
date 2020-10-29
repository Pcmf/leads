/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('aceitesController',function($scope,$http,$interval,$rootScope, $modal, NgTableParams){

    //listar os sugeridos
    function getData(){
            $http({
                url:'cc/php/getAceites.php',
                method:'PHP',
                data:sessionStorage.userId
            }).then(function(answer){
                var data = answer.data;
                $scope.paramsTable = new NgTableParams({
                },{
                    dataset:data
                });            
            });
    }
    
    //BEGIN
    getData();
    

    
    //Resposta do  cliente
    $scope.respostaParceiro = function(lead, resp){
        $http({
            url:'cc/php/respostaParceiro.php',
            method:'POST',
            data:JSON.stringify({'lead': lead, 'resp':resp})
        }).then(function(answer){
            alert(answer.data);
            getData();
        });
    };
    
 
    
});

