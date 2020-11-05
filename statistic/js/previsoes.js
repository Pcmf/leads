/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('previsoesController',function($scope, $http){
     $scope.sl = {};
     $scope.tml = {};
     $scope.tml.opc = 'mes';
     $scope.fornSel = '';
     
    $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_fornecedorleads'
    }).then(function(answer){
        $scope.fornecedores = answer.data;
    
        });
    
     //Aplicar filtro
    $scope.applyFilter = function(sl){
        console.log($scope.fornSel);
        sl.fornecedor = $scope.fornSel;
        if (sl.data1) {
            var dia = sl.data1.getDate();
            var mes = sl.data1.getMonth() + 1;
            var ano = sl.data1.getFullYear();
            sl.data11 = (ano + '-' + mes + '-' + dia).toLocaleString();

            if (sl.data2) {
                var dia = sl.data2.getDate();
                var mes = sl.data2.getMonth() + 1;
                var ano = sl.data2.getFullYear();
                sl.data22 = (ano + '-' + mes + '-' + dia).toLocaleString();
            } else {
                sl.data22 = null;
            }
        }
        console.log(sl);
            //Obter dados da BD
            $http({
                url:'statistic/previsoes.php',
                method:'POST',
                data:JSON.stringify({'sl':sl})
            }).then(function(answer){
                $scope.recebidos = answer.data.recebidos;
                $scope.aprovados = answer.data.aprovados;
                $scope.valorAprovado = answer.data.valorAprovado;
                $scope.financiados = answer.data.financiados;
                $scope.valorFin = answer.data.valorFin;
                $scope.desistencias = answer.data.desistencias;
                $scope.valorDesistencias = answer.data.valorDesistencias;
                $scope.semanal = answer.data.semanal;
            });
    }
    
    //Limpar dados
    $scope.clearFilter = function(){
        $scope.tml = {};
    }
    
    //limpar opções
    $scope.cleanOpc = function(){
        $scope.tml.opc='';
    }
    
    //Limpar datas
    $scope.clearDatas = function(){
        $scope.tml.data1='';
        $scope.tml.data2='';
    }
    
  //  });
});

