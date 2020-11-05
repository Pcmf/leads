/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('gcontactRecController', function ($scope, $http, $modal, $routeParams) {
    $scope.c ={};
    $scope.c.mkCall = true;
    $scope.tpc = 'CP';
    $scope.totalOCreditos = 0;
    $scope.totalOCPrestacoes = 0;
    $scope.poupanca = 0;
    $scope.txEsforco = 0.0;
    
    if (!sessionStorage.turn) {
        sessionStorage.turn = 'N';   //'N' para selecionar a ativa ou nova ou 'A' para selecionar Agendada.
    }
    //to make the left side painel heigth
    $scope.h = document.getElementById('total').offsetHeight + 133;
    $scope.anoatual = new Date().getFullYear();

    //Puxar LEADS
    if ($routeParams.id) {
        //Get the LEAD by lead id
        console.log("wrong way");
        $http({
            url: 'php/gestor/getPushedLead.php',
            method: 'POST',
            data: JSON.stringify({'lead': $routeParams.id, 'user': sessionStorage.userData})
        }).then(function (answer) {

            $scope.c.segprop = false;
            $scope.lead = answer.data.lead;
                if (answer.data.processo) {
                    $scope.c = answer.data.processo;
                } else {
                    $scope.c = answer.data.lead;
                    $scope.c.vencimento = $scope.c.rendimento1;
                    
                }

                $scope.regCont = answer.data.regCont;
                $scope.openLeads = answer.data.openLeads;
            
        });

    } else {
        //Get the LEAD next  
        $http({
            url: 'php/gestor/getPushedLeadRec.php',
            method: 'POST',
            data: JSON.stringify({'user': sessionStorage.userData, 'turn': sessionStorage.turn})
        }).then(function (answer) {
            $scope.lead = answer.data.lead;
            // Quando é uma chamada agendada redireciona para outra vista
//            if (answer.data.call) {
//                window.location.replace('#!/call/'+$scope.lead.id);
//                
//            } else {
            $scope.c = answer.data.processo;
            $scope.c.mkCall = true;
            $scope.regCont = answer.data.regCont;
            $scope.openLeads = answer.data.openLeads;
            //  sessionStorage.turn ==='N'?sessionStorage.turn ='A':sessionStorage.turn='N';
//        }
        });
    }
    
    //Limpar dados do segundo proponente quando não é selecionado
    $scope.segundoProponente = function () {
        if (!$scope.c.segprop) {
            $scope.c.idade2 = null;
            $scope.c.profissao2 = null;
            $scope.c.tipocontrato2 = null;
            $scope.c.vencimento2 = null;
            $scope.c.anoinicio2 = null;
        }
    };
    //Outros rendimentos
    var outroR = [];
    outroR.push({});
    $scope.outrosR = outroR;
    $scope.addLineOutrosRendimentos = function () {
        outroR.push({});
        $scope.outrosR = outroR;
    };
    $scope.removeLineOutrosRendimentos = function () {
        outroR.pop();
    };
    //Outros creditos    
    var outroC = [];
    outroC.push({});
    $scope.outrosC = outroC;
    $scope.addLineOutrosCreditos = function () {
        outroC.push({});
        $scope.outrosC = outroC;
        //calcularPoupanca();
    };
    $scope.removeLineOutrosCreditos = function (index) {
        outroC.pop();
        $scope.outrosC = outroC;
        //calcularPoupanca();
    };
    
    $scope.changeTPC = function(){
        $scope.tpc = $scope.c.tipocredito;
    }
    
    // Simulações para enviar no email juntamente com o pedido de documentação
     
    var simulacao = [];
    simulacao.push({});
    $scope.simulacoes = simulacao;
    $scope.addLineSimula = function () {
        simulacao.push({});
        $scope.simulacoes = simulacao;
    };
    $scope.removeLineSimula= function (index) {
        simulacao.pop();
        $scope.simulacoes = simulacao;
    };
    
    
    // TX DE ESFORÇO
    $scope.calcularTxEsforco = function(){
        $scope.txEsforco =0;
        $vencimento = +$scope.c.vencimento;
        if($scope.c.segprop) {
            console.log( $scope.c.vencimento2);
            $vencimento += +$scope.c.vencimento2;
        }
        if(!$scope.c.valorhabitacao) {
            $scope.c.valorhabitacao =0;
        }
        $custosGerais = +$scope.c.valorhabitacao + +$scope.c.valorprestacao;
        var totalOCPrestacoesCP = 0;
        var totalOCPrestacoesCC = 0;
        if($scope.c.oc){
            var result = [];
            var keys = Object.keys($scope.c.oc);
            keys.forEach(function(key){
                result.push($scope.c.oc[key]);
            });
            if(result.length>=0){
                result.forEach(function (ln){
                   !ln.liquidar ? totalOCPrestacoesCC += +ln.prestacao: null;
                   totalOCPrestacoesCP += +ln.prestacao;
                });
            }
        }
        if($scope.c.tipocredito == 'CC'){
            $scope.txEsforco = ($custosGerais + totalOCPrestacoesCC) / $vencimento *100;
            
        } else {
            $scope.txEsforco = ($custosGerais + totalOCPrestacoesCP) / $vencimento *100;
        }
        $scope.txEsforco<=65 ? $scope.progressColor= 'progress-bar-success' : $scope.progressColor= 'progress-bar-danger';
    }
    
    $scope.calcularPoupanca = function(){
        
        if($scope.c.oc){
            var result = [];
            var keys = Object.keys($scope.c.oc);
            keys.forEach(function(key){
                result.push($scope.c.oc[key]);
            });

            if(result.length>=0){
                var totalOC = 0;
                var totalOCPrestacoes = 0;
                result.forEach(function (ln){
                    totalOC += +ln.valorcredito;
                   ln.liquidar ? totalOCPrestacoes += +ln.prestacao: null;
                });
                $scope.totalOCreditos = +totalOC;
                $scope.totalOCPrestacoes = +totalOCPrestacoes;
                $scope.poupanca = +totalOCPrestacoes - +$scope.c.valorprestacao;
            } else {
                $scope.totalOCreditos = 0;
                $scope.totalOCPrestacoes = 0;   
                $scope.poupanca = 0;
            }
           $scope.totalPoupanca = $scope.c.prazopretendido * $scope.poupanca;
        }
    }
    
    //get data for Selects
    //Tipo Contrato    
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_sitprofissional'
    }).then(function (answer) {
        $scope.tipocontratos = answer.data;
    });
    //Estado Civil
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_sitfamiliar'
    }).then(function (answer) {
        $scope.estadoscivis = answer.data;
    });
    //Tipo Habitação
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_tipohabitacao'
    }).then(function (answer) {
        $scope.tiposhabitacao = answer.data;
    });

    //Button to open modal to finalize contact
    $scope.finalize = function (c) {
        //Validate fields
        if (!checkFields(c)) {
            if ($scope.grauparent == 'Conjugue') {
                $scope.c.parentesco2 = 'Conjugue';
            }
            var modalInstance = $modal.open({
                templateUrl: 'modalFinalize.html',
                controller: 'modalInstanceFinalize',
                size: 'lg',
                resolve: {items: function () {
                        return $scope.c;
                    }
                }
            });
        }
    };
    //Finalizar quando é uma lead ativada - grava o processo com os dados do formulario e vai para detalhe
    $scope.finalizarOpc2 = function(){
        //Validate fields
        if (!checkFields($scope.c)) {
            if ($scope.grauparent == 'Conjugue') {
                $scope.c.parentesco2 = 'Conjugue';
            }
            $http({
                url:'php/gestor/saveProcessoAtivado.php',
                method:'POST',
                data: JSON.stringify({'lead': $scope.lead, 'processo': $scope.c, 'user': sessionStorage.userId})
            }).then(function(answer){
                if(answer.data){
                    window.location.replace('#!/detLead/'+$scope.lead.id);
                } else {
                      alert("Houve um erro na criação do processo. Verifique os dados e tente outra vez ou entre em contacto com o suporte!") ;
                }
            });
        }
    }


    //Button para fazer a chamada
    $scope.makeCall = function () {
        console.log('func: ' + $scope.c.mkCall);
        if ($scope.c.mkCall) {
            $scope.c.mkCall = !$scope.c.mkCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": $scope.c.telefone, "lead": $scope.lead.id})
            }).then(function (answer) {
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
            });
        } else {
            $scope.c.mkCall = !$scope.c.mkCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
            });
        }
    };
    

    //Button when no answer
    $scope.noAnswer = function () {
        //Confirm this click
        if (confirm('Vai agendar para outro dia. \nPretende continuar?')) {
            console.log('no answer: ' + $scope.c.mkCall);
            !$scope.c.mkCall ? $scope.makeCall() : null;
            //Agendamento and go to dashboard
            var param = {};
            param.lead = $scope.c;
            param.user = JSON.parse(sessionStorage.userData);
            $http({
                url: 'php/gestor/agendamentoAutomaticoRec.php',
                method: 'POST',
                data: JSON.stringify(param)
            }).then(function (answer) {
                console.log(answer.data);
            
            sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            window.location.replace("");
            });
        }

    };    

    //Button when the phone isn't atribuited
    $scope.notAtrib = function () {
        //Confirm this click
        if (confirm('Vai fechar LEAD como não Atribuido. \nPretende continuar?')) {
            //if the email exist then cal function to send email
            !$scope.c.mkCall ? $scope.makeCall() : null;
            if ($scope.c.email) {
                var parm = {};
                parm.lead = $scope.c;
                parm.user = JSON.parse(sessionStorage.userData);
                $http({
                    url: 'php/gestor/sendEmailBadContact.php',
                    method: 'POST',
                    data: JSON.stringify(parm)
                }).then(function (answer) {
                    console.log(answer.data);
                    //call function to change lead status to ANULADO (3)
                    updateStatus(3, $scope.c);
                    
                    //redirweciona para a dashboard
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace("");
                });

            }

        }

    };


    
    //Fora do expediente - enviar SMS e Email
    $scope.foraDoExpediente = function () {
        //Confirm this click
        if (confirm('Atenção! Vai enviar um SMS e agendar para outro dia!')) {
            //Agendamento and go to dashboard
            var param = {};
            param.lead = $scope.c;
            param.user = JSON.parse(sessionStorage.userData);
            $http({
                url: 'php/gestor/foraDoExpediente.php',
                method: 'POST',
                data: JSON.stringify(param)
            }).then(function (answer) {
                console.log(answer.data);
            });
            sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            window.location.replace("");
        }

    };

    //Button to make agendamento
    $scope.agendamento = function () {
        if ($scope.c) {
            //Agendamento and go to dashboard
            var modalInstance = $modal.open({
                templateUrl: 'modalAgendar.html',
                controller: 'modalInstanceAgendar',
                size: 'lg',
                resolve: {items: function () {
                        return $scope.c;
                    }
                }
            });
        } else {
            alert('Há um problema com o processo. Por favor verifique.\Ou entre em contacto com o apoio!');
        }
    };

    //Button to make agendamento para dia 22 motivo BP
    $scope.agendamentoBP = function () {
        if ($scope.c) {
            if (confirm("Vai agendar para o proximo dia 22. Pretende continuar?")) {
                $http({
                    url: 'php/gestor/agendamentoBP22.php',
                    method: 'POST',
                    data: JSON.stringify({'lead': $scope.c, 'userId': sessionStorage.userId})
                }).then(function (answer) {

                    window.location.replace("");
                });
            }
        }
    };

    //Button to reject LEAD
    $scope.rejectLead = function (c) {
        //Agendamento and go to dashboard
        var modalInstance = $modal.open({
            templateUrl: 'modalRejeitar.html',
            controller: 'modalInstanceRejeitar1',
            size: 'lg',
            resolve: {items: function () {
                    return c;
                }
            }
        });
    };

    //Button to open modal to view openLeads list
    $scope.listOpenLeads = function (leads) {
        //Validate fields
        var obj = {};
        obj.leads = leads;
        obj.lead= $scope.lead.id;
        var modalInstance = $modal.open({
            templateUrl: 'modalOpenLeads.html',
            controller: 'modalInstanceOpenLeads',
            size: 'lg',
            resolve: {items: function () {
                    return obj;
                }
            }
        });
    };


    //FUNCTIONS
    /**
     * Function to update LEAD status and register contact
     * @param {int} status  
     * @param {obj) lead 
     * @returns {undefined}
     */
    function updateStatus(status, lead) {
        var param = {};
        param.lead = lead.id;
        param.userId = sessionStorage.userId;
        param.status = status;
        $http({
            url: 'php/updateLeadStatus.php',
            method: 'POST',
            data: JSON.stringify(param)
        }).then(function (answer) {
            // alert(answer.data);
        });
        ;
    }


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
          alert("Tem de indicar o sector!");
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
        if($scope.c.segprop){
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
  
        if($scope.c.tipocredito!='CT' && !$scope.c.finalidade && !erro){
          alert("Tem de indicar a Finalidade do crédito!");
          erro=true;
        }

        return erro;
    }
    
});

/**
 * Modal instance to select required documents, how to send and ETA 
 */
angular.module('appGest').controller('modalInstanceFinalize', function ($scope, $rootScope, $http, $modalInstance, items) {
    $scope.chamadaTerminada = items.mkCall;
    console.log('Modal: ' + items.mkCall);
    $scope.m = {};
    $scope.e = {};
    $scope.d = {};
    $scope.tpc = items.tipocredito;
    $scope.m.email = items.email;
    var simulacoes = items.sim;
    console.table(simulacoes);
    
    var date = new Date();
    var mes = '00';
    if (date.getMonth() + 1 < 10) {
        mes = '0' + (date.getMonth() + 1);
    } else {
        mes = date.getMonth() + 1;
    }
    $scope.minDate = date.getFullYear() + '-' + mes + '-' + date.getDate();
    if ($scope.m.email) {
        $scope.e.tipoenvio = 'email';
    }

    //Get Documentation
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_docnecessaria'
    }).then(function (answer) {
//        $scope.docs= answer.data;
        $scope.docs1=[];
        $scope.docs2=[];
        $scope.d = []; 
        $scope.d.docs1=[];
        $scope.d.docs2=[];       
       answer.data.forEach(function (ln){
           if(ln.titular==1){
               $scope.docs1.push(ln);
                ln.tipocredito=='T' ? $scope.d.docs1.push(ln) : null;
                ($scope.tpc=="CC" && ln.tipocredito=='C') ? $scope.d.docs1.push(ln) : null;
                (($scope.tpc=="CHCC" || $scope.tpc=="CH1" || $scope.tpc=="CH2") && ln.tipocredito=='H') ? $scope.d.docs1.push(ln) : null;
           } else if(items.segprop) {
               $scope.docs2.push(ln);
               ln.tipocredito=='T' ? $scope.d.docs2.push(ln) : null;
               ($scope.tpc=="CC" && ln.tipocredito=='C') ? $scope.d.docs2.push(ln) : null;
          }
       });
    });
    //botão para terminar a chamada antes de guardar e enviar SMS
    $scope.terminarChamada = function(){
        $scope.chamadaTerminada = true;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
            });
    }
    
    $scope.saveProcess = function (d, m, e) {
        //Validar os dados do formulario do modal
        //Documentação - pelo menos um documento
        var erro = false;
        if (d.docs1 == undefined || d.docs1.length == 0) {
            alert('Atenção! Não selecionou nenhum tipo de documento!');
            //erro = true;
        }
        var docs = {};
        if(d.docs2){
            docs.docs = d.docs1.concat( d.docs2);
        } else {
            docs.docs = d.docs1;
        }
        if (!erro) {
            //Gravar os dados do formulario
            $rootScope.prograssing = true;
            var parm = {};
            parm.process = items;
            parm.address = m;
            parm.docs = docs;
            parm.tipoEnv = e.tipoenvio;
            parm.user = JSON.parse(sessionStorage.userData);
            $http({
                url: 'php/gestor/saveProcess.php',
                method: 'POST',
                data: JSON.stringify(parm),
                headers: {'Content-Type': 'application/json'}
            }).then(function (answer) {
        //        console.log(answer.data);
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
                alert('Processo guardado.');
                $rootScope.prograssing = false;
                sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                window.location.replace("");
            });
        }
    };


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to select Agendar novo contacto
 */
angular.module('appGest').controller('modalInstanceAgendar', function ($scope, $http, $modalInstance, items) {
    $scope.ag = {};
    $scope.saveAgenda = function (ag) {

        if (ag.data) {
            var dia = ag.data.getDate();
            var mes = ag.data.getMonth() + 1;
            var ano = ag.data.getFullYear();
            ag.data = (ano + '-' + mes + '-' + dia).toLocaleString();
        } else {
            ag.data = null;
        }
        //  alert(ag.data);
        var param = {};
        param.userId = sessionStorage.userId;
        param.lead = items;
        param.ag = ag;
        JSON.parse(sessionStorage.userData).tipo == 'GRec' ? param.rec = true : param.rec = false;
        $http({
            url: 'php/gestor/agendamentoManual.php',
            method: 'POST',
            data: JSON.stringify(param)
        }).then(function (answer) {
            sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            window.location.replace("");
        });

    };
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to register Rejection
 */
angular.module('appGest').controller('modalInstanceRejeitar1', function ($scope, $http, $modalInstance, items) {
    $scope.m = {};
    $scope.rejeitar = function () {
        if (!$scope.r) {
            alert("Tem de selecionar um motivo ou descrever!");
        } else {
            if ($scope.motivoComum == 'RGPD') {
                $http({
                    url: 'php/delRGPD.php',
                    method: 'POST',
                    data: JSON.stringify({'lead': items.id, 'user': sessionStorage.userId})
                }).then(function (answer) {
                    alert("Toda a informação pessoal foi eleminada!");
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace('#!/dashboard');
                });
            } else {
                var param = {};
                param.user = JSON.parse(sessionStorage.userData);
                ;
                param.lead = items;
                param.motivo = $scope.r;
                $http({
                    url: 'php/registarRejeicao.php',
                    method: 'POST',
                    data: JSON.stringify(param)
                }).then(function (answer) {
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    window.location.replace("");
                });
            }
        }
    };
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to list open leads
 */
angular.module('appGest').controller('modalInstanceOpenLeads', function ($scope, $http, $modal, $modalInstance, items) {
    $scope.lista = items.leads;
  //  console.log(items.lead);

    $scope.openLeadDetail = function (lead) {
        
        var modalInstance = $modal.open({
            templateUrl: 'modalOpenLeadsDetail.html',
            controller: 'modalInstanceOpenLeadsDetail',
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
        })
    }

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to list open lead detail
 */
angular.module('appGest').controller('modalInstanceOpenLeadsDetail', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.readOnly = true;
    if($scope.lead){
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

