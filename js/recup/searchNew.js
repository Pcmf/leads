/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appRec').controller('searchNewController',function($scope, $http){
    //Obter lista dos Gestores
    $scope.l={};
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        var gestores=[];
        answer.data.forEach(function(ln){
            if((ln.tipo=='Gestor' || ln.tipo=='GRec') && ln.ativo==1){
                gestores.push(ln);
            }
        });
        $scope.gestores = gestores;
    });
    //Botão para limpar campos de pesquisa
    $scope.clean = function(){
        $scope.c ={};
        $scope.result = [];
    }
    //Botão  da pesquisa
    $scope.searchNew = function(c){
        if(c && (c.telefone || c.email || c.nif)){
            $http({
                url:'php/admin/searchNew.php',
                method:'POST',
                data:JSON.stringify(c)
            }).then(function(answer){
                $scope.result = answer.data;
                console.log(answer.data.length);
            });
        } else{
            alert('Tem de preencher pelo menos um campo de pesquisa!!');
        }
    }
    
    //Botão para confirmar a atribuição
    $scope.atribuirLead = function(lead,user){
        if(lead && user){
        $http({
            url:'php/admin/atribuirNew.php',
            method:'POST',
            data:JSON.stringify({'user':user,'lead':lead})
        }).then(function(answer){
           // alert(answer.data);
            $scope.l ={};
            $scope.c ={};
            $scope.result = [];
            $scope.g ={};
        });
    } else {
        alert('Tem de selecionar a LEAD e o Gestor!');
    }
    
    };
    
    
});

