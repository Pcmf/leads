/* 
 * Main module 
 */
app = angular.module('appGest',['ngRoute','ngResource','ngAnimate','ngSanitize','naif.base64',
    'ui.bootstrap','angularSpinkit','checklist-model','ngTable', 'luegg.directives',
    'chart.js','ngMask']);

app.config(function($routeProvider){
        $routeProvider
            .when('/new',{templateUrl:'views/gcreateLead.html',controller:'gcreateLeadController'})
            .when('/listPesq/:id',{templateUrl:'views/glistPesq.html',controller:'listPesqController'})
            .when('/agendadas',{templateUrl:'views/gagendadas.html',controller:'gagendadasController'})
            .when('/detLead/:id',{templateUrl:'views/detLead.html',controller:'detLeadController'})
            .when('/listFin',{templateUrl:'views/listFin.html',controller:'listFinController'})
            .when('/listSpeed',{templateUrl:'views/listSpeed.html',controller:'listSpeedController'})
            .when('/list',{templateUrl:'views/list.html',controller:'gListController'})
            .when('/gcontact',{templateUrl:'views/gcontact.html',controller:'gcontactController'})
            .when('/gcontact/:id',{templateUrl:'views/gcontact.html',controller:'gcontactController'})
            .when('/gcontactrec',{templateUrl:'views/gcontact.html',controller:'gcontactRecController'})
            .when('/anuladas/:id',{templateUrl:'views/ganuladas.html',controller:'ganuladasController'})
            .when('/docs/:id',{templateUrl:'views/gdocs.html',controller:'gdocsController'})
            .when('/docDet/:id',{templateUrl:'views/gdocDet.html',controller:'gdocDetController'})
            .when('/finReport',{templateUrl:'views/finReport.html',controller:'finReportController'})
            .when('/portal',{templateUrl:'views/portal.html',controller:'portalController'})
            .when('/call/:lead',{templateUrl:'views/callAskForDoc.html',controller:'callAskForDocController'})
            .when('/processForm/:lead',{templateUrl:'views/process_form.html',controller:'processFormController'})
            .otherwise({templateUrl:'views/gdashboard.html',controller:'gdashboardController'});
});

app.controller('gnavController',function($scope){

    $scope.userData = JSON.parse(sessionStorage.userData);

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
