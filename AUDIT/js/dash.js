/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('auditDashController',function($scope,$http, NgTableParams, $rootScope){
        //LEADS
    $scope.tml={};
    $scope.tml.opc = 'mes';
    $scope.tml.analista = 99;
    if(sessionStorage.analista) {
        $scope.tml.analista= sessionStorage.analista;
    }
    
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        $scope.analistas = answer.data;
    });
    
    getInfo($scope.tml);
    
    $scope.saveSelected = function(sel){
        sessionStorage.analista = sel;
    }
    
    $scope.verDetalhe = function(lead){
        window.location.assign("#!/detLead/" + lead);
    }
    
    
    //Analise
    $scope.cleanOpc = function(){
        $scope.tml.opc='';
    }
    
    $scope.clearDatas = function(){
        $scope.tml.data1='';
        $scope.tml.data2='';
    }
    $scope.applyFilter = function(tml){
        if (tml.data1) {
            var dia = tml.data1.getDate();
            var mes = tml.data1.getMonth() + 1;
            var ano = tml.data1.getFullYear();
            tml.data11 = (ano + '-' + mes + '-' + dia).toLocaleString();

            if (tml.data2) {
                var dia = tml.data2.getDate();
                var mes = tml.data2.getMonth() + 1;
                var ano = tml.data2.getFullYear();
                tml.data22 = (ano + '-' + mes + '-' + dia).toLocaleString();
            } else {
                tml.data22 = tml.data11;
            }
        }
        tml.analista = sessionStorage.analista;
        getInfo(tml);

    };
    
    $scope.clearFilter = function(){
        $scope.tml={};
        sessionStorage.analista = 99;
    }
    
    function getInfo(tml){
    $rootScope.prograssing = true;
    $http({
        'url':'AUDIT/php/dash.php',
        'method': 'POST',
        'data': JSON.stringify(tml)
    }).then(function(answer){
        var data = answer.data;
        $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           }); 
        $rootScope.prograssing = false;
    });
    
    }
    
});

