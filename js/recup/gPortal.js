/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appRec').controller('portalController',function($scope,$http){
    
    $scope.lead ='';
    
   $scope.enviarPass = function(){
       
       $http({
           url:'php/gestor/gPortal.php',
           method:'POST',
           data:$scope.lead
       }).then(function(answer){
           alert(answer.data);
          window.location.replace('');
       })
   }
    
});

