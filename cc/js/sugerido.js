/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('sugeridoController',function($scope,$http,$interval,$rootScope, $modal, NgTableParams){

    //listar os sugeridos
    function getData(){
            $http({
                url:'cc/php/getSugeridos.php',
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
    $scope.respostaCliente = function(lead, resp){
        $http({
            url:'cc/php/respostaSugestao.php',
            method:'POST',
            data:JSON.stringify({'lead': lead, 'resp':resp})
        }).then(function(answer){
            getData();
        });
    };
    
 
    
});

