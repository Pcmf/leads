angular.module('appMain').controller('detalheController',function($scope,$http, $modal){
        //LEADS
    $scope.filtros={};
    $scope.dados = [];
    $scope.codigosfiltros = {};
    
    $http({
        url:'php/getData.php',
        method: 'POST',
        data: 'cad_filtros'
    }).then(function(answer){
        $scope.codigosfiltros = answer.data;
    });
    
    $http({
        url:'AUDIT/php/getFornecedores.php'
    }).then(function(answer){
        $scope.fornecedores = answer.data;
    });
    
    
    $scope.aplicarFiltro = function(filtros){
        console.table(filtros);
        $http({
            url:'AUDIT/php/detalhe.php',
            method:'POST',
            data: JSON.stringify(filtros)
    }).then(function(answer){

        $scope.obj = answer.data;
        
        $scope.dados = Object.keys($scope.obj).map(it => $scope.obj[it]);
    });
    };
    
    //Botão para abrir o modal com o detalhe da lead
    $scope.verDetLead = function(lead){
        var modalInstance = $modal.open({
            templateUrl: 'modalDetLead_4.html',
            controller: 'modalInstanceDetLead_4',
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

});

/**
 * Modal instance para ver o Detalhe da Lead
 */
angular.module('appMain').controller('modalInstanceDetLead_4', function($scope,$http,$modalInstance,items,$modal){
    
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


