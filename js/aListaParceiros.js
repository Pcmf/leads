/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('listParcController',function($scope,$http){

    //get parceiros from DB
    listaParceiros();
    
    
    //Edit User
    $scope.editUser = function(u){
        $scope.edit = u;
    };
    
    //Remove User
    $scope.removeUser = function(u){
        if(u !== undefined && u !== {}){
            $http({
                url:'php/admin/editarParceiro.php',
                method:'POST',
                data:JSON.stringify({'parceiro':u,'op':'D'})
            }).then(function(answer){
                if(answer.data){
                    alert(answer.data);
                }
                $scope.edit = {};
                listaParceiros();
            });
        }
    };
    
    //Save or update parceiro
    $scope.saveUser = function(u){
            $http({
                url:'php/admin/editarParceiro.php',
                method:'POST',
                data:JSON.stringify({'parceiro':u,'op':'IU'})
            }).then(function(answer){
                $scope.edit = {};
                listaParceiros();
            });
    };
    
    //Clear form
    $scope.clear = function(){
        $scope.edit = {};
    };
    
    
    
    function listaParceiros(){
           $http({
           url:'php/getData.php',
           method:'POST',
           data:'cad_parceiros'
       }).then(function(answer){
           $scope.parceiros= answer.data;
       });
   }   
});

