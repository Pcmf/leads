/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
app = angular.module('appDash',[]);

angular.module('appDash').controller('dashController',function($scope,$http,$interval){
    $scope.d ={};
    
    getData();
    $interval(getData,30000);
    
    function getData(){
        $http({
           url:'dashboard.php'
       }).then(function(answer){
           $scope.d.recebidas= answer.data.recebidas;
           $scope.d.porcontactar= answer.data.porcontactar;
           $scope.d.contactados= answer.data.contactados;
           $scope.d.naoatendidas= answer.data.naoatendidas;
           $scope.d.paraanalise= answer.data.paraanalise;
           $scope.d.anuladas= answer.data.anuladas;
           $scope.d.pedidodoc= answer.data.pedidodoc;
           $scope.d.aprovados= answer.data.aprovados;
           $scope.d.financiados= answer.data.financiados;
           $scope.d.naoatendidasMes= answer.data.naoatendidasMes;
           $scope.d.anuladasMes= answer.data.anuladasMes;
           $scope.d.pedidodocMes= answer.data.pedidodocMes;
           $scope.d.aprovadosMes= answer.data.aprovadosMes;
           $scope.d.financiadosMes= answer.data.financiadosMes;
       });     
       //Numero aleatorio para a imagem
       $scope.x=Math.floor( Math.random()*100%5);
    }
});
