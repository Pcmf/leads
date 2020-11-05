/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('filtrosController', function ($scope, $http) {
    //Obter todos os filtros existentes na tabela
    $scope.nf = {};

    getData();

    $scope.guardarFiltro = function (filtro) {
        console.log(filtro);
        $http({
            url: 'php/admin/saveFiltro.php',
            method: 'POST',
            data: JSON.stringify(filtro)
        }).then(function (answer) {
            console.log(answer);
            limparFitro();
            getData()
        })
    };
    
    $scope.ativarFiltro = function (filtro, status) {
       filtro.status = status;
        $scope.guardarFiltro(filtro);
    }

    $scope.editarFiltro = function (filtro) {
        $scope.nf = filtro;
    };
    
    $scope.copiarFiltro = function (filtro) {
        filtro.id = 0;
        $scope.nf = filtro;
    };

    function limparFitro() {
        $scope.nf.nome = "";
        $scope.nf.codigo = "";
        $scope.nf.filtro = "";
        $scope.nf.id = "";
    }

    function getData() {
        $scope.filtros = {};
        $http({
            url: 'php/getData.php',
            method: 'POST',
            data: 'cad_filtros'
        }).then(function (answer) {
            $scope.filtros = answer.data;
        });
    }
});

