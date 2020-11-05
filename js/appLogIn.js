/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
app = angular.module('appLogIn',[]);

/**
 * Login - faz o log in e tem a opção para registar 
 */
app.controller('loginController', function($scope,$http){
       //Clear data
    $scope.validUser = false;
    window.sessionStorage.clear();
    $scope.error = '';

    $scope.login = function(u){
        $http({
            url:'php/checkUser.php',
            method: 'POST',
            data:{params: JSON.stringify(u)}
        }).then(function(resposta){
          if(resposta.data.aviso !== undefined){
            if(resposta.data.aviso == ""){
            //    alert(JSON.stringify(resposta.data));
                window.sessionStorage.userData = JSON.stringify(resposta.data);
                window.sessionStorage.userId = resposta.data.id;                
                switch(resposta.data.tipo){
                    case 'Administrador':
                        window.location.replace('main.php');
                        break;
                    case 'Analista':
                        window.location.replace('analist.php');
                        break;
                    case 'GRec':
                        window.location.replace('grec.php');
                        break;
                    default :
                        window.location.replace('gest.php');
                }
                
            } else {
                $scope.error = resposta.data.aviso;
            }
        } else{
            $scope.error = 'Problema na base de dados. Por favor contacte o suporte.';
        }
        });            
    }; 

});