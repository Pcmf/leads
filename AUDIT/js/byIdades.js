/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('appMain').directive('selectOnClick', ['$window', function ($window) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                element.on('click', function () {
                    if (!$window.getSelection().toString()) {
                        // Required for mobile Safari
                        this.setSelectionRange(0, this.value.length)
                    }
                });
            }
        };
    }]);

angular.module('appMain').controller('byIdadesController', function ($scope, $http) {

    $scope.grafico = 1;
    $scope.sl = {};
    $scope.sl.gid = ["25-34", "35-44", "45-54", "55-64", "64-80"];
    var M = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    var a = new Date();
    $scope.sl.ano = a.getFullYear();
    $scope.sl.mes1 = 1;
    $scope.sl.mes2 = a.getMonth() + 1;
    $scope.sl.fornSel = '';
    $scope.sl.tipocredito = '';



    $http({
        url: 'AUDIT/php/getEmpresas.php'
    }).then(function (answer) {
        $scope.fornecedores = answer.data;
        $scope.submeter();

    });




    // Submeter seleção - validar
    $scope.submeter = function () {

//            //Obter dados da BD
        $http({
            url: 'AUDIT/php/byIdades.php',
            method: 'POST',
            data: JSON.stringify($scope.sl)
        }).then(function (answer) {

             $scope.entradas = answer.data.entradas;
            console.log($scope.entradas);
  //          $scope.entradas = answer.data.entradas;
            $scope.financiadasV = answer.data.financiadasV;
            $scope.financiadasQ = answer.data.financiadasQ;

            //Grafico

            $scope.labels = M.slice($scope.sl.mes1 - 1, $scope.sl.mes2);
            
            $scope.series = [$scope.sl.gid[0], $scope.sl.gid[1], $scope.sl.gid[2], $scope.sl.gid[3], $scope.sl.gid[4]];

            $scope.onSelectGrafico(1);


        });
    }
    
    
                $scope.onSelectGrafico = function (g) {
                if (g == 1) {
                    $scope.grafico = 1;
                    
                    $scope.data = $scope.entradas;
                } else if (g == 2) {
                    $scope.grafico = 2;
                    $scope.data = $scope.financiadasQ;
                } else {
                    $scope.grafico = 3;
                    $scope.data = $scope.financiadasV;
                }
            }

});



