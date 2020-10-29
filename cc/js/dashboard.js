/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('dashboardController',function($scope,$http,$interval,$rootScope, $modal){
     
    $scope.n = 4;
    $scope.t = 2;
    $scope.s={};
    
    //Get parceiros
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_parceiros'
    }).then(function(answer){
        var parceiros=[];
        angular.forEach(answer.data, function(ln){
            if(ln.tipoparceiro==1 && ln.ativo==1){
                parceiros.push(ln); 
             }
        });
        $scope.parceiros = parceiros;
    });
    
    //Get  information
    getInfo();
    $interval(getInfo,120000);
   //    $scope.hoje = answer.data.hoje;
    
    function getInfo(){
           //Get  information
            $http({
                url:'cc/php/dashboardInfo.php',
                method:'POST',
                data:sessionStorage.userId
            }).then(function(answer){
               $scope.sugeridos = answer.data.sugeridos;
               $scope.listaAceites = answer.data.aceites;
               $scope.listaContratos = answer.data.listaContratos;
               $scope.qtySugeridos = answer.data.qtySugeridos;
                $scope.qtyAceites = answer.data.qtyAceites;
              $scope.qtyContratos = answer.data.qtyContratos;
               $scope.atribuidos = answer.data.atribuidos;
               $scope.ativados = answer.data.ativados;
               $scope.atribuidos = answer.data.atribuidos;
               $scope.ativados = answer.data.ativados;
               $scope.recusados = answer.data.recusados;
            });
    }
    
    
    $scope.searchLead = function(s){
        if(!s.lead && !s.nome && !s.telefone && !s.email && !s.nif && !s.parceiro){
            alert("Tem de preencher pelo menos um campo!");
        } else{
            window.location.replace("#!/listPesq/"+JSON.stringify(s));
        }
        
    };
        
    //Clear fields
    $scope.clearSearch = function(){
        $scope.s ={};
    };

    //Registar manualmente a sugestão de um cartão
    $scope.registarSugestao = function(){
        var modalInstance = $modal.open({
            templateUrl: 'modalRegistarSugestao.html',
            controller: 'modalInstanceRegistarSugestao',
            size: 'sm'
        });
        modalInstance.result.then(function(){
            getInfo();
        });     
    }

});




/**
 * Modal instance para registar uma sugestão
 */
angular.module('appAnalist').controller('modalInstanceRegistarSugestao', function($scope,$http,$modalInstance){
    $scope.r = {};
    
    //Fechar o modal
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
    //Guardar 
    $scope.saveSugestao = function(r){
        $http({
            url:'cc/php/registarSugestao.php',
            method:'POST',
            data:JSON.stringify({'lead':$scope.r.lead, 'user':sessionStorage.userId, 'formaSugestao':r.formaSugestao})
        }).then(function(answer){
            $modalInstance.close();
        });
    }
    
});