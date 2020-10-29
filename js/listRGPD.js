/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('rgpdController',function($scope,$http){
    
    //obter as leads que estão encriptadas 
    function getData(){
        $http({
            url:'php/admin/listRGPD.php',
            method:'POST',
            data:sessionStorage.userId
        }).then(function(answer){
            $scope.leadsList = answer.data;
        });
    }
    
    getData();
    
    //Re-ativar lead
    $scope.reativarLead = function(lead){
        if(confirm("Atenção!\n Vai re-ativar a lead "+lead.lead+". \nPretende continuar? ")){
            $http({
                url:'php/admin/reativarLead.php',
                method:'POST',
                data:JSON.stringify({'lead':lead.lead, 'key':lead.rgpdkey})
            }).then(function(answer){
                alert(answer.data+'\n\nA lead '+lead.lead+' foi re-ativada!');
                getData();
            });
        }
    }

    //Ver detalhes
    $scope.verDetalhes = function(lead){

                alert('Ver detalhes. TO DO');
    }
});

