/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('relFornController',function($scope,$http){
    
//        $scope.sl= {};
//        $scope.fornId =0;
    
    $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_fornecedorleads'
    }).then(function(answer){
        $scope.fornecedores = answer.data;
    });

    
    //Limpar dados
    $scope.limparDados = function(){
        $scope.sl.fornSel = null;
        $scope.sl.data1 = null;
        $scope.sl.data2 = null;
    };
    
    
    $scope.criarRelatorio = function(sl){
            sl.data11= null;
            sl.data22 =null;
        
            if(sl.data1){
                var dia= sl.data1.getDate();
                var mes = sl.data1.getMonth()+1;
                var ano = sl.data1.getFullYear();
                sl.data11 = (ano+'-'+mes+'-'+dia).toLocaleString();
            } else{
                sl.data11 =null;
            }
            if(sl.data2){
                var dia= sl.data2.getDate();
                var mes = sl.data2.getMonth()+1;
                var ano = sl.data2.getFullYear();
                sl.data22 = (ano+'-'+mes+'-'+dia).toLocaleString();                
            } else{
                sl.data22 =null;
            }  
            
            $scope.fornecedor = sl.fornSel.nome;
            $scope.datas = sl.data11+'/'+sl.data22;
            
          $http({
              url:'relatorios/php/criarRelatorioForn.php',
              method:'POST',
              data:JSON.stringify({'sl':sl})
          }).then(function(answer){
                console.log(answer.data);
                $scope.fornId = sl.fornSel.id;
                $scope.respostas = answer.data;
          });
        
    }
    
});

