/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
app = angular.module('appDash',[]);

angular.module('appDash').controller('dashController',function($scope,$http,$interval){
    $scope.d ={};
    $scope.d.recebidas = 233;
    
    $http({
        url:'dashboard.php',
    }).then(function(answer){
        $scope.d.recebidas= answer.data.recebidas;
        $scope.d.porcontactar= answer.data.porcontactar;
        $scope.d.contactados= answer.data.contactados;
        $scope.d.naoatendidas= answer.data.naoatendidas;
        $scope.d.anuladas= answer.data.anuladas;
        $scope.d.pedidodoc= answer.data.pedidodoc;
        $scope.d.aprovados= answer.data.aprovados;
        $scope.d.financiados= answer.data.financiados;
    });
});
