/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListPendController',function($scope,$http,NgTableParams){
    $scope.submetidos ={};
    //Get submetidos
    getSubmetidos();
    
    $scope.seeDetailAnalise = function(lead) {
        window.location.href = "#!/form1/"+lead;
    };
    
    function getSubmetidos(){
        $http({
            url:'php/analista/anaGetPendentes.php',
            method:'POST',
            data:sessionStorage.userId
        }).then(function(answer){
                var data = answer.data;
                $scope.paramsTable = new NgTableParams({
                },{
                    dataset:data
                });         
        //  $scope.pendentes = answer.data; 
        });
    }
});

