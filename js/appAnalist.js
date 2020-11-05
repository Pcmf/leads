/* 
 * Main module 
 */
app = angular.module('appAnalist',['ngRoute','ngResource','ngAnimate','ngSanitize','naif.base64',
    'angularSpinkit','ui.bootstrap','checklist-model','ngTable', 'luegg.directives','chart.js', 'ngclipboard']);

app.config(function($routeProvider){
        $routeProvider
            .when('/form1',{templateUrl:'views/analista/form1.html',controller:'anForm1Controller'})
            .when('/form1/:id',{templateUrl:'views/analista/form1.html',controller:'anForm1Controller'})
            .when('/listPesq/:id',{templateUrl:'views/glistPesq.html',controller:'listPesqController'})
            .when('/detLead/:id',{templateUrl:'views/detLead.html',controller:'detLeadController'})    
            .when('/listPend',{templateUrl:'views/analista/anaListPendentes.html',controller:'anaListPendController'})
            .when('/listApr',{templateUrl:'views/analista/anaListAprovados.html',controller:'anaListAprController'})
            .when('/listFin',{templateUrl:'views/analista/anaListFin.html',controller:'anaListFinController'})
            .when('/listFinACP',{templateUrl:'views/analista/anaListFinACP.html',controller:'anaListFinACPController'})
            .when('/listFinRCP',{templateUrl:'views/analista/anaListFinRCP.html',controller:'anaListFinRCPController'})
            .when('/listRec',{templateUrl:'views/analista/anaListRec.html',controller:'anaListRecController'})    
            .when('/listSusp',{templateUrl:'views/analista/anaListSusp.html',controller:'anaListSuspController'})    
            .when('/cc',{templateUrl:'cc/views/dashboard.html',controller:'dashboardController'})    
            .when('/cofid',{templateUrl:'views/analista/cofid.html',controller:'cofidController'})    
            .when('/listsug',{templateUrl:'cc/views/sugerido.html',controller:'sugeridoController'})    
            .when('/listAc',{templateUrl:'cc/views/aceites.html',controller:'aceitesController'})    
            .when('/listContr',{templateUrl:'cc/views/situacaoContratos.html',controller:'listContrController'})    
            .when('/listC/:id',{templateUrl:'cc/views/listC.html',controller:'listCController'})   
            .when('/finReport',{templateUrl:'views/finReport.html',controller:'finReportAController'})
            .otherwise({templateUrl:'views/analista/andashboard.html',controller:'andashboardController'});
});

app.controller('annavController',function($scope){
    $scope.userData = JSON.parse(sessionStorage.userData);
    
    $scope.cartoesView=true;
    $scope.toogle = function(){
        $scope.cartoesView = !$scope.cartoesView;
    }

    
});

app.run(function($rootScope) {
  $rootScope.typeOf = function(value) {
    return typeof value;
  };
});



app.directive('stringToNumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(value) {
        return '' + value;
      });
      ngModel.$formatters.push(function(value) {
        return parseFloat(value);
      });
    }
  };
});
