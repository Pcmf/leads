angular.module('appMain').controller('rDetController',function($scope,$http,$routeParams,NgTableParams){
    $scope.fornId = $routeParams.forn;
    var sts = $routeParams.sts;
    var data1 = $routeParams.data1;
    var data2 = $routeParams.data2;
   
    $http({
        url:'relatorios/php/getListBySts.php',
        method:'POST',
        data:JSON.stringify({'forn':$scope.fornId,'sts':sts,'data1':data1,'data2':data2})
    }).then(function(answer){
            $scope.fornecedor = answer.data[0].nomeFornecedor;
            var data = answer.data;
            $scope.paramsTable = new NgTableParams({
           },{
               dataset:data
           }); 
    });
    
    
    $scope.goBack = function(){
        window.history.back();
    };
    
    
});


