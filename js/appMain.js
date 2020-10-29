/* 
 * Main module 
 */
app = angular.module('appMain',['ngRoute','ngResource','ngAnimate','angularFileUpload','ui.bootstrap','ngTable',
    'ngSanitize','naif.base64','angularSpinkit','chart.js', 'luegg.directives', 'ngMask']);

app.config(function($routeProvider){
        $routeProvider
            .when('/users',{templateUrl:'views/admin/ausers.html',controller:'usersController'})
            .when('/suppliers',{templateUrl:'views/admin/asuppliers.html',controller:'suppliersController'})
            //.when('/reqDocs',{templateUrl:'views/reqDocs.html',controller:'reqDocsController'})
            .when('/listParc',{templateUrl:'views/admin/adListaParceiros.html',controller:'listParcController'})
            .when('/searchNew',{templateUrl:'views/admin/searchNew.html',controller:'searchNewController'})
            .when('/chGest',{templateUrl:'views/admin/chGest.html',controller:'chGestController'})
            .when('/chAnalist',{templateUrl:'views/admin/chAnalist.html',controller:'chAnalistController'})
            .when('/rgpd',{templateUrl:'views/admin/listRGPD.html',controller:'rgpdController'})  
           .when('/filtros',{templateUrl:'views/admin/filtros.html',controller:'filtrosController'})
            .when('/chOrder',{templateUrl:'views/admin/chOrder.html',controller:'chOrderController'})
            .when('/estAnalist',{templateUrl:'statistic/views/estAnalist.html',controller:'estAnalistController'})
           .when('/estGest',{templateUrl:'statistic/views/estGest.html',controller:'estGestController'})
            .when('/estRecup',{templateUrl:'statistic/views/estRecup.html',controller:'estRecupController'})    
            .when('/estForn',{templateUrl:'statistic/views/estForn.html',controller:'estFornController'})    
            .when('/listA/:id/:sts/:tm',{templateUrl:'statistic/views/list.html',controller:'listAController'}) 
            .when('/listG/:id/:sts/:tm',{templateUrl:'statistic/views/list.html',controller:'listGController'}) 
            .when('/list/:id/:nome/:sts/:tm',{templateUrl:'statistic/views/list.html',controller:'listStatController'}) 
            .when('/listFin/:forn/:tm',{templateUrl:'statistic/views/listFin.html',controller:'listFinController'}) 
            .when('/listApv/:forn/:tm',{templateUrl:'statistic/views/listApv.html',controller:'listApvController'}) 
            //.when('/detLead/:id',{templateUrl:'statistic/views/detLead.html',controller:'detLeadController'})
            .when('/detLead/:id',{templateUrl:'views/detLead.html',controller:'detLeadController'}) 
            .when('/prev',{templateUrl:'statistic/views/previsoes.html',controller:'previsoesController'})
    //Relatorios
            .when('/relForn',{templateUrl:'relatorios/views/relForn.html',controller:'relFornController'}) 
            .when('/relHon',{templateUrl:'relatorios/views/relHon.html',controller:'relHonController'}) 
            .when('/relAnalist',{templateUrl:'relatorios/views/relAnalist.html',controller:'relAnalistController'}) 
            .when('/relParceiros',{templateUrl:'relatorios/views/relParceiros.html',controller:'relParceirosController'}) 
            .when('/relLeads',{templateUrl:'relatorios/views/relatorioLeads.html',controller:'relLeadsController'})     
            //Para os detalhes dos relatorios
            .when('/rDet/:sts/:forn/:data1/:data2', {templateUrl:'relatorios/views/rDet.html', controller:'rDetController'})
    //Auditoria
            .when('/audit',{templateUrl:'AUDIT/views/dash.html',controller:'auditDashController'}) 
            .when('/reanalise',{templateUrl:'AUDIT/views/reanalise.html',controller:'reanaliseDashController'}) 
            .when('/ByIdades',{templateUrl:'AUDIT/views/byIdades.html',controller:'byIdadesController'}) 
            .when('/ByVencimentos',{templateUrl:'AUDIT/views/byVencimentos.html',controller:'byVencimentosController'}) 
            .when('/sitContratos',{templateUrl:'AUDIT/views/sitContratos.html',controller:'sitContratosController'}) 
            .when('/desempenho',{templateUrl:'AUDIT/views/desempenho.html',controller:'desempenhoController'}) 
            .when('/detalhe',{templateUrl:'AUDIT/views/detalhe.html',controller:'detalheController'}) 

            .otherwise({templateUrl:'views/admin/adashboard.html',controller:'adashboardController'});
});

app.controller('navController',function($scope, $http,$interval){
    $scope.userData = JSON.parse(sessionStorage.userData);
    $scope.myClass= 'css-class-c';
    $scope.btnMural= 'btnMural-c';    
    $scope.accao= '<<';
    $scope.flagMural = "" ;
    $scope.alerta= 'info';
         //Get mensagens
    getInfo();
    $interval(getInfo,60000);   
    //Get utilizadores
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cad_utilizadores'
    }).then(function(answer){
        $scope.utilizadores = answer.data;
    });  
    //Show Mural
    $scope.toogle = function(){
        $scope.myClass == 'css-class-c' ? $scope.myClass='css-class-o' : $scope.myClass='css-class-c'; 
        $scope.btnMural == 'btnMural-c' ? $scope.btnMural='btnMural-o' : $scope.btnMural='btnMural-c'; 
        $scope.accao == '<<' ? $scope.accao='>>' : $scope.accao='<<'; 
        $scope.alerta = 'info';
    }
    
    //Mural
    $scope.selectDestino = function(conv){
        if(!$scope.clicked || $scope.clicked!=conv.id){
            $scope.clicked = conv.id;
            $scope.destino = conv.origem;
        } else{
            $scope.clicked =0;
            $scope.destino = 0;
        }
        $scope.flagMural="";
    }
    //Botão enviar para
    $scope.enviarPara = function(u){
        conversa = {'id':'', 'origem': sessionStorage.userId, 'destino': u.id, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto':'' , 'status':0, 'sentido': 'msg-out' };
        $http({
            url:'php/enviarParaMural.php',
            method:'POST',
            data:JSON.stringify(conversa)
        }).then(function(answer){
            getInfo();
        });
        $scope.assunto='';
        $scope.clicked=0;
    }
    //Enviar resposta para o selecionado
    $scope.enviarResposta = function(){
        if($scope.clicked && $scope.clicked!=sessionStorage.userId){
            conversa = {'id':'', 'origem': sessionStorage.userId, 'destino': $scope.destino, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto':'' , 'status':0, 'sentido': 'msg-out' };
            $http({
                url:'php/enviarParaMural.php',
                method:'POST',
                data:JSON.stringify(conversa)
            }).then(function(answer){
                getInfo();
            });
            $scope.assunto='';
            $scope.clicked=0;
        }
    }
    
      function getInfo(){
        $scope.flagMural="";
       //Get LEADS information
        $http({
            url:'php/admin/getMsgMural.php',
            method:'POST',
            data:JSON.stringify({'user': sessionStorage.userId})
        }).then(function(answer){
           //Mural
           $scope.conversas = answer.data.conversas;
           var convArray = answer.data.conversas;
           $scope.flagMural = "";
           var now = Date.now();
           for(var i= convArray.length-1; i > 0; i--){
               if(convArray[i].sentido == "msg-in" && Math.floor((now - Date.parse(convArray[i].dataenvio))/60000)<=1){
                   $scope.flagMural = "[msg]" ;
                   $scope.alerta = 'warning';
                   break;
               } 
           }        
        });
    }
});

app.run(function($rootScope) {
  $rootScope.typeOf = function(value) {
    return typeof value;
  };
});
app.directive('activeLink', function () {
    return {
        restrict: "A",
        link: function ($scope, element, attrs) {

            element.bind('mouseenter', function () {
                element.css('background-color', 'yellow');
            });
            element.bind('mouseleave', function () {
                element.css('background-color', 'white');
            });
        }
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


app.directive('timeLine', function () {
    
    return{
        restrict:'AE',
        replace: 'true',
        templateUrl: 'templates/timeLine.html'
    };
    
});

