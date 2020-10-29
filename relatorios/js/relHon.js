/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('relHonController',function($scope,$http,NgTableParams,$modal){
    $scope.sl = {};
    $scope.sl.parceiroSel = -1;
    $scope.sl.fornSel = -1;
    $scope.sl.tipocredito= -1;
    $scope.sl.colabSel = null;
   //Fornecedores de leads
    $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_fornecedorleads'
    }).then(function(answer){
        $scope.fornecedores = answer.data;
    });
    //Colaboradores
    $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_utilizadores'
    }).then(function(answer){
        $scope.colaboradores = answer.data;
    });
    //Parceiros    
    $http({
            url:'php/getData.php',
            method:'POST',
            data:'cad_parceiros'
    }).then(function(answer){
        $scope.parceiros = answer.data;
    });
    
    //Limpar dados
    $scope.limparDados = function(){
        $scope.sl = {};
    };
    
        //Ordenação por campo
  //  $scope.predicate = 'id';
    $scope.sort = function (predicate) {
        $scope.predicate = predicate;
    };
    $scope.isSorted = function (predicate) {
        return ($scope.predicate == predicate);
    };  
    
    //Botão para abrir o modal com o detalhe da lead
    $scope.verDetLead = function(lead){
        var modalInstance = $modal.open({
            templateUrl: 'modalDetLead.html',
            controller: 'modalInstanceDetLead',
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
              url:'relatorios/php/criarRelatorioHon.php',
              method:'POST',
              data:JSON.stringify({'sl':sl})
          }).then(function(answer){
             //   console.log(answer.data);
                $scope.fornId = sl.fornSel.id;
              //  $scope.resultados = answer.data.resultados;
                $scope.totalaprovado = answer.data.totalaprovado;
                $scope.totalfinanciado = answer.data.totalfinanciado;
                $scope.totaldesistiu = answer.data.totaldesistiu;
                $scope.totalanulado = answer.data.totalanulado;
                $scope.numAprovado = answer.data.numAprovado;
                $scope.numFinanciado = answer.data.numFinanciado;
                $scope.numDesistiu = answer.data.numDesistiu;
                $scope.numAnulado = answer.data.numAnulado;
                $scope.totalhonorarios = answer.data.totalhonorarios;
                $scope.entradas = answer.data.entradas;
                
                $scope.totalAprov = answer.data.aprovado;
                $scope.totalSubmetido = answer.data.submetidos;
                var data = answer.data.resultados;
                    $scope.paramsTable = new NgTableParams({
                   },{
                       dataset:data
                   }); 
                
          });
        
    }
    
});


/**
 * Modal instance para ver o Detalhe da Lead
 */
angular.module('appMain').controller('modalInstanceDetLead', function($scope,$http,$modalInstance,items,$modal){
    
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
            
        });
    } 
    
});

/**
 * Modal instance to view document. 
 */
angular.module('appMain').controller('modalInstanceViewDoc', function($scope,$http,$modalInstance,items,$timeout,$rootScope, $sce){
    $scope.nomedoc = items.nomedoc;
   
    //Obter o base64 para a lead e linha que está no doc
      $rootScope.prograssing = true;  
      $http({
          url:'php/getDocBase64.php',
          method:'POST',
          data:JSON.stringify(items)
      }).then(function(answer){
          $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
          $rootScope.prograssing = false;
      });
     
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  

  
});