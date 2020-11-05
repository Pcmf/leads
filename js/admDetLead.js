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
    $scope.onCall = false;
    $scope.comunicacoes = [];
    $scope.show = false;
    $scope.e = {};
    $scope.s = {};
    $scope.addNewOR = false;
    $scope.addNewOC = false;
    $scope.temHistorico = false;
    $scope.titular = "primeiro";
    $scope.firstT ="active";
    $scope.secondT = "";    
    //INICIAR
    getLeadAllInfo();
    
        //Button para fazer a chamada
    $scope.makeCall = function (lead) {
        if (!$scope.onCall) {
            $scope.onCall = !$scope.onCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": $scope.ic.telefone, "lead": lead})
            }).then(function (answer) {
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
            });
        } else {
            $scope.onCall = !$scope.onCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
            });
        }
    };
    
        //Button when no answer
    $scope.noAnswer = function (lead) {
        //Confirm this click
        $scope.onCall = false;
            var param = {};
            param.lead = lead;
            param.user = JSON.parse(sessionStorage.userData);
            $http({
                url: 'php/gestor/detLeadNaoAtende.php',
                method: 'POST',
                data: JSON.stringify(param)
            }).then(function (answer) {
                console.log(answer.data);
                $http({
                    url: 'restful/makeCall.php',
                    method: 'POST',
                    data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
                }).then(function (answer) {
                    if(answer.data.results[0].error){
                        alert(answer.data.results[0].error);
                    }
                    user = JSON.parse(sessionStorage.userData);
                   var sms = "Para podermos efetuar uma analise ao seu pedido de credito, indique-nos qual a melhor hora para contacto ou ligue-nos diretamente. "+ user.nome +" GESTLIFES";
                    $http({
                            url: 'php/sendSMS.php',
                            method:'POST',
                            data: JSON.stringify({"user": user.id, "telefone": $scope.ic.telefone, "lead": lead, 'sms':sms})
                    }).then(function(answer){
                        console.log(answer.data);
                    });
                });                
            });

    };
    
        //Ver documentação
    $scope.verDoc = function(doc){  //doc inclui o lead id
        //open modal to view documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewDoc_adm',
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

    
   
        //Definição dos tabs
    $scope.tabs = [{
            title: 'Cliente',
            id: 'zero.tpl'
        }, {            
            title: 'Informação Financeira',
            id: 'one.tpl'
        }, {               
            title: 'Processo',
            id: 'ten.tpl'
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
        }, {
            title: 'Cartões',
            id:  'eight.tpl' 
        }, {
            title: 'Comunicações',
            id:  'nine.tpl'
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
    
     //Função para navegar nas tabs da listagem
    $scope.onClickTab = function (tab) {
        onClickTabFunc(tab);
    };
    $scope.isActiveTab = function(tabId) {
       return tabId == $scope.currentTab;
    };
    
     //Botão para descarregar o comprovativo para o ambiente de trabalho
    $scope.descarregarComprovativo = function(c){
                if(c.tipodoc=='jpg'){
                    download("data:image/jpeg;base64," + c.documento, c.nomedoc);
                } else  {
                    download("data:application/pdf;base64," + c.documento, c.nomedoc);
                }
    }; 
    //Ver Comprovativo
    $scope.verComprovativo = function(doc){  
        var modalInstance = $modal.open({
            templateUrl: 'modalViewComp1.html',
            controller: 'modalInstanceViewComp1',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function(){
           // getLeadAllInfo();
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
                download("data:image/jpg;base64,"+doc.fx64,doc.nomefx);
            }
            if(doc.tipo=='jpeg'){
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
                    download("data:image/jpg;base64,"+ln.fx64,ln.nomefx);
                }
                if(ln.tipo=='jpeg'){
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
 
    //Botão para descarregar o CONTRATO para o ambiente de trabalho
    $scope.descarregarContrato = function(c){
            download("data:application/pdf;base64,"+c.fx64,c.nome);
    }; 
    //Ver Contrato
    $scope.verContrato = function(doc){  
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewContratoG',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function(){
           // getLeadAllInfo();
        });        
    };
    

    //Enviar para a Analise
    $scope.confirmOrReanalise = function(lead, userId, status){
        //Send to Analise with full documentation or incomplete
        if(confirm("Atenção! Pretende continuar?")){
            $http({
                url:'php/admin/sendToReAnalise.php',
                method:'POST',
                data:JSON.stringify({'lead':lead, 'analista': userId, 'status':status, 'user': sessionStorage.userId})
            }).then(function(answer){
                window.location.replace('#!/audit');
            });
        }
    };

    //Comunicações enviar email
    $scope.enviarComunicacao = function(e){
        if(e.assunto && e.texto){
            $http({
                url:'php/sendComunicacao.php',
                method:'POST',
                data:JSON.stringify({'lead': $scope.lead, 'e':e, 'tipo': 'G'})
            }).then(function(answer){
                alert(answer.data.msg);
                if(answer.data.msg="Enviado"){
                    $scope.e ={};
                    $scope.comunicacoes = answer.data.comunicacoes;   
                }
            });
        } else {
            alert("Atenção! Tem de preencher o assunto e o texto do email.");
        }
    }
    
    //Button to open modal to view Historico
    $scope.showHistorico = function (lead) {
        //Validate fields
        var obj = {};
        obj.leads = $scope.listaHistorico;
        obj.lead= $scope.lead;
        var modalInstance = $modal.open({
            templateUrl: 'modalHistorico.html',
            controller: 'modalInstanceHistorico',
            size: 'lg',
            resolve: {items: function () {
                    return obj;
                }
            }
        });
    };
    
    
        
    // Abrir modal com lista de simulações guardadas
    $scope.getSimulacoes = function(lead) {
        var modalInstance = $modal.open({
            templateUrl: 'modalGetSimulaDet.html',
            controller: 'modalInstanceGetSimulaDet',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function (answer) {
             console.table(answer);
             $scope.s = {};
             $scope.ic.vencimento = answer.vencimento;
             $scope.ic.vencimento2 = answer.vencimento2;
             $scope.ic.venc_cetelem = answer.venc_cetelem;
             $scope.ic.venc_cetelem2 = answer.venc_cetelem2;
             $scope.ic.outrosrendimentos = answer.outrosrendimentos;
             $scope.ic.outroscreditos = answer.outroscreditos;
             $scope.ic.valorhabitacao = answer.valorhabitacao;
             $scope.ic.filhos = answer.filhos;    
             
             $scope.s.valorpretendido = answer.valorpretendido;
             $scope.s.prestacaopretendida = answer.prestacaopretendida;
             $scope.s.prazopretendido = answer.prazopretendido;
             $scope.s.tipocredito = answer.tipocredito;
             $scope.s.segundoproponente = answer.segundoproponente;
             $scope.checkParceiros();
        });
    }
    
    
    
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
        if($scope.lead){
            //Estados Civil
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cnf_sitfamiliar'
            }).then(function(answer){
                $scope.estadoscivis = answer.data;
            });
            //Tipos de documentos
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_tiposdoc'
            }).then(function (answer) {
                $scope.tiposdoc = answer.data;
            }); 
             //Relaçoes familiares
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_relacaofamiliar'
            }).then(function (answer) {
                $scope.relacoesfamiliares = answer.data;
            }); 
            //Nacionalidades
            $http.get('lib/nacionalidades.json').then(function(answer) {
                $scope.nacionalidades = answer.data;
            });
            //Tipos de contrato
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cnf_sitprofissional'
            }).then(function(answer){
                $scope.tiposcontrato = answer.data;
            });
            //Utilizadores
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cad_utilizadores'
            }).then(function(answer){
                $scope.utilizadores = answer.data;
            });  
            //Tipo Habitação
            $http({
                url:'php/getData.php',
                method:'POST',
                data:'cnf_tipohabitacao'
            }).then(function(answer){
                $scope.tiposhabitacao = answer.data;
            });        
            //Comunicações
            $http({
                url:'php/getComunicacoes.php',
                method:'POST',
                data:$scope.lead
            }).then(function(answer){
                $scope.comunicacoes = answer.data;
            });    
            //Obter as regras de financiamento dos parceiros
            $http({
                url: 'php/analista/getRegrasFinanciamento.php',
                method: 'GET'
            }).then(function (answer) {
                $scope.regras = answer.data;
            });          
            //Informações da LEAD
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
                $scope.contratos = answer.data.contratos; 
                $scope.calculos = answer.data.calculos;
                $scope.s = answer.data.simula;
                $scope.p = answer.data.processo; 
                $scope.listaHistorico = answer.data.historic;
                if($scope.listaHistorico.length>0){
                    $scope.temHistorico = true;
                }

                 //Obter informação da aplicação das regras dos parceiros
                $scope.checkParceiros();           

                if($scope.ic.mesmahabitacao=='Sim'){
                    $scope.mesmaHabitacao = true;
                } else {
                    $scope.mesmaHabitacao = false;
                }  
                $scope.rejeicoes='';
                (answer.data.rejeicoes).forEach(function(ln){
                    $scope.rejeicoes += ln.data +' -->   ' + ln.motivo +';   ' + ln.obs + ';  ' + ln.outro + '\n';
                });

                $scope.cc = answer.data.cc;


            });

           getComprovativos($scope.lead);
       } else {
           alert("Atenção! Verifique os dados e tente outra vez");
       }

    } 

        //Checa parceiros 
    $scope.checkParceiros = function () {
         if (!$scope.s.segundoproponente || $scope.s.segundoproponente == 0) {
            $scope.RLiq = +$scope.ic.vencimento + +$scope.calculos.outrosR;
            $scope.RLiqCt = +$scope.ic.venc_cetelem + +$scope.calculos.outrosR;             
         } else {
            $scope.RLiq = +$scope.ic.vencimento + +$scope.ic.vencimento2 + +$scope.calculos.outrosR;
            $scope.RLiqCt = +$scope.ic.venc_cetelem + +$scope.ic.venc_cetelem2 + +$scope.calculos.outrosR;
         }  

        
        $scope.despesa = +$scope.ic.valorhabitacao + +$scope.calculos.outrosC;

        //Calculos da tx de esforço e validação das regras
        $scope.parceirosChk = [];

        $scope.regras.forEach(function (ln) {
            ln['motivo'] = "";
            //Validar tipo de credito, valor pretendido, prazo, idade
            if ($scope.s.tipocredito == ln.tipocredito) {
                if (($scope.ic.idade < +ln.idade_min) || ($scope.ic.idade > +ln.idade_max)) {
                    ln['motivo'] = "Idade do cliente";
                } else if (($scope.s.prazopretendido < +ln.prazo_min) || ($scope.s.prazopretendido > +ln.prazo_max)) {
                    ln['motivo'] = "Prazo";
                } else if ((+$scope.s.valorpretendido < +ln.montante_min) || (+$scope.s.valorpretendido > +ln.montante_max)) {
                    ln['motivo'] = "Montante pedido";
                } else if ($scope.s.segundoproponente == 0 && ($scope.ic.vencimento < +ln.vencimento_1t) && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 1º titular";
                    
                } else if ($scope.s.segundoproponente == 0 && +ln.vencimento_1t==0 && +$scope.ic.vencimento < +ln.soma_venc  && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 1º titular";
                    
                } else if ((+$scope.ic.venc_cetelem < +ln.vencimento_1t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento Cetelem 1º titular";
                } else if ($scope.s.segundoproponente == 1 && (+$scope.ic.venc_cetelem2 < +ln.vencimento_2t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento Cetelem 2º titular";
                } else if ($scope.s.segundoproponente == 1 && (+$scope.ic.vencimento2 < +ln.vencimento_2t) 
                        && ((+$scope.ic.vencimento + +$scope.ic.vencimento2 ) < +ln.soma_venc) && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 2º titular";
//                } else if((+$scope.ic.vencimento + +$scope.ic.vencimento2) < +ln.soma_venc) {
//                    ln['motivo'] = "Vencimento(s) com valor inferior ao exigido ("+ ln.soma_venc +")";
                } else {
                    //Calculos 
                    $scope.RL = 0;
                    //só um titular
                    if (!$scope.s.segundoproponente || $scope.s.segundoproponente == 0) {
                        if (+ln.indice_rl > 1) {
                                    $scope.RL = +$scope.ic.venc_cetelem + +$scope.ic.outrosrendimentos;
                        } else {
                            $scope.RL = +$scope.ic.vencimento + +$scope.ic.outrosrendimentos;
                        }
                    } else {
                        // dois titulares
                        if (+ln.indice_rl > 1) {
                            $scope.RL = +$scope.ic.venc_cetelem + +$scope.ic.venc_cetelem2 + +$scope.ic.outrosrendimentos;
                        } else {
                            $scope.RL = +$scope.ic.vencimento + +$scope.ic.vencimento2 + +$scope.ic.outrosrendimentos;
                        }
                    }

                    //Taxa de esfoço
                    ln['txEsf'] = Math.round(((+$scope.ic.valorhabitacao + +$scope.ic.outroscreditos + +$scope.s.prestacaopretendida + (+$scope.ic.filhos * +ln.filhos)) / (+$scope.RL * +ln.indice_rl)) * 100);
                    //Disponibilidade Orçamental
     //               ln['disp'] = Math.round(+$scope.RL - (+$scope.ic.valorhabitacao + +$scope.s.prestacaopretendida + +$scope.ic.outroscreditos + (+$scope.ic.filhos * +ln.filhos) + +ln.disp_orcamental));
                    //Disponibilidade Orçamental
                   if(ln['parceiro'!=7]) {  
                       // Calculo normal
                        ln['disp'] = Math.round(+$scope.RL - (+$scope.ic.valorhabitacao + +$scope.s.prestacaopretendida + +$scope.ic.outroscreditos + (+$scope.ic.filhos * +ln.filhos) + +ln.disp_orcamental));
                    } else {
                        var DispOrcUnicre =0;
                         // Calculo de disponibilidade para UNICRE - depende do tipo de habitação
                         if($scope.ic.tipohabitacao==1){  // tipo habitação
                                 DispOrcUnicre = ln['habarrendada'];
                         } else {
                                 DispOrcUnicre = ln['habpropria'];
                         }
                         // Calculo
                        ln['disp'] = Math.round(+$scope.RL - (+$scope.ic.valorhabitacao + +$scope.s.prestacaopretendida + +$scope.ic.outroscreditos
                                + (+$scope.ic.filhos * +ln.filhos) + +ln.disp_orcamental + +DispOrcUnicre));
                         
                    }
                    
                    if (+ln['txEsf'] > +ln.tx_esfoco && +ln.tolerancia == 0) {
                        ln['motivo'] = "Taxa de esforço";
                    } else if (+ln['txEsf'] > (+ln.tx_esforco + +ln.tolerancia)) {
                        ln['motivo'] = "Taxa de esforço";
                    } else if (+ln['disp'] <= 0) {
                        ln['motivo'] = "Disponibilidade orçamental";
                    }

                }
                if (ln['motivo'] != "") {
                    ln['parceiroOk'] = "parceiroRed";
                } else {
                    ln['parceiroOk'] = "parceiroOk";
                }
                $scope.parceirosChk.push(ln);
            }
        });
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
angular.module('appMain').controller('modalInstanceViewDoc_adm', function($scope,$http,$modalInstance,items,$timeout,$rootScope, $sce){
    $scope.nomedoc = items.nomedoc;
 
      $rootScope.prograssing = true;  
      $http({
          url:'php/getDocBase64.php',
          method:'POST',
          data:JSON.stringify(items)
      }).then(function(answer){

            if(answer.data.tipo =='jpg'){
                console.log('JPG: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + answer.data.fx64);
            } else if (answer.data.tipo =='jpeg'){
                console.log('JPEG: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpeg;base64,' + answer.data.fx64);
            } else {
                console.log('PDF: ' + answer.data.tipo);
                $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
            }
          $rootScope.prograssing = false;
      }); 

    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  


});


/**
 * Modal instance to register Rejection
 */
angular.module('appMain').controller('modalInstanceRejeitar', function($scope,$http,$modalInstance,items){
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
                    console.log(answer);
                });
                    window.location.replace("");
            }
    };
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to enviar email a pedir Documentos
 */
angular.module('appMain').controller('modalInstancePedirDoc', function($scope,$modalInstance,$http,items){
    $scope.lead = items;
    $scope.d = {};
    $scope.outroDoc ='';
    //Obter lista de documentação 
    $http({
        url:'php/getData.php',
        method:'POST',
        data:'cnf_docnecessaria'
    }).then(function(answer){
        $scope.docs = answer.data;
        
        //Pedir a documentação selecionada
        $scope.enviarPedidoDoc = function(d){
            if(d){
                $http({
                    url:"php/sendEmailMissingDocsA.php",
                    method:'POST',
                    data:JSON.stringify({'lead':$scope.lead,'docFalta':d.docs,'outroDoc':$scope.outroDoc,'user':JSON.parse(sessionStorage.userData)})
                }).then(function(answer){
                   alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
        //Enviar o pedido da documentação em falta
        $scope.enviarPedidoDocEmFalta = function(lead,d){
            if(d){
                $http({
                    url:"php/gestor/sendEmailMissingDocsA_1.php",
                    method:'POST',
                    data:JSON.stringify({'lead':lead,'docFalta':d.docs,'outroDoc':$scope.outroDoc,'user':JSON.parse(sessionStorage.userData)})
                }).then(function(answer){
                    alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
    });
    

    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to change doc name
 */
angular.module('appMain').controller('modalInstanceChangeDoc', function($scope,$http,$modalInstance,items){
    $scope.d={};
    $scope.docOrig = items;
        //obter tipos de documentos
    $http({
        url:"php/getData.php",
        method:"POST",
        data:'cnf_docnecessaria'
    }).then(function(answer){
        $scope.docs = answer.data;
    });
    
    $scope.saveChange = function(d){
        if(d.docs.length==1){
            $http({
                url:'php/changeNameDoc.php',
                method:'POST',
                data:JSON.stringify({'docOrig':$scope.docOrig,'docNew':d.docs})
            }).then(function(answer){
                $modalInstance.close();
            });
        } else{
            alert("Só pode selecionar um!!");
        }
    };
    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  

});


/**
 * Modal instance to view Comprovativo. 
 */
angular.module('appMain').controller('modalInstanceViewComp1', function($scope, $modalInstance, items, $sce){
    $scope.nomedoc = items.instituicao;
      if(items.tipodoc==="jpg"){
         $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + items.documento);  
      } else {
          $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.documento);
      }
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  

});

/**
 * Modal instance to view CONTRATO. 
 */
angular.module('appMain').controller('modalInstanceViewContratoG', function($scope,$rootScope,$modalInstance,items,$sce){
    $scope.nomedoc = items.nome;
    $rootScope.prograssing = true;
    $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.fx64);
   $rootScope.prograssing = false;
    //Obter o base64 para a lead e linha que está no doc

    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  

});

/**
 * Modal instance para fazer o agendamento
 */
angular.module('appMain').controller('modalInstanceAgendamento', function($scope,$http,$modalInstance,items){
    
    $scope.ag = {};
    $scope.ag.lead = items;
    $scope.ag.user = sessionStorage.userId;
    
    $scope.saveAgendamento = function(ag){
        if (ag.data) {
            var dia = ag.data.getDate();
            var mes = ag.data.getMonth() + 1;
            var ano = ag.data.getFullYear();
            ag.data = (ano + '-' + mes + '-' + dia).toLocaleString();
        } else {
            ag.data = null;
        }
        $http({
            url: 'php/gestor/agendamentoNoDetalhe.php',
            method:'POST',
            data:JSON.stringify(ag)
        }).then(function(answer){
            console.log(answer.data);
            $modalInstance.close('OK');
        });
    }

    
    $scope.closeModal = function(){
        $modalInstance.dismiss('Cancel');
    };  

});

/**
 * Modal instance to list historico de leads
 */
angular.module('appMain').controller('modalInstanceHistorico', function ($scope, $http, $modal, $modalInstance, items) {
    $scope.lista = items.leads;
  //  console.log(items.lead);

    $scope.openHistoricoLeadDetail = function (lead) {
        
        var modalInstance = $modal.open({
            templateUrl: 'modalHistoricoLeadsDetail.html',
            controller: 'modalInstanceHistoricoLeadsDetail',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });

    }
    
    $scope.anularRepetida =  function(){
        $http({
            url:'php/gestor/anulaRepetida.php',
            method:'POST',
            data:JSON.stringify({'lead': items.lead, 'user': sessionStorage.userId})
        }).then(function(answer){
            window.location.replace("");
        });
    }

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to list open lead detail
 */
angular.module('appMain').controller('modalInstanceHistoricoLeadsDetail', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    if($scope.lead){
        $scope.readOnly = true;

        $http({
            url: 'php/getData.php',
            method: 'POST',
            data: 'cnf_sitfamiliar'
        }).then(function (answer) {
            $scope.estadoscivis = answer.data;
        });

        $http({
            url: 'php/getLeadAllInfo.php',
            method: 'POST',
            data: JSON.stringify({"lead": $scope.lead, "user": JSON.parse(sessionStorage.userData)})
        }).then(function (answer) {
            $scope.dl = answer.data.dlead;
            $scope.ic = answer.data.infoCliente;
            $scope.rendimentos = answer.data.rendimentos;
            $scope.creditos = answer.data.creditos;
            $scope.contactos = answer.data.contactos;
            $scope.docs = answer.data.docs;
            $scope.financiamentos = answer.data.financiamentos;
            //   $scope.rejeicoes = answer.data.rejeicoes;

            $scope.rejeicoes = '';
            (answer.data.rejeicoes).forEach(function (ln) {
                $scope.rejeicoes += ln.data + ' -->   ' + ln.motivo + ';   ' + ln.obs + ';  ' + ln.outro + '\n';
            });
        });
    }else {
        alert("Atenção! Verifique os dados e tente novamente.");
    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});


// Modal instance para Listar e selecionar simulações guardadas
angular.module('appMain').controller('modalInstanceGetSimulaDet', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.sim= {};
    $http({
        url:'php/analista/getSimulacoes.php',
        method: 'POST',
        data: $scope.lead
    }).then(function(answer){
        $scope.simulacoes = answer.data;
    });
    
    $scope.selectSimula = function(s){
        console.table(s);
        $modalInstance.close(s);
    }

    

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});