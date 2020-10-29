/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('gcreateLeadController',function($scope,$http,$modal){
    $scope.newLeadId = 0;
    $scope.c ={};
    $scope.c.segundoproponente=false;
    $scope.nomelead ='';
    $scope.anoatual = new Date().getFullYear();
      //Outros rendimentos
    var outroR = [];
    outroR.push({});
    $scope.outrosR = outroR;
    $scope.addLineOutrosRendimentos = function(){
        outroR.push({});
        $scope.outrosR = outroR;
    }; 
    $scope.removeLineOutrosRendimentos = function(){
        outroR.pop();
    };
     //Otros creditos    
    var outroC = [];
     outroC.push({});
     $scope.outrosC = outroC;     
    $scope.addLineOutrosCreditos = function(){
        outroC.push({});
        $scope.outrosC = outroC;
    };
    $scope.removeLineOutrosCreditos = function(){
        outroC.pop();
    }; 
    
    //get data for Selects
    //Tipo Contrato    
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_sitprofissional'
    }).then(function(answer){
        $scope.tipocontratos = answer.data;
    });
    //Estado Civil
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_sitfamiliar'
    }).then(function(answer){
        $scope.estadoscivis = answer.data;
    });
        //Tipo Habitação
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_tipohabitacao'
    }).then(function(answer){
        $scope.tiposhabitacao = answer.data;
    });
    
    //Button to open modal to finalize contact and ask for documentation
    $scope.finalize = function(){
        if(checkFields()){
            if($scope.grauparent=='Conjugue'){
                $scope.c.parentesco2='Conjugue';
            }
            //modal to select and ask for documentation
            var modalInstance = $modal.open({
                templateUrl: 'modalFinalizeNew.html',
                controller: 'modalInstanceFinalizeNew',
                size: 'lg',
                resolve: {items: function () {
                        return $scope.c;
                    }
                }
            });
        }
    };
    
    //Button to Attach documentation and send to analise
    $scope.attachDocs = function(){
        if(checkFields()){
            //se não tiver erros grava os dados do formulario
            if($scope.grauparent=='Conjugue'){
                $scope.c.parentesco2='Conjugue';
            }
            
            if(!$scope.newLeadId){
            $http({
                url:'php/gestor/saveFormData.php',
                method:'POST',
                data:JSON.stringify({'lead':$scope.c,'user':JSON.parse(sessionStorage.userData)})
            }).then(function(answer){
                //se bem sucedido recebe o id da LEAD criada
               //open modal to attach documentation
               $scope.newLeadId = answer.data;
               if(answer.data){
                var modalInstance = $modal.open({
                    templateUrl: 'modalAttachDocsD.html',
                    controller: 'modalInstanceAttachDocsD',
                    size: 'lg',
                    resolve: {items: function () {
                            return answer.data; //numero da lead criada
                        }
                    }
                });
                modalInstance.result.then(function(data){
                    window.location.replace('#!/dashboard');
                });
                }
            });
        } else {
                var modalInstance = $modal.open({
                    templateUrl: 'modalAttachDocsD.html',
                    controller: 'modalInstanceAttachDocsD',
                    size: 'lg',
                    resolve: {items: function () {
                            return $scope.newLeadId //numero da lead criada
                        }
                    }
                });
                modalInstance.result.then(function(data){
                    window.location.replace('#!/dashboard');
                });            
        }
        }
    };
    
//Functions
    function checkFields(){
        var erro=false;
        if(!$scope.c.nomelead){
          alert("Tem de preencher o nome da lead / Origem!");
          erro=true;
        }
        if(!$scope.c.nome){
          alert("Tem de preencher o nome!");
          erro=true;
        }
        if(!$scope.c.idade && !erro){
          alert("Tem de preencher a idade!");
          erro=true;
        }        
        if(!$scope.c.telefone && !erro){
          alert("Tem de preencher o telefone!");
          erro=true;
        }  
        if(!$scope.c.nif && !erro){
          alert("Tem de preencher o NIF!");
          erro=true;
        }  
        if(!$scope.c.email && !erro){
          alert("Tem de preencher o email!");
          erro=true;
        }
        if(!$scope.c.profissao && !erro){
          alert("Tem de preencher a profissão!");
          erro=true;
        }
        if(!$scope.c.tipocontrato && !erro){
          alert("Tem selecionar o Tipo de Contrato!");
          erro=true;
        }          
        if(!$scope.c.vencimento && !erro){
          alert("Tem de indicar o Vencimento!");
          erro=true;
        }
        if(!$scope.c.anoinicio && !erro){
          alert("Tem de indicar o Ano de Inicio!");
          erro=true;
        }
        if(!$scope.c.estadocivil && !erro){
          alert("Tem de indicar o estado civil!");
          erro=true;
        }        
        if(!$scope.c.irs && !erro){
          alert("Tem de selecionar o IRS!");
          erro=true;
        }
        if($scope.c.segundoproponente){
            if(!$scope.grauparent){
              alert("Tem de selecionar o grau de parentesco!");
              erro=true;              
            }
            if($scope.grauparent=='Outro' && !$scope.c.parentesco2){
              alert("Tem de indicar o grau de parentesco!");
              erro=true;            
            }
            if(!$scope.c.idade2 && !erro){
              alert("Tem de indicar a idade do segundo titular!");
              erro=true;
            }             
            if(!$scope.c.profissao2 && !erro){
              alert("Tem de indicar a profissão do 2º titular!");
              erro=true;
            }             
            if(!$scope.c.tipocontrato2 && !erro){
              alert("Tem de selecionar o tipo de contrato do 2º titular!");
              erro=true;
            } 
            if(!$scope.c.vencimento2 && !erro){
              alert("Tem de indicar o vencimento do 2º titular!");
              erro=true;
            } 
            if(!$scope.c.anoinicio2 && !erro){
              alert("Tem de indicar o ano de inicio de atividade do 2º titular!");
              erro=true;
            } 
            if(!$scope.c.mesmahabitacao && !erro){
              alert("Tem de selecionar a habitação do 2º titular!");
              erro=true;
            }              
        }
        
        if(!$scope.c.tipohabitacao && !erro){
          alert("Tem de selecionar o tipo de habitação!");
          erro=true;
        } 
        if(!$scope.c.anoiniciohabitacao && !erro){
          alert("Tem de indicar o ano de inicio na habitação!");
          erro=true;
        }         
        if($scope.c.tipohabitacao && $scope.c.tipohabitacao.nome=='Alugada' && !erro){
            if(!($scope.c.valorhabitacao>0) ){
                alert("Tem de indicar o valor da renda!");
                erro=true;
            }
        }
        if($scope.c.tipohabitacao && $scope.c.tipohabitacao.nome=='Propria com CH'  && !erro){
            if(!($scope.c.valorhabitacao>0)){
                alert("Tem de indicar o valor da prestação!");
                erro=true;
            }
        }        
        if(!$scope.c.montante && !erro){
          alert("Tem de indicar o Valor pretendido!");
          erro=true;
        }  

        if(!$scope.c.tipocredito && !erro){
          alert("Tem de selecionar o Tipo de Crédito!");
          erro=true;
        }
        if($scope.c.tipocredito!='CT' && !$scope.c.prazopretendido && !erro){
          alert("Tem de indicar o Prazo!");
          erro=true;
        }
//        if($scope.c.tipocredito!='CT' && !$scope.c.valorprestacao && !erro){
//          alert("Tem de indicar o valor da Prestação!");
//          erro=true;
//        }         
        if($scope.c.tipocredito!='CT' && !$scope.c.finalidade && !erro){
          alert("Tem de indicar a Finalidade do crédito!");
          erro=true;
        }

        return !erro;
    }
    
});

/**
 * Modal instance to select required documents, how to send and ETA 
 */
angular.module('appGest').controller('modalInstanceFinalizeNew', function($scope,$http,$modal,$modalInstance,items){
    $scope.m = {};$scope.e={}; $scope.d = {};
    $scope.m.email = items.email;
    var date = new Date();
    var mes = '00';
    if(date.getMonth()+1<10){
        mes = '0'+(date.getMonth()+1);
    } else{
        mes = date.getMonth()+1;
    }
    $scope.minDate = date.getFullYear()+'-'+mes+'-'+date.getDate();
    if($scope.m.email){
        $scope.e.tipoenvio = 'email';
    } 

    //Get Documentation
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_docnecessaria'
    }).then(function(answer){
        $scope.docs = answer.data;
    });
  
    
    $scope.saveProcess2 = function(d,m,e){
        //Validar os dados do formulario do modal
        //Documentação - pelo menos um documento
    //    alert(m.dataExpectavel);
        if(d.docs==undefined || d.docs.length==0){
            alert('Tem de selecionar pelo menos um documento!');
        } else {
     
            //Gravar os dados do formulario
            var parm = {};
            parm.process = items;
            parm.address = m;
            parm.docs = d;
            parm.tipoEnv = e.tipoenvio;
            parm.user = JSON.parse(sessionStorage.userData);
            $http({
                url:'php/gestor/saveNewLeadProcess.php',
                method:'POST',
                data:JSON.stringify(parm)
            }).then(function(answer){
             //   alert(answer.data);
               $modalInstance.dismiss('Cancel');
              //  window.location.replace("#!/dashboard");
            });
            window.location.replace("#!/dashboard");
        }
    };
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
 
    
});

/**
 * Modal instance to attach documentation
 */
angular.module('appGest').controller('modalInstanceAttachDocsD', function($scope,$http,$modal,$modalInstance,items){
     $scope.d = {};
     $scope.da ={};
     $scope.lead = items;
     getInf();
 //Anexar documento
    $scope.anexarDoc = function(d){
        //open modal to attach documentation
        var parm={};
        parm.lead= $scope.lead;
        parm.doc = d;
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocD.html',
            controller: 'modalInstanceAnexarDocD',
            size: 'lg',
            resolve: {items: function () {
                    return parm;
                }
            }
        });
        modalInstance.result.then(function(){
            getInf();
        });
    };
    //Anexar Doc Extra
    $scope.anexarDocExtra = function(){
     
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocExtraD.html',
            controller: 'modalInstanceAnexarDocExtraD',
            size: 'lg',
            resolve: {items: function () {
                    return $scope.lead;
                }
            }
        });
        modalInstance.result.then(function(){
            getInf();
        });        
    }; 
    //Remover Documento
    $scope.removerDoc = function(doc){
        if(confirm('Vai APAGAR este documento! Pretende Continuar?')){
            $http({
                url:'php/removerDoc.php',
                method:'POST',
                data:JSON.stringify({'doc':doc,'lead':$scope.lead,'op':'Delete'})
            }).then(function(answer){
               getInf();
            });
        }
    };
    //Cancelar Pedido de documento
    $scope.cancelarPedidoDoc = function(doc,lead){
        $http({
            url:'php/removerDoc.php',
            method:'POST',
            data:JSON.stringify({'doc':doc,'lead':$scope.lead,'op':'Cancel'})
        }).then(function(answer){
            getInf();
        });       
    };    
    $scope.files = [];
    
    //Apenas quando faz a anexação dos documentos no momento
    $scope.sendToAnalise = function(){
        //Verificar que tem documentação e caso afirmativo muda o status da lead para 10
        getInf();
        var rec =0;
        var ped = 0;
        var docs= $scope.docs;
        docs.forEach(function(d){
            rec += +d.recebido;
            ped++;
        });
        if(rec == ped){
            $http({
                url:'php/gestor/changeLeadStatus.php',
                method:'POST',
                data:JSON.stringify({'lead':$scope.lead,'status':10,'user':JSON.parse(sessionStorage.userData)})
            }).then(function(answer){
                $modalInstance.close('ok');
            });            
        } else {
            alert("Não é permitido enviar para analise sem documentação!!");
        }
    
    };
    
    //Por a aguardar documentação e ir para a dashboard
    $scope.aguardarDoc = function(lead){
        
        $http({
            url:'php/gestor/changeLeadStatus.php',
            method:'POST',
            data:JSON.stringify({'lead':lead,'status':8,'user':JSON.parse(sessionStorage.userData)})
        }).then(function(answer){
            $modalInstance.close('ok');
        });
    };
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    }; 
    
    
    //functions
    function getInf(){
        //Get Documentation
        $http({
            url:'php/gestor/getDocPedidos.php',
            method:'POST',
            data:items
        }).then(function(answer){
            $scope.docs = answer.data;
        });
    }
    
});


/**
 * Modal instance to attach documents and send to analise. 
 */
angular.module('appGest').controller('modalInstanceAnexarDocD', function($scope,$http,$modalInstance,items){
    $scope.lead = items.lead;
    $scope.doc = items.doc;
    $scope.da ={};
    $scope.file = {};
    $scope.novonome='';
    
    
    //Apenas quando faz a anexação dos documentos no momento
    $scope.saveAttachedDoc = function(){
        //guardar o ficheiro na arq_documentação e alterar o cad_docpedida 
        if($scope.file){
            if($scope.file.filesize<4000000){
                //Gravar os dados do formulario
                var parm = {};
                parm.lead = items.lead;
                parm.docAnx = $scope.doc;
                parm.userId = sessionStorage.userId;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url:'php/saveAttachDocument.php',
                    method:'POST',
                    data:JSON.stringify(parm)
                }).then(function(answer){
                    if(answer.data!='OK'){
                        alert(answer.data);
                    } else {
                        $modalInstance.close();
                    }
                }); 
            } else{
                alert("Ficheiro é demasiado grande: "+$scope.file.filesize/1000000+"MB");
            }
        }
    };
    
    
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
});

/**
 * Modal instance to attach documents
 */
angular.module('appGest').controller('modalInstanceAnexarDocExtraD', function($scope,$http,$modalInstance,items){
    $scope.lead = items;
    $scope.novonome ="";
    $scope.d = {};
    //obter tipos de documentos
    $http({
        url:"php/getData.php",
        method:"POST",
        data:'cnf_docnecessaria'
    }).then(function(answer){
        $scope.docs = answer.data;
    });
    //Atualizar o novoNome
    $scope.upNovoNome = function(d){
        var novonome='';
        for(var i=0;i<d.docs.length;i++){
            novonome +=d.docs[i].sigla+'_'; 
        }
        $scope.novonome = novonome;
    };
    
    //Guardar o ficheiro extra
    $scope.saveAttachedDocExtra = function(){
       
      if($scope.file){
          if($scope.file.filesize<4000000){
                //Gravar os dados do formulario
                var parm = {};
                parm.lead = $scope.lead;
                parm.docAnx = $scope.d.docs;
                parm.userId = sessionStorage.userId;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url:'php/saveAttachDocumentExtra.php',
                    method:'POST',
                    data:JSON.stringify(parm)
                }).then(function(answer){
                    if(answer.data!='OK'){
                        alert(answer.data);
                    } else {
                        $modalInstance.close();
                    }
                }); 
            } else {
                alert("Ficheiro é demasiado grande: "+$scope.file.filesize/1000000+"MB");            
            }
      }  
    };
    
    
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  
    
});