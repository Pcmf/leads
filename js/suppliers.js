/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('suppliersController',function($scope,$http){
    
    //get Suppliers from DB
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_fornecedorleads'
    }).then(function(answer){
        $scope.suppliers = answer.data;
    });
    
        //Edit supplier
    $scope.editSupplier = function(s){
        $scope.edit = s;
    };
    //Clear form
    $scope.clear = function(){
        $scope.edit = {};
    };
    
        //Remove supplier
//    $scope.removeSupplier = function(s){
//        if(s !== undefined && s !== {} && confirm("Pretende remover este fornecedor?")){
//            $http({
//                url:'php/removeSupplier.php',
//                method:'POST',
//                data:s.id
//            }).then(function(answer){
//                $scope.edit = {};
//                $scope.suppliers = answer.data;
//            });
//        }
//    };

    //Save or update supplier
    $scope.saveSupplier = function(s){

            if(s.outrainfo === undefined){
                s.outrainfo ='';
            }
            if(s.api == 0){
                s.password = '';
            }
            $http({
                url:'php/admin/saveSupplier.php',
                method:'POST',
                data:JSON.stringify(s)
            }).then(function(answer){
                $scope.suppliers = answer.data;
                $scope.edit = {};
            });
        
    };
    
    
});
