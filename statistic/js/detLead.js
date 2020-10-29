/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appMain').controller('detLeadController',function($scope,$http,$routeParams,$modal){
    $scope.lead = $routeParams.id;
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
        }, {
            title: 'Comprovativos',
            id:  'seven.tpl'
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
    
    
    //Botão para descarregar o comprovativo para o ambiente de trabalho
    $scope.descarregarComprovativo = function(c){
            download("data:application/pdf;base64,"+c.documento,c.nomedoc);
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
           // $scope.rejeicoes = answer.data.rejeicoes;
            $scope.rejeicoes='';
            (answer.data.rejeicoes).forEach(function(ln){
                $scope.rejeicoes += ln.data +' -->   ' + ln.motivo +';   ' + ln.obs + ';  ' + ln.outro + '\n';
            });
        });
        getComprovativos($scope.lead);

    } 
    
    function getComprovativos(lead){
            $http({
                url:'php/analista/getComprovativosList.php',
                method:'POST',
                data:lead
            }).then(function(answer){
                $scope.comprovativos = answer.data;
            });        
        }
});


/**
 * Modal instance to view document. 
 */
angular.module('appMain').controller('modalInstanceViewDoc', function($scope,$http,$modalInstance,items,$timeout,$rootScope){
    $scope.nomedoc = items.nomedoc;
    $scope.currPage = 0;
   
    //Obter o base64 para a lead e linha que está no doc
    getBase64(items,1);
     
    //Mudar pagina
    $scope.changePage = function(p){
        $rootScope.prograssing = true;
        $scope.currPage = p;
        getBase64(items,p);
        $rootScope.prograssing = false;

    };
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
  /* 
   * FUNCTIONS 
   */
  function getBase64(doc,pg){
        //Obter o fxbase64
      $http({
          url:'php/getDocBase64.php',
          method:'POST',
          data:JSON.stringify(doc)
      }).then(function(answer){
          //drawBase64(answer.data.tipo,answer.data.fx64,pg);
          $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
      });
  };

    function drawBase64(fxtype,fxbase64,pg){
      
      if(fxtype=='pdf'){
                 var bin_data =  atob(fxbase64);
              // Fetch the PDF document from the URL using promises.
                  PDFJS.getDocument({data: bin_data}).then(function (pdf) {
                      // Fetch the page.
                      var pga =[];
                      for(k=1;k<= pdf.numPages;k++){
                          pga.push(k);
                      }
                      $scope.pages =pga;
                      
                      $timeout(function(){
                      pdf.getPage(pg).then(function (page) {
                          var scale = 1.3;
                          var viewport = page.getViewport(scale);

                          // Prepare canvas using PDF page dimensions.
                          var canvas = document.getElementById('previewDocCanvas');
                          var context = canvas.getContext('2d');
                          canvas.height = viewport.height;
                          canvas.width = viewport.width;

                          // Render PDF page into canvas context.
                          var renderContext = {
                              canvasContext: context,
                              viewport: viewport
                          };
                          page.render(renderContext);
                      });
                    },500);
                  });
              }

            if(fxtype==='jpg' || fxtype==='png'){
                var canvas = document.getElementById('previewDocCanvas');
                  var ctx = canvas.getContext("2d");
                  var img = new Image();
                  img.onload = function() {
                      drawImageScaled(img,ctx);
                  };
                  img.src = "data:image/"+fxtype+";base64,"+fxbase64;
            }
            //Limpar Canvas
            if(fxtype=='clear'){
                var canvas = document.getElementById('previewDocCanvas');
                var context = canvas.getContext("2d");
                context.clearRect(0,0,800,1200);
            }
        

  };
  
  
  function drawImageScaled(img, ctx) {
   var canvas = ctx.canvas ;
   var hRatio = canvas.width  / img.width    ;
   var vRatio =  canvas.height / img.height  ;
   var ratio  = Math.min ( hRatio, vRatio );
   var centerShift_x = ( canvas.width - img.width*ratio ) / 2;
   var centerShift_y = ( canvas.height - img.height*ratio ) / 2;  
   ctx.clearRect(0,0,canvas.width, canvas.height);
   ctx.drawImage(img, 0,0, img.width, img.height,
   centerShift_x,centerShift_y,img.width*ratio, img.height*ratio);  
}
});




