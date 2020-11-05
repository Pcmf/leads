/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('appMain').controller('sitContratosController', function ($scope, $http, $modal) {
    $scope.dados ={};
    $scope.reverse= true;

    $http({
        url: 'AUDIT/php/sitContratos.php'
    }).then(function (answer) {
      //  console.table(answer.data);
       $scope.dados = answer.data;
       
       //TOTAIS
       $scope.totalQty1 = answer.data.reduce((acc, el) =>  acc +  +el.porEnviar[0].qty, 0) ;
       $scope.totalQty2 = answer.data.reduce((acc, el) =>  acc +  +el.noCliente[0].qty, 0) ;
       $scope.totalQty3 = answer.data.reduce((acc, el) =>  acc +  +el.en2via[0].qty, 0) ;
       $scope.totalQty4 = answer.data.reduce((acc, el) =>  acc +  +el.noParceiro[0].qty, 0) ;
       $scope.totalQty5 = answer.data.reduce((acc, el) =>  acc +  +el.suspenso[0].qty, 0) ;

       $scope.totalValor1 = answer.data.reduce((acc, el) =>  acc +  +el.porEnviar[0].valor, 0) ;
       $scope.totalValor2 = answer.data.reduce((acc, el) =>  acc +  +el.noCliente[0].valor, 0) ;
       $scope.totalValor3 = answer.data.reduce((acc, el) =>  acc +  +el.en2via[0].valor, 0) ;
       $scope.totalValor4 = answer.data.reduce((acc, el) =>  acc +  +el.noParceiro[0].valor, 0) ;
       $scope.totalValor5 = answer.data.reduce((acc, el) =>  acc +  +el.suspenso[0].valor, 0) ;

    });
     //Botão para abrir o modal com o detalhe com lista 
        $scope.listar = function(tipo, analista){

            
            var obj = {'tipo': tipo,  'analista': analista};
            var modalInstance = $modal.open({
                templateUrl: 'modalDetListS.html',
                controller: 'modalInstanceDetListS',
                size: 'lg',
                resolve: {items: function () {
                        return obj;
                    }
                }
            });
            modalInstance.result.then(function(){
               //getLeadAllInfo();
            });
        }
    


});

/**
 * Modal instance para ver o Detalhe da Lead
 */
angular.module('appMain').controller('modalInstanceDetListS', function($scope,$http,$modalInstance,items,$modal){
    
      
      $scope.motivo = items.motivo;
      var tipos = [];
      tipos[1] = 'Por Enviar'; 
      tipos[2] = 'No Cliente'; 
      tipos[3] = 'Segunda Via'; 
      tipos[4] = 'No Parceiro'; 
      tipos[5] = 'Suspensos';
      
      $scope.tipo = tipos[items.tipo];
      
      $http({
          url:'AUDIT/php/getDetListSitContratos.php',
          method:'POST',
          data: JSON.stringify(items)
      }).then(function(answer) {
          console.log(answer.data);
          $scope.dados = answer.data;
      });
   
          //Close Modal
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
        //Botão para abrir o modal com o detalhe da lead
    $scope.verDetLead = function(lead){
        var modalInstance = $modal.open({
            templateUrl: 'modalDetLead_2.html',
            controller: 'modalInstanceDetLead_2',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function(){
           //getLeadAllInfo();
        });
    }
    
    $scope.imprimir = function(){
        $http({
            'url': 'relatorios/php/imprimir.php',
            'method': 'POST',
            'data': JSON.stringify({'sts': $scope.tipo, 'data':$scope.dados})
        }).then(function(answer){
            window.open('./relatorios/php/doc/' + answer.data);
        });
    };
    
    //Botão para abrir o modal com a lista de chamadas
    $scope.verChamadas = function(telefone, lead){
        var obj = {};
        obj.lead = lead;
        obj.telefone = telefone;
        var modalInstance = $modal.open({
            templateUrl: 'modalListCalls.html',
            controller: 'modalInstanceListCalls',
            size: 'lg',
            resolve: {items: function () {
                    return obj;
                }
            }
        });
        modalInstance.result.then(function(){
           //getLeadAllInfo();
        });
    }
   });
   
   /**
 * Modal instance para ver o Detalhe da Lead
 */
angular.module('appMain').controller('modalInstanceDetLead_2', function($scope,$http,$modalInstance,items,$modal){
    
   $scope.lead = items;
    $scope.tipoUser = JSON.parse(sessionStorage.userData).tipo;
    $scope.editar = false;
    $scope.readOnly = true;
    
    getLeadAllInfo();
    
    
        //Definição dos tabs
    $scope.tabs = [{
            title: 'Cliente',
            id: 'zero.tpl'
        }, {            
            title: 'Credito Pretendido/Rendimentos/Despesas',
            id: 'one.tpl'
        }, {            
            title: 'Documentos',
            id: 'two.tpl'            
        }, {            
            title: 'Registo de Contactos',
            id: 'three.tpl',
        }, {            
            title: 'Financiamentos',
            id: 'four.tpl'
        }, {            
            title: 'Rejeições',
            id: 'five.tpl' 
        }, {            
            title: 'Notas do Analista',
            id: 'six.tpl'               
    }];
    /*
     * Controlo dos tabs e dos paineis
     */
    if(sessionStorage.currentTab != undefined){
        $scope.currentTab = sessionStorage.currentTab;
        var t={};
        t.id= $scope.currentTab;
        onClickTabFunc(t);
    } else {
        $scope.currentTab = 'zero.tpl';
       // sessionStorage.currentTab = 'zero.tpl';
    }
    
  //  loadReqByTab($scope.currentTab);
    //Função para navegar nas tabs da listagem de requisições
    $scope.onClickTab = function (tab) {
        onClickTabFunc(tab);
    };
    $scope.isActiveTab = function(tabId) {
       return tabId == $scope.currentTab;
    };
    

    //Ver documentação
    $scope.verDoc = function(doc){  //doc inclui o lead id
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewDoc',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function(){
            getLeadAllInfo();
        });        
    };

    //DOCUMENTAÇAO
    //Botão para descarregar um documento (fx)
    $scope.descarregarDoc = function(doc){
        $http({
        url:'php/getDocumentacao.php',
        method:'POST',
        data:JSON.stringify({'lead':doc.lead,'linha':doc.linha})
        }).then(function(answer){
            var doc =answer.data[0];        
            if(doc.tipo=='jpg'){
                download("data:image/jpeg;base64,"+doc.fx64,doc.nomefx);
            }
            if(doc.tipo=='png'){
               download("data:image/png;base64,"+doc.fx64,doc.nomefx);
            }
            if(doc.tipo=='pdf'){
               download("data:application/pdf;base64,"+doc.fx64,doc.nomefx);
            }
            if(doc.tipo=='docx'){
               download("data:application/docx;base64,"+doc.fx64,doc.nomefx);
            } 
        });
    };
    //Botão para descarregar todos os documentos para o ambiente de trabalho
    $scope.descarregarDocs = function(lead){
        $http({
        url:'php/getDocumentacao.php',
        method:'POST',
        data:JSON.stringify({'lead':lead})
        }).then(function(answer){
            answer.data.forEach(function(ln){
                if(ln.tipo=='jpg'){
                    download("data:image/jpeg;base64,"+ln.fx64,ln.nomefx);
                }
                if(ln.tipo=='png'){
                   download("data:image/png;base64,"+ln.fx64,ln.nomefx);
                }
                if(ln.tipo=='pdf'){
                   download("data:application/pdf;base64,"+ln.fx64,ln.nomefx);
                }
                if(ln.tipo=='docx'){
                   download("data:application/docx;base64,"+ln.fx64,ln.nomefx);
                }                    
            });
        });
    };    
    
    //Close Modal
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
    //FUNCTIONS
    function onClickTabFunc(tab){
        var x = document.getElementById(tab.id);
        var k = document.getElementsByClassName('pn');
        for(var i=0; i<k.length; i++){
            if(x !== k[i]){
                k[i].className = k[i].className.replace(" show", " hide");
            } else {
                k[i].className = k[i].className.replace(" hide", " show");
            }
        }
        $scope.currentTab = tab.id;
    } 
    
        //Obter todos os dados da LEAD/Processo
    function getLeadAllInfo(){
        $http({
            url:'php/getData.php',
            method:'POST',
            data:'cnf_sitfamiliar'
        }).then(function(answer){
            $scope.estadoscivis = answer.data;
        });
        
        $http({
            url:'php/getLeadAllInfo.php',
            method:'POST',
            data:JSON.stringify({"lead":$scope.lead,"user":JSON.parse(sessionStorage.userData)})
        }).then(function(answer){
            $scope.dl = answer.data.dlead;
            $scope.ic = answer.data.infoCliente;
            $scope.rendimentos = answer.data.rendimentos;
            $scope.creditos = answer.data.creditos;
            $scope.contactos = answer.data.contactos;
            $scope.docs =answer.data.docs;
            $scope.financiamentos =answer.data.financiamentos;
          $scope.rejeicoes='';
            (answer.data.rejeicoes).forEach(function(ln){
                $scope.rejeicoes += ln.data +' -->   ' + ln.motivo +';   ' + ln.obs + ';  ' + ln.outro + '\n';
            });
            
            if($scope.ic.parentesco2){
                $scope.segTitular = true;
            } else {
                $scope.segTitular = false;
            }
            if($scope.ic.mesmahabitacao=='Sim'){
                $scope.mesmaHabitacao = true;
            } else {
                $scope.mesmaHabitacao = false;
            }  
        });
    } 
    
});


