/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appRec').controller('gCreateNewLeadController',function($scope,$http,$modal){
    $scope.newLeadId = 0;
    $scope.c ={};
    
    //Tipo Contrato    
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_fornecedorleads'
    }).then(function(answer){
        
        $scope.fornecedores = answer.data.filter((el) => {return el.ativo == '1'});
    });
   
    
    //Button to open modal to finalize contact and ask for documentation
    $scope.criaLead = function(){
            $http({
                url:'php/recup/createNewLead.php',
                method:'POST',
                data:JSON.stringify({'lead':$scope.c,'user':JSON.parse(sessionStorage.userData)})
            }).then(function(answer){
            if(answer.data>0){
            window.location.replace('#!/detLead/' + answer.data);
        } else {
            alert("Houve um erro!");
        }
    });
    };
    
});
