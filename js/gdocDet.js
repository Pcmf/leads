/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('gdocDetController',function($scope,$http,$routeParams,$timeout,$modal){
    $scope.c={};
    $scope.od ={};
    $scope.enableCk = false;
    
    
    var docData;
    var docType;

 //get data fro DB
 //Process ID or LEAD
  $scope.lead = $routeParams.id;  
  getLists($scope.lead);
  //Documeto recebido selecionado - global e temporario
  var docSel ={};
  var lastIndex = 0;
  
  //Show on Canvas the selected document 
  $scope.showDoc = function(doc){
  // console.log(this);
        if(document.getElementById(this.$index).style.cssText==''){
            document.getElementById(lastIndex).style ='';
            lastIndex=this.$index;
            document.getElementById(this.$index).style ="background-color:green; color:white; text-decoration:none";
            docSel = doc;
            $scope.enableCk = true;
            getBase64(doc,1);
        } else {
            document.getElementById(this.$index).style ="";
            docSel = {};
            $scope.c ={};
            $scope.enableCk = false;
            drawBase64('clear','');
            $scope.pages = [];
        }
     };
     
     $scope.changePage = function(p){
        getBase64(docSel,p);
     };
    
    //Button to Validar Checkbox dos documentos pedidos
    $scope.confirmDoc = function(checks){
        if(checks){
           var param ={};
           param.chk = checks;
           param.doc = docSel;
           param.lead = $scope.lead;
           $http({
               url:'php/gAtulizaDocRec.php',
               method:'POST',
               data:JSON.stringify(param)
           }).then(function(answer){
               $scope.c ={};  //remover checks
               $scope.od = {};
               $scope.enableCk = false;
               getLists($scope.lead);
               drawBase64('clear','');
               $scope.pages = [];
           });
        }

    };
    //Button to confirm extra doc
    $scope.confirmDocExt = function(od){
        if(od){
           var param ={};
           param.docExtra = od.doc;
           param.doc = docSel;
           param.lead = $scope.lead;
           $http({
               url:'php/gAtulizaDocRecExtra.php',
               method:'POST',
               data:JSON.stringify(param)
           }).then(function(answer){
               $scope.c ={};  //remover checks
               $scope.od = {};
               $scope.enableCk = false;
               getLists($scope.lead);
               drawBase64('clear','');
               $scope.pages = [];
           });              
        }        
    }
  
    //Send to Analise with full documentation or incomplete
    $scope.sendToAnalise = function(lead,sts){
        //before try to update LEAD status to 10 checks if all required documentation was checked
        $http({
            url:'php/gestor/sendToAnalise.php',
            method:'POST',
            data:JSON.stringify({'lead':lead,'gestor': sessionStorage.userData})
        }).then(function(answer){
            if(answer.data!=''){
                alert(answer.data);
            }
            window.location.replace('#!/dashboard');
        });
    };
    
    //Delete an document from the received 
    $scope.deleteDoc = function(doc){
        $http({
            url:'php/gestor/gDeleteDoc.php',
            method:'POST',
            data:JSON.stringify(doc)
        }).then(function(){
            getLists($scope.lead);
        });
    };
  
    //Send a new request of documentation
    $scope.sendNewPedido = function(lead){
        //tem de abrir um modal com uma check list
        var modalInstance = $modal.open({
            templateUrl: 'modalAskForDocs.html',
            controller: 'modalInstanceAskForDocs',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        

        
    };
    //Rejeitar LEAD
    $scope.rejeitarLead = function(lead){
        //Agendamento and go to dashboard
            var modalInstance = $modal.open({
                templateUrl: 'modalRejeitar.html',
                controller: 'modalInstanceRejeitar',
                size: 'lg',
                resolve: {items: function () {
                        return lead;
                    }
                }
            });          
    };
  
  
  /* 
   * FUNCTIONS 
   */
  function getBase64(doc,pg){
              //Obter o fxbase64
            $http({
                url:'php/getBase64.php',
                method:'POST',
                data:JSON.stringify(doc)
            }).then(function(answer){
                docType = doc.type;
                docData = answer.data;
                drawBase64(doc.fxtype,answer.data,pg);
            });
        };
  
  function getLists(lead){
        $http({
            url:'php/gestor/getDocsByLead.php',
            method:'POST',
            data:lead
        }).then(function(answer){

            $scope.docsPed = answer.data.docsPed;
            $scope.docsRec = answer.data.docsRec;
            $scope.docsConf = answer.data.docsConf;

        });
  }
  
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
                          var canvas = document.getElementById('previewCanvas');
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
                var canvas = document.getElementById('previewCanvas');
                  var ctx = canvas.getContext("2d");
                  var img = new Image();
                  img.onload = function() {
                      drawImageScaled(img,ctx);
                  };
                  img.src = "data:image/"+fxtype+";base64,"+fxbase64;
            }
            //Limpar Canvas
            if(fxtype=='clear'){
                var canvas = document.getElementById('previewCanvas');
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

/**
 * Modal instance to register Rejection
 */
angular.module('appGest').controller('modalInstanceRejeitar', function($scope,$http,$modalInstance,items){
    $scope.m ={};
    $scope.rejeitar = function(){
      if(!$scope.r){
          alert("Tem de selecionar um motivo ou descrever!");
      } else {
                
                var param = {};
                param.user = JSON.parse(sessionStorage.userData);
                param.lead = items;
                param.motivo = $scope.r;
                $http({
                    url:'php/registarRejeicao.php',
                    method:'POST',
                    data:JSON.stringify(param)
                }).then(function(answer){
                    window.location.replace("");
                });
            }
    };
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to register Rejection
 */
angular.module('appGest').controller('modalInstanceAskForDocs', function($scope,$http,$modalInstance,items){
        //Obter check list documentação necessária
        $http({
            url:'php/getData.php',
            method:'POST',
            data:'cnf_docnecessaria'
        }).then(function(answer){
            $scope.docs = answer.data;
        });
        $scope.d ={};
        $scope.outroTipoDoc ='';
        
        $scope.pedirDoc = function(){
            var param = {};
            param.gestor = JSON.parse(sessionStorage.userData);
            param.docs = $scope.d.docs;
            param.otd = $scope.outroTipoDoc; 
            param.lead = items;  

            $http({
                url:'php/gestor/sendEmailAskAgainDocs.php',
                method:'POST',
                data:JSON.stringify(param)
            }).then(function(answer){
                alert(answer.data);
                $modalInstance.dismiss('Cancel');
                window.location.replace("#!/docs/3");
            });
            
        };
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});