/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('chOrderController',function($scope,$http,$modal,$routeParams){
    //Obter lista dos Gestores
    $scope.lead='';
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        var users=[];
        answer.data.forEach(function(ln){
            if(ln.tipo=='Analista'){
                users.push(ln);
            }
        });
        $scope.users = users;
    });
    
    //Botão para confirmar a atribuição
    $scope.chLeadToAnalise = function(lead,user){
        if(lead && user){
        $http({
            url:'php/admin/chOrderAnalista.php',
            method:'POST',
            data:JSON.stringify({'user':user.id,'lead':lead})
        }).then(function(answer){
           if(answer.data.erro){
               alert(answer.data.erro);
           }else{
                $scope.lead ='';
                 $scope.g ={};
            }
        });
    } else {
        alert('Tem de indicar a LEAD/Nº Cliente e o Analista!');
    }
    
    };
    
    
});

