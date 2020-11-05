/* 
 * Listar Od Processos financiados por Utilizador
 */
angular.module('appGest').controller('listSpeedController',function($scope, $rootScope, $http,NgTableParams){
    console.log($rootScope.speedup)
    $scope.data = $rootScope.speedup;
});