/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anForm1Controller', function ($scope, $http, $routeParams, $modal, $timeout, $sce) {
    $scope.addNewOR = false;
    $scope.addNewOC = false;
    $scope.titular = "primeiro";
    $scope.firstT ="active";
    $scope.secondT = "";
    $scope.onCall = false;
    $scope.reverse=true;
    // Simulador de vencimentos
    $scope.sim = {};
    // Hipotecario
    $scope.hip = {};
    //Definição dos tabs
    $scope.tabs = [{
            title: 'LEAD',
            id: 'zero.tpl'
        }, {
            title: 'Cliente',
            id: 'one.tpl'
        }, {
            title: 'Credito Pretendido, OR, OC',
            id: 'two.tpl'
        }, {
            title: 'Situação Financeira',
            id: 'four.tpl'
        }, {
            title: 'Processo',
            id: 'five.tpl'
        }, {
            title: 'Notas',
            id: 'six.tpl'
        }, {
            title: 'Comunicações',
            id: 'seven.tpl'
        }, {
            title: 'Contactos',
            id: 'eight.tpl'
        }, {
            title: 'Simulador Vencimentos',
            id: 'nine.tpl'
        }, {
            title: 'Crédito Hipotecário',
            id: 'ten.tpl'
        }];
    /*
     * Controlo dos tabs e dos paineis
     */
    if (sessionStorage.currentTab != undefined) {
        $scope.currentTab = sessionStorage.currentTab;
        var t = {};
        t.id = $scope.currentTab;
        onClickTabFunc(t);
    } else {
        $scope.currentTab = 'zero.tpl';
    }

    //Função para navegar nas tabs
    $scope.onClickTab = function (tab) {
        onClickTabFunc(tab);
    };
    $scope.isActiveTab = function (tabId) {
        return tabId == $scope.currentTab;
    };


    //Definição dos tabrs  - Painel direito
    $scope.tabrs = [{
            title: 'Registo dos Parceiros',
            id: 'rzero.tpl'
        }, {
            title: 'Documentação',
            id: 'rtwo.tpl'
        }, {
            title: 'Simulador',
            id: 'rthree.tpl'
        }];
    /*
     * Controlo dos tabs e dos paineis
     */
    if (sessionStorage.currentTabR != undefined) {
        $scope.currentTabR = sessionStorage.currentTabR;
        var tr = {};
        tr.id = $scope.currentTabR;
        onClickTabFuncR(tr);
    } else {
        $scope.currentTabR = 'rtwo.tpl';
    }

    //  loadReqByTab($scope.currentTab);
    //Função para navegar nas tabs
    $scope.onClickTabR = function (tab) {
        onClickTabFuncR(tab);
    };
    $scope.isActiveTabR = function (tabId) {
        return tabId == $scope.currentTabR;
    };

    $scope.fazerChamada = function(lead){
        console.log(lead);
        if (!$scope.onCall) {
            $scope.onCall = !$scope.onCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": $scope.ic.telefone, "lead": lead})
            }).then(function (answer) {
                console.log(answer.data);
                if (answer.data.failure) {
                    alert(answer.data.results[0].error);
                }
                // Atualizar o registo de contactos
                atualizaListaContactos(lead);
            });
        }
    }

    $scope.terminarChamada = function(lead){
        console.log(lead);
        if($scope.onCall){
            $scope.onCall = !$scope.onCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
                atualizaListaContactos(lead);
            });
        }    
        
    }
    
    $scope.naoAtende = function(lead){
        console.log(lead);
        if($scope.onCall){
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
                    if (answer.data.results[0].error) {
                        alert(answer.data.results[0].error);
                    }
//                    user = JSON.parse(sessionStorage.userData);
//                    var sms = "Para podermos efetuar uma analise ao seu pedido de credito, indique-nos qual a melhor hora para contacto ou ligue-nos diretamente. " + user.nome + " GESTLIFES";
//                    $http({
//                        url: 'php/sendSMS.php',
//                        method: 'POST',
//                        data: JSON.stringify({"user": user.id, "telefone": $scope.ic.telefone, "lead": lead, 'sms': sms})
//                    }).then(function (answer) {
//                        console.log(answer.data);
//                    });
                });
            });
        }
    }   

    $scope.showDoc = false;
    $scope.historic = [];
    $scope.readOnly = true;
    //Get Parceiros
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cad_parceiros'
    }).then(function (answer) {
        var parceiros = [];
        answer.data.forEach(function (ln) {
            ln.ativo == 1 ? parceiros.push(ln) : null
        })
        $scope.parceiros = parceiros;
    });

    //Se for uma seleção 
    if ($routeParams.id) {
        getLeadAllInfo($routeParams.id);
    } else {
        //Obter informações do processo
        $http({
            url: 'php/analista/anaGetProcess.php',
            method: 'POST',
            data: JSON.stringify({'user': sessionStorage.userId, 'lead': $routeParams.id})
        }).then(function (answer) {

            $scope.processo = answer.data.processo;
            getLeadAllInfo($scope.processo.lead);
            $scope.historic = answer.data.historic;

        });
    }
    //Adicionar linhas
    $scope.addLineParceiro = function () {
        var sbms = {};
        if ($scope.sbms != undefined) {
            sbms = $scope.sbms;
            sbms.push({});
            $scope.sbms = sbms;
        } else {
            $scope.sbms = [{}];
        }

    };
    //Change LEAD to aproved - check that only one partener has status aproved. If necessary has to cancel the others 
    $scope.aproveLead = function (lead) {
        if ($scope.sbms) {
            //check if only one process is aproved
            var aproved = 0;
            $scope.sbms.forEach(function (ln) {
                if (ln.status == 6 && ln.parceiro!=12) {   //12 -cofidis CC
                    aproved++;
                }
            });
            if (aproved > 1) {
                alert("Não pode ter mais que um processo aprovado.\n Cancele os que não pretende usar");
            } else if( aproved == 0) {
                alert("Verifique os estados dos parceiros.")
            } else {
                $http({
                    url: 'php/analista/aproveLead.php',
                    method: 'POST',
                    data: lead
                }).then(function (answer) {
                    //                alert(answer);
                    window.location.replace("#!/dashboard");
                });
            }
        }
    };
    //Temporary Save changes on submitions - save partener process status and changes/update Lead status to Pendente 
    $scope.submitProcess = function (sbm) {
        if (sbm && sbm.processo) {
            sbm.lead = $scope.ic.lead;
            sbm.userId = sessionStorage.userId;
            
            if(sbm.parceiro == 11 && sbm.status==1){
                alert("Parceiro Novo Banco - Vai criar pdf!");
                $http({
                    'url': 'php/analista/criarPdfNB.php',
                    'method': 'POST',
                    'data': JSON.stringify(sbm)
                }).then(function(answer){
                    window.open('php/analista/doc_NB/'+answer.data);
                });
            }

            $http({
                url: 'php/analista/temporaryUpdates.php',
                method: 'POST',
                data: JSON.stringify(sbm)
            }).then(function (answer) {
                //   console.log(answer.data);
            });
        } else {
            sbm.status ='';
            alert("Atenção! Tem de indicar o numero do processo.");
        }
    };
    //Rejeitar LEAD
    $scope.rejectProcess = function (lead) {
        var modalInstance = $modal.open({
            templateUrl: 'modalReject.html',
            controller: 'modalInstanceReject',
            size: 'md'
            , resolve: {items: function () {
                    return lead;
                }
            }
        });
    };
    //Copia o email para clipboard
    $scope.copyToClip = function () {
        var copyText = document.getElementById("emailToCopy");
        copyText.select();
        document.execCommand("copy");
    };
    //Button to open modal to view openLeads list
    $scope.showHistorico = function (lista) {
        //Validate fields

        var modalInstance = $modal.open({
            templateUrl: 'modalHistoric.html',
            controller: 'modalInstanceHistoricAnalist',
            size: 'lg',
            resolve: {items: function () {
                    return lista;
                }
            }
        });
    };
    //DOCUMENTAÇAO
    //Botão para descarregar um documento (fx)
    $scope.descarregarDoc = function (doc) {
        $http({
            url: 'php/getDocumentacao.php',
            method: 'POST',
            data: JSON.stringify({'lead': doc.lead, 'linha': doc.linha})
        }).then(function (answer) {
            var doc = answer.data[0];
            if (doc.tipo == 'jpg') {
                download("data:image/jpeg;base64," + doc.fx64, doc.nomefx);
            }
            if (doc.tipo == 'png') {
                download("data:image/png;base64," + doc.fx64, doc.nomefx);
            }
            if (doc.tipo == 'pdf') {
                download("data:application/pdf;base64," + doc.fx64, doc.nomefx);
            }
            if (doc.tipo == 'docx') {
                download("data:application/docx;base64," + doc.fx64, doc.nomefx);
            }
        });
    };
    //Botão para descarregar todos os documentos para o ambiente de trabalho
    $scope.descarregarDocs = function () {
        $http({
            url: 'php/getDocumentacao.php',
            method: 'POST',
            data: JSON.stringify({'lead': $scope.dl.id})
        }).then(function (answer) {
            answer.data.forEach(function (ln) {
                if (ln.tipo == 'jpg') {
                    download("data:image/jpeg;base64," + ln.fx64, ln.nomefx);
                }
                if (ln.tipo == 'png') {
                    download("data:image/png;base64," + ln.fx64, ln.nomefx);
                }
                if (ln.tipo == 'pdf') {
                    download("data:application/pdf;base64," + ln.fx64, ln.nomefx);
                }
                if (ln.tipo == 'docx') {
                    download("data:application/docx;base64," + ln.fx64, ln.nomefx);
                }
            });
        });
    };
    //Ver DOC
    $scope.verDoc = function (doc) {

        //Obter o fxbase64
        $http({
            url: 'php/getDocBase64.php',
            method: 'POST',
            data: JSON.stringify(doc)
        }).then(function (answer) {
            //    console.log(answer.data);
            if (answer.data.tipo === "pdf") {
                $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
            } else {
                $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + answer.data.fx64);
            }
            $scope.showDoc = true;
            $scope.nomedoc = doc.nomedoc;
        });

    };

    //Fechar a visualização do documento e mostrar formulario de registo
    $scope.closeShowDoc = function () {
        $scope.showDoc = false;
//        $scope.currentTabR = 'rzero.tpl';
//        onClickTabFuncR($scope.tabrs[0]);
    };

    //Anexar documento
    $scope.anexarDoc = function (d) {
        var parm = {};
        parm.lead = $scope.dl.id;
        parm.doc = d;
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocsA.html',
            controller: 'modalInstanceAnexarDocsA',
            size: 'lg',
            resolve: {items: function () {
                    return parm;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo($routeParams.id);
        });
    };
    //Anexar Doc Extra
    $scope.anexarDocExtra = function () {

        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocsExtra.html',
            controller: 'modalInstanceAnexarDocsExtraA',
            size: 'lg',
            resolve: {items: function () {
                    return $scope.dl.id;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo($routeParams.id);
        });
    };
    //Pedir Documentação em falta
    $scope.pedirDoc = function (lead) {
        //abrir modal com lista de documentação a pedir
        var modalInstance = $modal.open({
            templateUrl: 'modalPedirDoc_.html',
            controller: 'modalInstancePedirDoc_',
            size: 'lg',
            resolve: {items: function () {
                    return {'lead': lead, 'segProp': $scope.ic.segundoproponente};
                }
            }
        });
        modalInstance.result.then(function (answer) {
            getLeadAllInfo($routeParams.id);
        });
    };
    //Documentação OK - quando está a aguardar documentação vai colocar como pendente
    $scope.docsOk = function (lead) {
        $http({
            url: 'php/analista/updateStatusAnalista.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'status': 13})
        }).then(function (answer) {
            getLeadAllInfo($routeParams.id);
        });
    };
    //Remover Documento
    $scope.removerDoc = function (doc, lead) {
        if (confirm('Vai APAGAR este documento! Pretende Continuar?')) {
            $http({
                url: 'php/removerDoc.php',
                method: 'POST',
                data: JSON.stringify({'doc': doc, 'lead': lead, 'op': 'Delete'})
            }).then(function (answer) {
                getLeadAllInfo($routeParams.id);
            });
        }
    };
    //Cancelar Pedido de documento
    $scope.cancelarPedidoDoc = function (doc, lead) {
        $http({
            url: 'php/removerDoc.php',
            method: 'POST',
            data: JSON.stringify({'doc': doc, 'lead': lead, 'op': 'Cancel'})
        }).then(function (answer) {
            getLeadAllInfo($routeParams.id);
        });
    };
    //Alterar a designação de um documento
    $scope.changeDoc = function (doc) {
        //abrir modal com lista de documentação para escolher
        var modalInstance = $modal.open({
            templateUrl: 'modalChangeDoc.html',
            controller: 'modalInstanceChangeDoc',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function (answer) {
            //  alert(answer);
            getLeadAllInfo($routeParams.id);
        });
    };
    //Outros Rendimentos e outros Creditos
    //Adicionar linha de OR
    $scope.saveLinhaOR = function (or) {
        if (or.tiporendimento && or.valorrendimento && or.periocidade) {
            $http({
                url: "php/addNewOR.php",
                method: "POST",
                data: JSON.stringify({'lead': $scope.dl.id, 'or': or})
            }).then(function (answer) {
                if (answer.data.msg == 'OK') {
                    $scope.rendimentos = answer.data.rendimentos;
                    $scope.addNewOR = false;
                } else {
                    alert(answer.data.msg);
                }
            });
        } else {
            alert("Atenção! Tem que preencher os campos todos");
        }
    }

    //Adicionar linha de OC
    $scope.saveLinha = function (oc) {
        if (oc.tipocredito && oc.valorcredito && oc.prestacao && (oc.liquidar || oc.liquidar == 0)) {
            $http({
                url: "php/addNewOC.php",
                method: "POST",
                data: JSON.stringify({'lead': $scope.dl.id, 'oc': oc})
            }).then(function (answer) {
                if (answer.data.msg == 'OK') {
                    $scope.creditos = answer.data.creditos;
                    $scope.addNewOC = false;
                } else {
                    alert(answer.data.msg);
                }
            });
        } else {
            alert("Atenção! Tem que preencher os campos todos");
        }
    }

    $scope.saveAplicar = function (r) {

        $http({
            'url': 'php/analista/saveAplicar.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.dl.id, 'linha': r.linha, 'usar': r.usar})
        }).then(function (answer) {
            getLeadAllInfo($scope.dl.id);
            if (answer.data != 'Ok') {
                alert('Não foi possivel guardar a alteração.');
            }
        });
    }

    $scope.saveAnaChoice = function (c) {

        $http({
            'url': 'php/analista/saveAdminChoice.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.dl.id, 'linha': c.linha, 'adminchoice': c.adminchoice})
        }).then(function (answer) {
            getLeadAllInfo($scope.dl.id);
            $scope.getTotalCreditos();
            if (answer.data != 'Ok') {
                alert('Não foi possivel guardar a alteração.');
            }
        });
    }

    //Guardar Simulações
    $scope.saveChangesFin = function () {
        var param = {};
        param.lead = $scope.dl.id;
        param.vencimento = $scope.ic.vencimento;
        param.vencimento2 = $scope.ic.vencimento2;
        param.venc_cetelem = $scope.ic.venc_cetelem;
        param.venc_cetelem2 = $scope.ic.venc_cetelem2;
        param.valorhabitacao = $scope.ic.valorhabitacao;
        param.filhos = $scope.ic.filhos;
        param.segundoproponente = $scope.ic.segundoproponente;
        param.tipocredito = $scope.ic.tipocredito;
        param.outrosrendimentos = $scope.ic.outrosrendimentos;
        param.outroscreditos = $scope.ic.outroscreditos;
        param.simulacao = $scope.s;
        
        $http({
            'url': 'php/analista/saveChangesFin.php',
            'method': 'POST',
            'data': JSON.stringify({'param': param})
        }).then(function (answer) {
            alert(answer.data);
        });
    }
    
    // Abrir modal com lista de simulações guardadas
    $scope.getSimulacoes = function(lead) {
        var modalInstance = $modal.open({
            templateUrl: 'modalGetSimula.html',
            controller: 'modalInstanceGetSimula',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function (answer) {
             console.table(answer);
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
    
    
    //Limpar dados do segundo titular
    $scope.clear2TitularDados = function(titular){
        if ( !titular || titular==0 ) {
            $scope.p.relacaofamiliar = 0;
            $scope.p.nome2 ="";
            $scope.p.datanascimento2 ="";
            $scope.p.tipodoc2 = 0;
            $scope.p.numdocumento2="";
            $scope.p.nif2 = 0;
            $scope.p.validade2 = "";
            $scope.p.nacionalidade2 = "";
            $scope.p.mesmahabitacao = 0;
            $scope.p.morada2 = "";
            $scope.p.localidade2 = "";
            $scope.p.cp2 = "";
            $scope.p.telefone2 = "";
            $scope.p.email2 = "";
            $scope.p.sector2 = "";
            $scope.p.tiposcontrato2 = 0;
            $scope.p.desde2 = "";
            $scope.p.nomeempres2 = "";
            $scope.p.nifempresa2 = 0;
            $scope.p.telefoneempresa2 = "";
        }
    }
    
    //Processo 
    $scope.saveProcesso = function(p){
        $http({
            'url':'php/analista/saveProcessForm.php',
            'method':'POST',
            'data': JSON.stringify({'lead': $scope.dl.id, 'process': p})
        }).then(function(answer){
            if(answer.data == 'OK'){
                alert("Guardado");
            } else {
                alert("Atenção!!! Não gravou!");
            }
        });
    }

    //Editar Notas
    $scope.editarNota = function () {
        $scope.readOnly = false;
    };
    //Guardar Nota
    $scope.salvarNota = function (lead) {
        $http({
            url: 'php/analista/salvarNota.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'nota': $scope.ic.nota})
        }).then(function (answer) {
            $scope.readOnly = true;
            getLeadAllInfo($routeParams.id);
        });
    };

    //Botão Pendente
    $scope.pendente = function () {
        window.location.replace("");
    }

    //Colocar a aguardar documentação
    $scope.aguardarDoc = function (lead) {
        if (confirm("Vai colocar a Aguardar Documentação! Pretende continuar?")) {
            $http({
                url: 'php/analista/updateStatusAnalista.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'status': 21})
            }).then(function (answer) {
                window.location.replace("");
            });
        }
    }

    //Processo de Cartão de Crédito. Cancela o processo de financiamento.
    $scope.processoCC = function (lead) {
        $http({
            url: 'cc/php/cancelaProcFinancCriaCC.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'user': sessionStorage.userId})
        }).then(function (answer) {
            window.location.replace("");
        });
    }

    //Aprova o processo e faz registo de CC como aceite pelo cliente
    $scope.aproveLeadAndCC = function (lead) {
        //Registar o CC como aceite pelo cliente
        $http({
            url: 'cc/php/novoCCAceiteCliente.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'user': sessionStorage.userId})
        }).then(function (answer) {
            $scope.aproveLead(lead);
            window.location.replace("");
        });

    }

    //Comunicações enviar email
    $scope.enviarComunicacao = function (e) {
        if (e.assunto && e.texto) {
            console.log($scope.dl.id);
            $http({
                url: 'php/sendComunicacao.php',
                method: 'POST',
                data: JSON.stringify({'lead': $scope.dl.id, 'e': e, 'tipo': 'A'})
            }).then(function (answer) {
                alert(answer.data.msg);
                if (answer.data.msg = "Enviado") {
                    $scope.e = {};
                    $scope.comunicacoes = answer.data.comunicacoes;
                }
            });
        } else {
            alert("Atenção! Tem de preencher o assunto e o texto do email.");
        }
    }
    
    $scope.calculaSimula = function(sim){
        if(!$scope.sim.filhos) {
        sim.filhos = $scope.ic.filhos;
        }
        $http({
            url:'php/analista/calculaSimula.php',
            method:'POST',
            data: JSON.stringify(sim)
        }).then(function(answer) {
            console.log( answer.data.vencLiq1);
            $scope.sim.vencLiquido1 = answer.data.vencLiq1;
            if(answer.data.vencLiq2){
                $scope.sim.vencLiquido2 = answer.data.vencLiq2;
            }
        });
    }
    
    // confirmar simulação e guardar os dados na BD
    $scope.confirmSimula = function(sim) {
        $scope.ic.venc_cetelem = sim.vencLiquido1;
        if(sim.vencLiquido2){
            $scope.ic.venc_cetelem2 = sim.vencLiquido2;
            $scope.RLiqCt = sim.vencLiquido1 + sim.vencLiquido2;
        } else {
            $scope.ic.venc_cetelem2=0;
            $scope.RLiqCt = sim.vencLiquido1;
        }
        $scope.checkParceiros();
        
    }
    
    
    // Valores Hipotecario
    $scope.calcResult = function() {
        (($scope.hip.garantia * 0.75 - $scope.hip.valordivida) - $scope.hip.valorpretendido) > 0 ? $scope.result = 'green' : $scope.result = 'red';
    }
    
    $scope.saveSimulaHipo = function() {
        $scope.hip.lead = $scope.dl.id;
        $http({
            url: 'php/analista/saveSimulaHipo.php',
            method: 'POST',
            data: JSON.stringify($scope.hip)
        }).then(function (answer) {
            if (answer.data) {
                alert("Simulação guardada");
            }
        });
    }
    

    //FUNCTIONS
    function onClickTabFunc(tab) {
        var x = document.getElementById(tab.id);
        var k = document.getElementsByClassName('pn');
        for (var i = 0; i < k.length; i++) {
            if (x !== k[i]) {
                k[i].className = k[i].className.replace(" show", " hide");
            } else {
                k[i].className = k[i].className.replace(" hide", " show");
            }
        }
        $scope.currentTab = tab.id;
    }
    function onClickTabFuncR(tab) {
        var x = document.getElementById(tab.id);
        var k = document.getElementsByClassName('pnr');
        for (var i = 0; i < k.length; i++) {
            if (x !== k[i]) {
                k[i].className = k[i].className.replace(" show", " hide");
            } else {
                k[i].className = k[i].className.replace(" hide", " show");
            }
        }
        $scope.currentTabR = tab.id;
    }
    //Obter todos os dados da LEAD/Processo
    function getLeadAllInfo(lead) {
        if(lead){
            //Estados civis
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_sitfamiliar'
            }).then(function (answer) {
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
            //Tipos de habitacao
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_tipohabitacao'
            }).then(function (answer) {
                $scope.tiposhabitacao = answer.data;
            });
            //Relaçoes familiares
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_relacaofamiliar'
            }).then(function (answer) {
                $scope.relacoesfamiliares = answer.data;
            });    
            //Tipos de contrato
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_sitprofissional'
            }).then(function (answer) {
                $scope.tiposcontrato = answer.data;
            });          
            //Obter as regras de financiamento dos parceiros
            $http({
                url: 'php/analista/getRegrasFinanciamento.php',
                method: 'GET'
            }).then(function (answer) {
                $scope.regras = answer.data;
            });
            //Comunicações
            $http({
                url: 'php/getComunicacoes.php',
                method: 'POST',
                data: lead
            }).then(function (answer) {
                $scope.comunicacoes = answer.data;
            });
            //Nacionalidades
            $http.get('lib/nacionalidades.json').then(function(answer) {
                $scope.nacionalidades = answer.data;
            });
            //Parceiros Hipoetcarios
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cad_parceirohipotecario'
            }).then(function (answer) {
                $scope.parceiroshipo = answer.data;
            });          
            
            $http({
                url: 'php/getLeadAllInfo.php',
                method: 'POST',
                data: JSON.stringify({"lead": lead, "user": JSON.parse(sessionStorage.userData)})
            }).then(function (answer) {
                $scope.dl = answer.data.dlead;
                $scope.ic = answer.data.infoCliente;
                $scope.rendimentos = answer.data.rendimentos;
                $scope.creditos = answer.data.creditos;
                $scope.contactos = answer.data.contactos;
                $scope.calculos = answer.data.calculos;
                $scope.financiamentos = answer.data.financiamentos;
                $scope.sbms = answer.data.financiamentos;
                $scope.rejeicoes = answer.data.rejeicoes;
                $scope.docs = answer.data.docs;
                $scope.historic = answer.data.historic;
                $scope.COFD = answer.data.cofd;
                if(answer.data.hipotecario) {
                     $scope.hip = answer.data.hipotecario;
                 }
                $scope.s = answer.data.simula;
                $scope.p = answer.data.processo;
                
                (($scope.hip.garantia * 0.75 - $scope.hip.valordivida) - $scope.hip.valorpretendido) > 0 ? $scope.result = 'green' : $scope.result = 'red';

             //   if(!$scope.ic.outrosrendimentos) {
                    $scope.ic.outrosrendimentos = $scope.calculos.outrosR
               // };
          //      if(!$scope.ic.outroscreditos) {
                    $scope.ic.outroscreditos = $scope.calculos.outrosC
            //    };

                $scope.checkParceiros();

                $scope.getTotalCreditos();
            });
            }else {
                alert("Atenção! Verifique os dados e tente novamente.");
            }
    }

    //função para calcular totais de creditos e prestações
    $scope.getTotalCreditos = function () {
        $scope.totalCreditos = 0;
        $scope.totalPrestacoes = 0;
        if ($scope.creditos) {
            $scope.creditos.forEach(function (ln) {
                if (ln.adminchoice == 1) {
                    $scope.totalCreditos += +ln.valorcredito;
                    $scope.totalPrestacoes += +ln.prestacao;
                }
            })
        }
    };
    //Checa parceiros 
    $scope.checkParceiros = function () {
         if (!$scope.s.segundoproponente || $scope.s.segundoproponente == 0) {
            $scope.RLiq = +$scope.ic.vencimento + +$scope.ic.outrosrendimentos;  //+ +$scope.calculos.outrosR 
            $scope.RLiqCt = +$scope.ic.venc_cetelem + +$scope.ic.outrosrendimentos;      //+ +$scope.calculos.outrosR        
         } else {
            $scope.RLiq = +$scope.ic.vencimento + +$scope.ic.vencimento2 + +$scope.ic.outrosrendimentos; //+ +$scope.calculos.outrosR 
            $scope.RLiqCt = +$scope.ic.venc_cetelem + +$scope.ic.venc_cetelem2 + +$scope.ic.outrosrendimentos;  //+ +$scope.calculos.outrosR 
         }  

        
        $scope.despesa = +$scope.ic.valorhabitacao + +$scope.ic.outroscreditos;//+ +$scope.calculos.outrosC;

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
                    
                } else if ($scope.s.segundoproponente == 0 && +$scope.ic.vencimento < +ln.vencimento_1t && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 1º titular";
                
                } else if ($scope.s.segundoproponente == 0 && +ln.vencimento_1t==0 && +$scope.ic.vencimento < +ln.soma_venc  && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 1º titular";
                    
                } else if ((+$scope.ic.venc_cetelem < +ln.vencimento_1t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento 14/12 1º titular";
                    
                } else if ($scope.s.segundoproponente == 1 && (+$scope.ic.venc_cetelem2 < +ln.vencimento_2t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento 14/12 2º titular";
                    //
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
                            if(ln.tipocredito=='CHCC'){
                                $scope.RL = +$scope.ic.venc_cetelem ;
                            } else {
                                $scope.RL = +$scope.ic.venc_cetelem  + +$scope.ic.outrosrendimentos;
                            }
                        } else {
                            $scope.RL = +$scope.ic.vencimento + +$scope.ic.outrosrendimentos;
                        }
                    } else {
                        // dois titulares
                        if (+ln.indice_rl > 1) {
                            if(ln.tipocredito=='CHCC'){
                                $scope.RL = +$scope.ic.venc_cetelem + +$scope.ic.venc_cetelem2;
                            } else {
                                $scope.RL = +$scope.ic.venc_cetelem + +$scope.ic.venc_cetelem2 + +$scope.ic.outrosrendimentos;
                            }
                        } else {
                            $scope.RL = +$scope.ic.vencimento + +$scope.ic.vencimento2 + +$scope.ic.outrosrendimentos;
                        }
                    }

                    //Taxa de esfoço
                    ln['txEsf'] = Math.round(((+$scope.ic.valorhabitacao + +$scope.ic.outroscreditos + +$scope.s.prestacaopretendida + (+$scope.ic.filhos * +ln.filhos)) / (+$scope.RL * +ln.indice_rl)) * 100);
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
    /**
     * Função para atualizar a lista dos contactos
     * @param {type} lead
     * @returns {undefined}
     */
    function atualizaListaContactos(lead){
                              // Atualizar o registo de contactos
                    $http({
                        url: 'php/getRegistoContactos.php',
                        method: 'POST',
                        data: JSON.stringify({"lead": lead})
                    }).then(function (answer) {
                        $scope.contactos = answer.data;
                    });    
    }

});




//MODAL instance controller for Reject
angular.module('appAnalist').controller('modalInstanceReject', function ($scope, $http, $modalInstance, items) {
    $scope.op = {};
    $scope.op.CC = true;
    $scope.updateRejection = function (op) {
        if ($scope.op.motivoTipo != '' || $scope.op.motivo != '') {
            $http({
                url: 'php/analista/updateRejection.php',
                method: 'POST',
                data: JSON.stringify({'lead': items, 'op': op})
            }).then(function (answer) {
                window.location.replace('#!/dashboard');
                $modalInstance.close();
            });

        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to attach documents 
 */
angular.module('appAnalist').controller('modalInstanceAnexarDocsA', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items.lead;
    $scope.doc = items.doc;
    $scope.da ={};
    $scope.file = {};
    $scope.novonome='';

    $scope.compressImage = function (event) {
        
        var file = event.target.files[0];
        $scope.file.filename = event.target.files[0]['name'];
        $scope.file.filetype = file.type;
        if(file.type == 'image/jpeg' || file.type=='image/png'){
            ImageTools.resize(file, {
                width: 800, // maximum width
                height: 1000 // maximum height
            }, function(blob, didItResize) {
           //Converter blob to base64
             var reader = new FileReader();
             reader.readAsDataURL(blob); 
                reader.onloadend = function() {
                    $scope.file.base64data = reader.result;                
                  //  console.log($scope.file.base64data);
                };
            }); 
        } else if(file.type=='application/pdf'){
                       //Converter blob to base64
             var reader = new FileReader();
             reader.readAsDataURL(file); 
                reader.onloadend = function() {
                    $scope.file.base64data = reader.result;                
                   // console.log($scope.file.base64data);
                };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };
    
    //Apenas quando faz a anexação dos documentos no momento
    $scope.saveAttachedDoc = function(){
        //guardar o ficheiro na arq_documentação e alterar o cad_docpedida 

          if ($scope.file) {
            //Gravar os dados do formulario
            var obj = {};
            obj.lead = items.lead;
            obj.doc = $scope.doc;
            obj.userId = sessionStorage.userId;
//            obj.fxBase64 = 'data:' + $scope.file.filetype + ';base64,' + $scope.file.base64;
            obj.fxBase64 = $scope.file.base64data;
            obj.nomeFx = $scope.novonome;
            obj.type = ($scope.file.filetype).substr(($scope.file.filetype).indexOf('/') + 1);
            $http({
                url: 'sisleadsrest/cltdocs',
                method: 'POST',
                data: JSON.stringify(obj)
            }).then(function (answer) {
                $modalInstance.close(answer.data);
            });
        } else {
            alert("Tem de selecionar um ficheiro!");
        }
    }

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});



/**
 * Modal instance to list historico
 */
angular.module('appAnalist').controller('modalInstanceHistoricAnalist', function ($scope, $modal, $modalInstance, items) {
    $scope.lista = items;
    $scope.openLeadDetail = function (lead) {

        var modalInstance = $modal.open({
            templateUrl: 'modalHistoricoDetail.html',
            controller: 'modalInstanceHistoricoDetail',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });

    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to enviar email a pedir Documentos
 */
angular.module('appAnalist').controller('modalInstancePedirDoc_', function ($scope, $modalInstance, $http, items) {
    $scope.lead = items.lead;
    $scope.d = {};
    $scope.outroDoc = '';
    //Obter lista de documentação 
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_docnecessaria'
    }).then(function (answer) {
        $scope.docs1 = [];
        $scope.docs2 = [];
        $scope.d = [];
        $scope.d.docs1 = [];
        $scope.d.docs2 = [];
        answer.data.forEach(function (ln) {
            if (ln.titular == 1) {
                $scope.docs1.push(ln);
//                ln.tipocredito == 'T' ? $scope.d.docs1.push(ln) : null;
//                ($scope.tpc = "CC" && ln.tipocredito == 'C') ? $scope.d.docs1.push(ln) : null;
            } else if (items.segProp==1) {
                $scope.docs2.push(ln);
//                ln.tipocredito == 'T' ? $scope.d.docs2.push(ln) : null;
//                ($scope.tpc = "CC" && ln.tipocredito == 'C') ? $scope.d.docs2.push(ln) : null;
            }
        });

    });

    //Enviar o pedido
    $scope.enviarPedidoDoc = function (d) {
        var docs = {};
        if (d.docs2) {
            docs.docs = d.docs1.concat(d.docs2);
        } else {
            docs.docs = d.docs1;
        }
        if (docs) {
            console.log(docs);
            $http({
                url: "php/sendEmailMissingDocsA.php",
                method: 'POST',
                data: JSON.stringify({'lead': $scope.lead, 'docFalta': docs.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
            }).then(function (answer) {
                alert(answer.data);
                $modalInstance.close('OK');
            });
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to attach document Extra
 */
angular.module('appAnalist').controller('modalInstanceAnexarDocsExtraA', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.novonome = "";
    $scope.d = {};
    $scope.file ={};
    $scope.wait = true;
    //obter tipos de documentos
    $http({
        url: "php/getData.php",
        method: "POST",
        data: 'cnf_docnecessaria'
    }).then(function (answer) {
        $scope.docs = answer.data;
    });
    
    $scope.compressImage2 = function (event) {
        
        var file = event.target.files[0];
        console.log(file['name']);
        $scope.file.filename = event.target.files[0]['name'];
        $scope.file.filetype = file.type;
        if(file.type == 'image/jpeg' || file.type=='image/png'){
            ImageTools.resize(file, {
                width: 800, // maximum width
                height: 1000 // maximum height
            }, function(blob, didItResize) {
           //Converter blob to base64
             var reader = new FileReader();
             reader.readAsDataURL(blob); 
                reader.onloadend = function() {
                    $scope.file.base64data = reader.result; 
                    $scope.wait = false;
                    console.log($scope.file.base64data);
                };
            }); 
        } else if(file.type=='application/pdf'){
                       //Converter blob to base64
             var reader = new FileReader();
             reader.readAsDataURL(file); 
                reader.onloadend = function() {
                    $scope.file.base64data = reader.result;
                    $scope.wait = false;
                    console.log($scope.file.base64data);
                };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };
    
    //Atualizar o novoNome
    $scope.upNovoNome = function (d) {
        var novonome = '';
        for (var i = 0; i < d.docs.length; i++) {
            novonome = d.docs[i].sigla;
        }
        $scope.novonome = novonome;
    };

    //Guardar o ficheiro extra
    $scope.saveAttachedDocExtra = function () {

        if ($scope.file && $scope.d.docs[0]) {
            //Gravar os dados do formulario
            var obj = {};
            obj.lead = $scope.lead;
            obj.doc = $scope.d.docs[0];
            obj.doc.tipodoc = $scope.d.docs[0].id;
            obj.userId = sessionStorage.userId;
            
            obj.nomeFx = $scope.file.filename;
            obj.type = ($scope.file.filetype).substr(($scope.file.filetype).indexOf('/') + 1);

            $http({
                url: 'php/saveAttachDocumentExtra.php',
                method: 'POST',
                data: JSON.stringify(obj)
            }).then(function (answer) {
                obj.fxBase64 = $scope.file.base64data;
                obj.doc.linha = answer.data;
                $http({
                    url: 'sisleadsrest/cltdocs',
                    method: 'POST',
                    data: JSON.stringify(obj)
                }).then(function (answer) {
                    $modalInstance.close('answer.data');
                });
            });
        } else {
            alert("Verifique as seleções!");
        }
    };
    
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to change doc name
 */
angular.module('appAnalist').controller('modalInstanceChangeDoc', function ($scope, $http, $modalInstance, items) {
    $scope.d = {};
    $scope.docOrig = items;
    //obter tipos de documentos
    $http({
        url: "php/getData.php",
        method: "POST",
        data: 'cnf_docnecessaria'
    }).then(function (answer) {
        $scope.docs = answer.data;
    });

    $scope.saveChange = function (d) {
        if (d.docs.length == 1) {
            $http({
                url: 'php/changeNameDoc.php',
                method: 'POST',
                data: JSON.stringify({'docOrig': $scope.docOrig, 'docNew': d.docs})
            }).then(function (answer) {
                $modalInstance.close();
            });
        } else {
            alert("Só pode selecionar um!!");
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});



/**
 * Modal instance to list open lead detail
 */
angular.module('appAnalist').controller('modalInstanceHistoricoDetail', function ($scope, $http, $modalInstance, items) {
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
            $scope.rejeicoes = answer.data.rejeicoes;
        });
    }else {
            alert("Atenção! Verifique os dados e tente novamente.");
    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});


// Modal instance para Listar e selecionar simulações guardadas
angular.module('appAnalist').controller('modalInstanceGetSimula', function ($scope, $http, $modalInstance, items) {
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