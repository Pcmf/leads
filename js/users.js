/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('usersController',function($scope,$http){
    $scope.flt = 'nome';
    $scope.edit ={};
    

    getData();
    
    $scope.changeOrderBy = function(ord){
        $scope.flt = ord;
    };

    
    //Edit User
    $scope.editUser = function(u){
        $scope.edit = u;
    };
    
    //Remove User
    $scope.changeUserState = function(u){
        if(u !== undefined && u !== {} && confirm("Pretende alterar a situação deste utilizador?")){
            $http({
                url:'php/admin/changeUserState.php',
                method:'POST',
                data:JSON.stringify(u)
            }).then(function(answer){
                $scope.edit = {};
                $scope.users = answer.data;
                getData();
            });
        }
    };
    
    $scope.alteraPresenca = function() {
        $http({
            url:'php/admin/changePresence.php',
            method:'POST',
            data:JSON.stringify($scope.edit)
        }).then(function(){
            alert("Alterou a presença do utilizador: " + $scope.edit.nome);
        });
    }
    
    //Save or update user
    $scope.saveUser = function(u){
            if(u.outrainfo === undefined){
                u.outrainfo ='';
            }
            
            $http({
                url:'php/admin/saveUser.php',
                method:'POST',
                data:JSON.stringify(u)
            }).then(function(answer){
                $scope.users = answer.data;
                $scope.edit = {};
                getData();
            });
    };
    //Clear password
    $scope.clearPass = function(){
        $scope.edit.password ='';
    };
    
    //Clear form
    $scope.clear = function(){
        $scope.edit = {};
    };
    
    
    //Get Data
    function getData(){
                //get users from DB
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cad_utilizadores'
            }).then(function(answer){
                (answer.data).forEach(function(ln){
                    ln.password ='';
                    ln.rgpdkey=''
                });
                $scope.users = answer.data;
            });
                //get Fornecedores from DB
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cad_fornecedorleads'
            }).then(function(answer){
                $scope.fornecedores = answer.data;
            });            
    }
});



