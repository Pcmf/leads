/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('relParceirosController', function ($scope, $http, $modal) {
    $scope.tml = {};
    $scope.u = null;
    var data11 = null;
    var data22 = null;
    $scope.tml.opc = 'mes';
    
    loadDados('mes', null, null);

    //Limpar dados
    $scope.clearFilter = function () {
        $scope.tml = {};
    };

    $scope.cleanOpc = function () {
        $scope.tml.opc = '';
    }
    $scope.clearDatas = function () {
        $scope.tml.data1 = '';
        $scope.tml.data2 = '';
    }

    $scope.applyFilter = function (sl) {
        if (sl.data1) {
            var dia = sl.data1.getDate();
            var mes = sl.data1.getMonth() + 1;
            var ano = sl.data1.getFullYear();
            data11 = (ano + '-' + mes + '-' + dia).toLocaleString();
        } else {
            data11 = null;
        }
        if (sl.data2) {
            var dia = sl.data2.getDate();
            var mes = sl.data2.getMonth() + 1;
            var ano = sl.data2.getFullYear();
            data22 = (ano + '-' + mes + '-' + dia).toLocaleString();
        } else {
            data22 = null;
        }
        loadDados(sl.opc, data11, data22, $scope.u);
    };
    
    // Ver detalhes
    $scope.showDet = function(status, parceiro, tipocredito){
        var obj = {'tipocredito': tipocredito,  'opc': $scope.tml.opc, 'data1':data11, 'data2':data22, 'parceiro': parceiro, 'status': status};
        var modalInstance = $modal.open({
            templateUrl: 'modalDetList_p.html',
            controller: 'modalInstanceDetList_p',
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
    
    
    function loadDados(opc, data1, data2, user){
        
        $http({
            url: 'relatorios/php/getRelatorioParceiros.php',
            method: 'POST',
            data: JSON.stringify({'opc': opc, 'data1': data1, 'data2': data2, 'user':user})
        }).then(function (answer) {
            $scope.dados = answer.data[0];
            $scope.totais = answer.data[1];
        }); 
    }
    
    

});

/**
 * Modal instance para ver o Detalhe da Lead
 */
angular.module('appMain').controller('modalInstanceDetList_p', function($scope,$http,$modalInstance,items,$modal){
    
        
      
      $http({
          url:'relatorios/php/getDetListParceiros.php',
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
            templateUrl: 'modalDetLead_p.html',
            controller: 'modalInstanceDetLead_p',
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
angular.module('appMain').controller('modalInstanceDetLead_p', function($scope,$http,$modalInstance,items,$modal){
    
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

   /**
 * Modal instance para ver a Lista de chamadas
 */
angular.module('appMain').controller('modalInstanceListCalls', function($scope,$http,$modalInstance,items,$modal){
    
   $scope.lead = items.lead;
   $scope.telefone = items.telefone;
   
   $http({
       url:'relatorios/php/getListCalls.php',
       method:'POST',
       data: items.telefone
   }).then(function(answer){
       $scope.list = answer.data;
   });
   
   
   
       //Close Modal
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
   
});