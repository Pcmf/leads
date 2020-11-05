/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('chGestController',function($scope,$http,$modal,$routeParams){
    //Obter lista dos Gestores
    $scope.lead='';
    $scope.g ={};
    $scope.g2 ={};    
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        var users=[];
        answer.data.forEach(function(ln){
            if(ln.tipo=='Gestor' && ln.ativo==1){
                users.push(ln);
            }
        });
        $scope.users = users;
    });
    
    //Botão para confirmar a atribuição
    $scope.chOneToGest = function(lead,user){
        if(lead && user){
        $http({
            url:'php/admin/chOneToUser.php',
            method:'POST',
            data:JSON.stringify({'user':user.id,'lead':lead,'tipo':user.tipo})
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
    
    //Botão para confirmar a atribuição
    $scope.chAllToGest = function(userorigem,userdestino){
        if(userorigem && userdestino){
            $http({
                url:'php/admin/chAllToUser.php',
                method:'POST',
                data:JSON.stringify({'userorigem':userorigem.id,'userdestino':userdestino.id,'tipo':userdestino.tipo})
            }).then(function(answer){
               if(answer.data.erro){
                   alert(answer.data.erro);
               }else{
                    $scope.g ={};
                     $scope.g2 ={};
                }
            });
        } else {
            alert('Tem de indicar o Analista origem e o Analista destino!');
        }

        };    
    
});

