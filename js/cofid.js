angular.module('appAnalist').controller('cofidController',function($scope,$http,NgTableParams){

    getDados();
    
    //Registar resposta
    $scope.resposta = function(p, sts) {
        $http({
            url:'php/analista/respostaCofidis.php',
            method:'POST',
            data:JSON.stringify({'processo': p, 'sts': sts, 'user':sessionStorage.userId})
        }).then(function(answer){
            getDados();
        });
    };


    function getDados(){
            $http({
                url:"php/analista/getCofid.php"
            }).then(function(answer){
              // console.log( answer.data);
                var data = answer.data;
                $scope.paramsTable = new NgTableParams({
                },{
                    dataset:data
                });        
            });
    }
});

