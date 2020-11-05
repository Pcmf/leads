/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module("appRec").directive("ngUploadChange", function () {
    return{
        scope: {
            ngUploadChange: "&"
        },
        link: function ($scope, $element, $attrs) {
            $element.on("change", function (event) {
                $scope.$apply(function () {
                    $scope.ngUploadChange({$event: event})
                })
            })
            $scope.$on("$destroy", function () {
                $element.off();
            });
        }
    }
});

angular.module('appRec').controller('detLeadRecController', function ($scope, $http, $routeParams, $modal) {
    $scope.lead = $routeParams.id;
    $scope.tipoUser = JSON.parse(sessionStorage.userData).tipo;
    $scope.editar = false;
    $scope.readOnly = true;
    $scope.onCall = false;
    $scope.comunicacoes = [];
    $scope.show = false;
    $scope.e = {};
    $scope.sim = {};
    $scope.addNewOR = false;
    $scope.addNewOC = false;
    $scope.addNewSim = false;
    $scope.temHistorico = false;
    $scope.titular = "primeiro";
    $scope.firstT = "active";
    $scope.secondT = "";
    $scope.reverse = true;
    $scope.txEsforco = 0.0;

    // Prazo Taxa
    $scope.prazotaxa = [];
    $scope.c = {};
    $scope.s = {};


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
                sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            });
        } else {
            $scope.onCall = !$scope.onCall;
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            }).then(function (answer) {
                sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            });
        }
    };

    //Button when no answer
    $scope.noAnswer = function (leadId) {

        //Confirm this click
        $scope.onCall = false;
        var param = {};
        param.lead = $scope.p;
        param.user = JSON.parse(sessionStorage.userData);
        if (!($scope.dl.status == 8 || $scope.dl.status == 108 || ($scope.dl.status >= 10 && $scope.status < 100))) {

            if (confirm('Pretende agendar para o dia seguinte? ')) {
                $http({
                    url: 'php/recup/agendamentoAutomaticoRec.php',
                    method: 'POST',
                    data: JSON.stringify(param)
                }).then(function (answer) {
                    console.log(answer.data);
                    //Desligar chamada
                    $http({
                        url: 'restful/makeCall.php',
                        method: 'POST',
                        data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
                    }).then(function (answer) {
                        // alert(answer.data);
                        sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                        window.location.replace('#/dash');
                    });
                });
            }
        }
        // window.location.replace('#/dash');

    };

    $scope.notAtribuited = function (lead) {
        // anular por o numero não estar atribuido
        if (confirm('Vai fechar LEAD como não Atribuido. \nPretende continuar?')) {
            //if the email exist then cal function to send email
            $scope.onCall = false;
            if ($scope.ic.email) {
                var parm = {};
                parm.lead = lead;
                parm.user = JSON.parse(sessionStorage.userData);
                $http({
                    url: 'php/gestor/sendEmailBadContact.php',
                    method: 'POST',
                    data: JSON.stringify(parm)
                }).then(function (answer) {
                    console.log(answer.data);
                    //call function to change lead status to ANULADO (103)
                    updateStatus(103, lead);
                    window.location.replace("");
                });

            }

        }
    }

    //Botão de agendamento
    $scope.agendar = function (lead) {
        //abrir modal para fazer o agendamento
        var modalInstance = $modal.open({
            templateUrl: 'modalAgendamento.html',
            controller: 'modalInstanceAgendamentoRec',
            size: 'sm',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function () {
            sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            window.location.replace('#');
        });
    }

    //Fora do expediente - enviar SMS e Email
    $scope.foraDoExpediente = function () {
        //Confirm this click
        //Agendamento and go to dashboard
        var param = {};
        param.lead = $scope.dl;
        param.user = JSON.parse(sessionStorage.userData);
        $http({
            url: 'php/recup/foraDoExpediente.php',
            method: 'POST',
            data: JSON.stringify(param)
        }).then(function (answer) {
            console.log(answer.data);
        });
        sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
        window.location.replace("");

    };



    //Ativar a lead se esta estiver anulada com documentação pedida
    $scope.ativar = function (lead) {
        var array = ['3', '4', '5', '9', '14', '15', '18', '19', '28', '29', '31', '103', '104', '105', '109'];
        if (array.indexOf(lead.status) > -1 && lead.docpedida) {
            if (confirm("Pretende ativar este processo?")) {
                $http({
                    url: 'php/recup/ativarProcesso.php',
                    method: 'POST',
                    data: JSON.stringify({'user': JSON.parse(sessionStorage.userData), 'lead': lead})
                }).then(function (answer) {
                    if (answer.data) {
                        alert(answer.data);
                    }
                    sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
                    getLeadAllInfo();
                });
            }
        }
    };
    
    // Atualizar o segundo proponente
    $scope.updateSegProp = function(value) {
        $scope.p.segundoproponente = value;
            $http({
                url: 'php/updateSegProp.php',
                method: 'POST',
                data: JSON.stringify({'lead': $scope.lead, 'segprop': value})
            }).then(function (answer) {
                console.log(answer);
            });
    }

    /**
     * Atualizar a outra informação
     * @param {type} lead
     * @param {type} outrainfo
     * @returns {undefined}
     */
    $scope.updateOutraInfo = function (lead, outrainfo) {
        if (outrainfo != '') {
            $http({
                url: 'php/updateOutraInfo.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'outrainfo': outrainfo})
            }).then(function (answer) {
                console.log(answer);
            });
        }
    }

    //calcular a idade
    $scope.calcIdade = function () {
        var today = new Date();
        var year = Number($scope.p.datanascimento.substr(-4));
        $scope.p.idade = today.getFullYear() - year;
        console.log($scope.p.datanascimento);
    }

    //Definição dos tabs
    $scope.tabs = [{
            title: 'Cliente',
            id: 'zero.tpl'
        }, {
            title: 'Informação Financeira',
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
            id: 'seven.tpl'
        }, {
            title: 'Cartões',
            id: 'eight.tpl'
        }, {
            title: 'Comunicações',
            id: 'nine.tpl'
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
        $scope.currentTab = 'one.tpl';
        // sessionStorage.currentTab = 'zero.tpl';
    }

    //Função para navegar nas tabs da listagem
    $scope.onClickTab = function (tab) {
        onClickTabFunc(tab);
    };
    $scope.isActiveTab = function (tabId) {
        return tabId == $scope.currentTab;
    };

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
                    $scope.checkParceiros();
                    $scope.calcularTxEsforco();
                } else {
                    alert(answer.data.msg);
                }
            });
        } else {
            alert("Atenção! Tem que preencher os campos todos");
        }
    }

    //Remover linha de OR
    $scope.removeLnOR = function (ln) {

        $http({
            url: "php/recup/removeLnOR.php",
            method: "POST",
            data: JSON.stringify({'or': ln})
        }).then(function (answer) {
            $scope.rendimentos = $scope.rendimentos.filter((el) => {
                return el.linha != ln.linha
            });
            console.log($scope.rendimentos);
            $scope.checkParceiros();
            $scope.calcularTxEsforco();
        });
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
                    $scope.calcularTxEsforco();
                } else {
                    alert(answer.data.msg);
                }
            });
        } else {
            alert("Atenção! Tem que preencher os campos todos");
        }
    }

    //Remover linha de OC
    $scope.removeLnOC = function (ln) {

        $http({
            url: "php/recup/removeLnOC.php",
            method: "POST",
            data: JSON.stringify({'or': ln})
        }).then(function (answer) {
            $scope.creditos = $scope.creditos.filter((el) => {
                return el.linha != ln.linha
            });
            console.log($scope.creditos);
            $scope.calcularTxEsforco();
        });
    }

    //Guardar  linha de Simulação para Email
    $scope.saveLinhaSim = function (sim) {
        if (sim.tipocredito && sim.valor && sim.prazo) {
            $http({
                url: "php/recup/saveSimulaEmail.php",
                method: "POST",
                data: JSON.stringify({'lead': $scope.dl.id, 'sim': sim, 'gestor': sessionStorage.userId})
            }).then(function (answer) {
                $scope.simulacoesEmail = answer.data;
                $scope.addNewSim = false;
            });
        } else {
            alert("Atenção! Tem que preencher os campos todos");
        }
    }


    //Processo 
    $scope.saveProcesso = function (p) {
        
        $http({
            'url': 'php/recup/saveProcessForm.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.dl.id, 'process': p})
        }).then(function (answer) {
            if (answer.data == 'OK') {
                $scope.s.segundoproponente = $scope.p.segundoproponente;
//                alert("Guardado");
                if (p.segundoproponente == 1 && $scope.titular == 'primeiro') {
                    window.scrollTo(0, 80);
                   $scope.firstT ='';
                   $scope.secondT = 'active';
                    $scope.titular = 'segundo';
                } else {
                    window.scrollTo(0, 80);
                    $scope.onClickTab({
                        title: 'Documentos',
                        id: 'two.tpl'
                    });
                }
                
            } else {
                alert("Atenção!!! Não gravou!");
            }
        });

    }


    $scope.saveInfoFin = function (ic, f, p) {
        console.log(ic);
       console.log(f);
        console.log(p);

        ic.diaprestacao = $scope.p.diaprestacao
        f.vencimento = p.vencimento;
        f.vencimento2 = p.vencimento2;
        f.venc_cetelem = p.venc_cetelem;
        f.venc_cetelem2 = p.venc_cetelem2;
        f.valorhabitacao = p.valorhabitacao;
       
        $http({
            url: "php/recup/saveInfoFin.php",
            method: "POST",
            data: JSON.stringify({'lead': $scope.dl.id, 'ic': ic, 'fin': f, 'gestor': sessionStorage.userId})
        }).then(function (answer) {
            console.log(answer.data);
        });
        // passa para o tab do cliente
        window.scrollTo(0,80);
        $scope.onClickTab({title: 'Cliente', id: 'zero.tpl' });
    }

    // TX DE ESFORÇO
    $scope.calcularTxEsforco = function () {
        $scope.txEsforco = 0;
        var vencimento = +$scope.p.vencimento;
        if ($scope.s.segundoproponente == 1) {
            vencimento += +$scope.p.vencimento2;
        }
        if (!$scope.p.valorhabitacao) {
            $scope.p.valorhabitacao = 0;
        }
        custosGerais = +$scope.p.valorhabitacao + +$scope.s.prestacaopretendida;

        // Outros Creditos
        var valorOC = $scope.calcOC()
        // Outros Rendimentos
        var valorOR = $scope.calcOR();

        // Calulo da tx
        $scope.txEsforco = ((custosGerais + valorOC) / (vencimento + valorOR)) * 100;

        $scope.txEsforco <= 65 ? $scope.progressColor = 'progress-bar-success' : $scope.progressColor = 'progress-bar-danger';
    }


    // Calcular Outros Rendimentos
    $scope.calcOR = function () {
        var valorOR = 0;
        if ($scope.rendimentos) {
            var result = [];
            var keys = Object.keys($scope.rendimentos);
            keys.forEach(function (key) {
                result.push($scope.rendimentos[key]);
            });
            if (result.length >= 0) {
                result.forEach(function (ln) {
                    if(ln.usar) {
                        ln.periocidade == 'Ano' ? valorOR += +(ln.valorrendimento / 12) : valorOR += +ln.valorrendimento;
                    }
                });
            }
        }
        return valorOR;
    }

    // Calcular Outros Creditos
    $scope.calcOC = function () {
        var resp = 0;
        if ($scope.creditos) {
            var result = [];
            var keys = Object.keys($scope.creditos);
            keys.forEach(function (key) {
                result.push($scope.creditos[key]);
            });
            if (result.length >= 0) {
                result.forEach(function (ln) {
                    if (ln.liquidar==0) {
                        resp += +ln.prestacao;
                    }
                });
            }
        }
        return resp;
    }



    // Calculo de Simulações para enviar por email
    $scope.calcSimulaEmail = function (ln) {
        console.table(ln);
        var ptxline = $scope.prazotaxa.filter((el) => {
            if (ln.tipocredito == el.tipocredito && ln.prazo >= el.prazo && ln.prazo <= el.prazotop)
            {
                return el;
            }
        })[0];
        console.table(ptxline);
        var prestacao = +(+ln.valor / ((1 - Math.pow((1 + (+ptxline.taxa / 100) / 12), -ln.prazo)) / ((+ptxline.taxa / 100) / 12))).toFixed(2);
        console.log(prestacao);
        $scope.sim.prestacao = prestacao;
    }

    $scope.calcPrestacao = function (ln) {
        console.table(ln);
        var ptxline = $scope.prazotaxa.filter((el) => {
            if (ln.tipocredito == el.tipocredito && ln.prazopretendido >= el.prazo && ln.prazopretendido <= el.prazotop)
            {
                return el;
            }
        })[0];

        var prestacao = +(+ln.valorpretendido / ((1 - Math.pow((1 + (+ptxline.taxa / 100) / 12), -ln.prazopretendido)) / ((+ptxline.taxa / 100) / 12))).toFixed(2);
        console.log(prestacao);
        $scope.s.prestacaopretendida = prestacao;
    }


    //Agendar BP 22
    $scope.agendaBP22 = function (lead) {
        if (lead) {
            if (confirm("Vai agendar para o proximo dia 22. Pretende continuar?")) {
                $http({
                    url: 'php/gestor/agendamentoBP22.php',
                    method: 'POST',
                    data: JSON.stringify({'lead': lead, 'userId': sessionStorage.userId})
                }).then(function (answer) {
                    //  console.log(answer.data);
                    window.location.replace("");
                });
            }
        }
    }
    //Anexar documento
    $scope.anexarDoc = function (d, lead) {
        //open modal to attach documentation
        var parm = {};
        parm.lead = lead;
        parm.doc = d;
        parm.op = "Anexar";
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocsPesq.html',
            controller: 'modalInstanceAnexarDocsPesq',
            size: 'lg',
            resolve: {items: function () {
                    return parm;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    };
    // Merge Documento a pdf existente
    $scope.mergeDoc = function (doc) {
        //open modal to attach documentation
        var parm = {};
        parm.lead = doc.lead;
        parm.doc = doc;
        parm.op = "Juntar";
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocsPesq.html',
            controller: 'modalInstanceMergeDocs',
            size: 'lg',
            resolve: {items: function () {
                    return parm;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    };

    //Anexar Doc Extra
    $scope.anexarDocExtra = function () {

        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarDocsExtra.html',
            controller: 'modalInstanceAnexarDocsExtra',
            size: 'lg',
            resolve: {items: function () {
                    return $scope.lead;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    };
    //Ver documentação
    $scope.verDoc = function (doc) {  //doc inclui o lead id
        //open modal to view documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewDoc',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    };
    //RGPD - Eliminar dados
    $scope.delRGPD = function (lead) {
        if (confirm("Vai eliminar os dados pessoais para esta LEAD. Pretende continuar?")) {
            $http({
                url: 'php/delRGPD.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'user': sessionStorage.userId})
            }).then(function (answer) {
                alert("Toda a informação pessoal foi eleminada!");
                window.location.replace('#!/dashboard');
            });
        }
    };
    //Rejeitar LEAD
    $scope.rejeitarLead = function (lead) {
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
        sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
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
            //   console.log(JSON.stringify(doc));
            if (doc.tipo == 'jpg') {
                download("data:image/jpg;base64," + doc.fx64, doc.nomefx);
            }
            if (doc.tipo == 'jpeg') {
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
    $scope.descarregarDocs = function (lead) {
        $http({
            url: 'php/getDocumentacao.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead})
        }).then(function (answer) {
            answer.data.forEach(function (ln) {
                if (ln.tipo == 'jpg') {
                    download("data:image/jpg;base64," + ln.fx64, ln.nomefx);
                }
                if (ln.tipo == 'jpeg') {
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

    //Editar - altera o readonly para false
    $scope.editarLead = function () {
        $scope.editar = true;
        $scope.readOnly = false;
    };

    //Guardar as alterações
    $scope.gravarLead = function (lead) {
        $scope.editar = false;
        $scope.readOnly = true;
        //atualizar o processo
        var parm = {};
        parm.lead = lead;
        parm.user = JSON.parse(sessionStorage.userData);
        if (!$scope.ic.segundoproponente || $scope.ic.segundoproponente == 0) {
            $scope.ic.nome2 = null;
            $scope.ic.parentesco2 = null;
            $scope.ic.telefone2 = null;
            $scope.ic.nif2 = null;
            $scope.ic.idade2 = null;
            $scope.ic.profissao2 = null;
            $scope.p.vencimento2 = null;
            $scope.ic.tipocontrato2 = null;
            $scope.ic.inicio2 = null;
            $scope.ic.mesmahabitacao = null;
        }
        if ($scope.mesmaHabitacao) {
            $scope.ic.mesmahabitacao = 'Sim';
            $scope.ic.tipohabitacao2 = null;
            $scope.ic.valorhabitacao2 = null;
            $scope.ic.declarada2 = null;
            $scope.ic.anoiniciohabitacao2 = null;
        } else {
            $scope.ic.mesmahabitacao = '';
        }
        parm.ic = $scope.ic;
        $http({
            url: 'php/gestor/editarProcesso.php',
            method: 'POST',
            data: JSON.stringify(parm)
        }).then(function (answer) {
            //alert(answer.data);
        });
    };

    //Abrir uma nova janela para mostrar o process-form
    $scope.processForm = function (lead) {
        window.location.replace('#!/processForm/' + lead);
    }

    //Enviar para a Analise
    $scope.envParaAnalise = function (lead) {
        //Send to Analise with full documentation or incomplete
        if (confirm("Vai enviar para a Analise! Pretende continuar?")) {
            $http({
                url: 'php/gestor/sendToAnalise.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'gestor': sessionStorage.userData})
            }).then(function (answer) {
                if (answer.data != '') {
                    alert(answer.data);
                }
                window.location.replace('#!/dashboard');
            });
        }
        sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
    };
    //Pedir Documentação em falta
    $scope.pedirDoc = function (lead, p, s) {
        lead.tipocredito = s.tipocredito;
        lead.email = p.email;
        lead.segundoproponente = s.segundoproponente;
        //abrir modal com lista de documentação a pedir
        var modalInstance = $modal.open({
            templateUrl: 'modalPedirDoc.html',
            controller: 'modalInstancePedirDoc',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function (answer) {
            //getLeadAllInfo($routeParams.id);
            sessionStorage.turn === 'N' ? sessionStorage.turn = 'A' : sessionStorage.turn = 'N';
            window.location.replace('#!/gdashboard');
        });
    };
    //Documentação OK - quando está a aguardar documentação vai colocar como pendente
    $scope.docsOk = function (lead) {
        $http({
            url: 'php/analista/updateStatusAnalista.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'status': 22})
        }).then(function (answer) {
            //  getLeadAllInfo($routeParams.id);
            window.history.back(-1);
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

    //Botão para descarregar o CONTRATO para o ambiente de trabalho
    $scope.descarregarContrato = function (c) {
        download("data:application/pdf;base64," + c.fx64, c.nome);
    };
    //Ver Contrato
    $scope.verContrato = function (doc) {
        var modalInstance = $modal.open({
            templateUrl: 'modalViewDoc.html',
            controller: 'modalInstanceViewContratoG',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function () {
            // getLeadAllInfo();
        });
    };

    //Botão para descarregar o comprovativo para o ambiente de trabalho
    $scope.descarregarComprovativo = function (c) {
        if (c.tipodoc == 'jpg') {
            download("data:image/jpeg;base64," + c.documento, c.nomedoc);
        } else {
            download("data:application/pdf;base64," + c.documento, c.nomedoc);
        }
    };
    //Ver Comprovativo
    $scope.verComprovativo = function (doc) {
        var modalInstance = $modal.open({
            templateUrl: 'modalViewComp1.html',
            controller: 'modalInstanceViewComp1',
            size: 'lg',
            resolve: {items: function () {
                    return doc;
                }
            }
        });
        modalInstance.result.then(function () {
            // getLeadAllInfo();
        });
    };

    //Comunicações enviar email
    $scope.enviarComunicacao = function (e) {
        if (e.assunto && e.texto) {
            $http({
                url: 'php/sendComunicacao.php',
                method: 'POST',
                data: JSON.stringify({'lead': $scope.lead, 'e': e, 'tipo': 'G'})
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

    //Button to open modal to view Historico
    $scope.showHistorico = function (lead) {
        //Validate fields
        var obj = {};
        obj.leads = $scope.listaHistorico;
        obj.lead = $scope.lead;
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
    $scope.getSimulacoes = function (lead) {
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
            $scope.s = {};
            $scope.p.vencimento = answer.vencimento;
            $scope.p.vencimento2 = answer.vencimento2;
            $scope.p.venc_cetelem = answer.venc_cetelem;
            $scope.p.venc_cetelem2 = answer.venc_cetelem2;
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
    /**
     * Function to update LEAD status and register contact
     * @param {int} status  
     * @param {obj) lead 
     * @returns {undefined}
     */
    function updateStatus(status, lead) {
        var param = {};
        param.lead = lead;
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

    //Obter todos os dados da LEAD/Processo
    function getLeadAllInfo() {
        if ($scope.lead) {
            //Estados Civil
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
            //Relaçoes familiares
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_relacaofamiliar'
            }).then(function (answer) {
                $scope.relacoesfamiliares = answer.data;
            });
            //Nacionalidades
            $http.get('lib/nacionalidades.json').then(function (answer) {
                $scope.nacionalidades = answer.data;
            });
            //Tipos de contrato
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_sitprofissional'
            }).then(function (answer) {
                $scope.tiposcontrato = answer.data;
            });
            //Tipo Habitação
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_tipohabitacao'
            }).then(function (answer) {
                $scope.tiposhabitacao = answer.data;
            });
            //Tabela Prazo Taxa
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_prazotaxa'
            }).then(function (answer) {
                $scope.prazotaxa = answer.data;
            });
            //Comunicações
            $http({
                url: 'php/getComunicacoes.php',
                method: 'POST',
                data: $scope.lead
            }).then(function (answer) {
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
                url: 'php/recup/getLeadAllInfo.php',
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
                $scope.contratos = answer.data.contratos;
                $scope.calculos = answer.data.calculos;
                $scope.s = answer.data.simula;
                $scope.s.prazopretendido = $scope.ic.prazopretendido;
                $scope.s.prestacaopretendida = $scope.ic.prestacaopretendida;
                $scope.s.segundoproponente = $scope.ic.segundoproponente;
                $scope.p = answer.data.processo;
                $scope.listaHistorico = answer.data.historic;
                $scope.simulacoesEmail = answer.data.simulaemail;
                if ($scope.listaHistorico.length > 0) {
                    $scope.temHistorico = true;
                }
                //Obter informação da aplicação das regras dos parceiros
                $scope.checkParceiros();

                if ($scope.ic.mesmahabitacao == 'Sim') {
                    $scope.mesmaHabitacao = true;
                } else {
                    $scope.mesmaHabitacao = false;
                }
                $scope.rejeicoes = '';
                (answer.data.rejeicoes).forEach(function (ln) {
                    $scope.rejeicoes += ln.data + ' -->   ' + ln.motivo + ';   ' + ln.obs + ';  ' + ln.outro + '\n';
                });

                $scope.cc = answer.data.cc;


            });

            getComprovativos($scope.lead);
        } else {
            alert("Atenção! Verifique os dados e tente novamente.");
        }
    }
    //Checa parceiros 
    $scope.checkParceiros = function () {
        $scope.calcularTxEsforco();
        console.log($scope.s.segundoproponente);
        if (!$scope.s.segundoproponente || $scope.s.segundoproponente == 0) {
            $scope.RLiq = +$scope.p.vencimento + +$scope.calcOR();
            $scope.RLiqCt = +$scope.p.venc_cetelem + +$scope.calcOR();
        } else {
            $scope.RLiq = +$scope.p.vencimento + +$scope.p.vencimento2 + +$scope.calcOR();
            $scope.RLiqCt = +$scope.p.venc_cetelem + +$scope.p.venc_cetelem2 + +$scope.calcOR();
        }


        $scope.despesa = +$scope.ic.valorhabitacao + +$scope.calcOC();

        //Calculos da tx de esforço e validação das regras
        $scope.parceirosChk = [];

        $scope.regras.forEach(function (ln) {
            ln['motivo'] = "";
            //Validar tipo de credito, valor pretendido, prazo, idade
            if ($scope.s.tipocredito == ln.tipocredito) {
                if (($scope.p.idade < +ln.idade_min) || ($scope.p.idade > +ln.idade_max)) {
                    ln['motivo'] = "Idade do cliente";
                } else if (($scope.s.prazopretendido < +ln.prazo_min) || ($scope.s.prazopretendido > +ln.prazo_max)) {
                    ln['motivo'] = "Prazo";
                } else if ((+$scope.s.valorpretendido < +ln.montante_min) || (+$scope.s.valorpretendido > +ln.montante_max)) {
                    ln['motivo'] = "Montante pedido";
                } else if ($scope.s.segundoproponente == 0 && ($scope.p.vencimento < +ln.vencimento_1t) && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 1º titular";
                } else if ((+$scope.p.venc_cetelem < +ln.vencimento_1t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento 14/12 1º titular";
                } else if ($scope.s.segundoproponente == 1 && (+$scope.p.venc_cetelem2 < +ln.vencimento_2t) && +ln.indice_rl > 1) {
                    ln['motivo'] = "Vencimento 14/12 2º titular";
                } else if ($scope.s.segundoproponente == 1 && (+$scope.p.vencimento2 < +ln.vencimento_2t)
                        && ((+$scope.p.vencimento + +$scope.p.vencimento2) < +ln.soma_venc) && ln.indice_rl == '1.00') {
                    ln['motivo'] = "Vencimento 2º titular";
                } else if ((+$scope.p.vencimento + +$scope.p.vencimento2) < +ln.soma_venc) {
                    ln['motivo'] = "Vencimento(s) com valor inferior ao exigido (" + ln.soma_venc + ")";
                } else {
                    //Calculos 
                    $scope.RL = 0;

                    if (!$scope.s.segundoproponente || $scope.s.segundoproponente == 0) {
                        //só um titular
                        if (+ln.indice_rl > 1) {
                            if (ln.tipocredito == 'CHCC') {
                                $scope.RL = +$scope.p.venc_cetelem;
                            } else {
                                $scope.RL = +$scope.p.venc_cetelem + +$scope.ic.outrosrendimentos;
                            }
                        } else {
                            $scope.RL = +$scope.p.vencimento + +$scope.ic.outrosrendimentos;
                        }
                    } else {
                        // dois titulares
                        if (+ln.indice_rl > 1) {
                            if (ln.tipocredito == 'CHCC') {
                                $scope.RL = +$scope.p.venc_cetelem + +$scope.p.venc_cetelem2;
                            } else {
                                $scope.RL = +$scope.p.venc_cetelem + +$scope.p.venc_cetelem2 + +$scope.ic.outrosrendimentos;
                            }
                        } else {
                            $scope.RL = +$scope.p.vencimento + +$scope.p.vencimento2 + +$scope.ic.outrosrendimentos;
                        }
                    }

                    //Taxa de esfoço
                    ln['txEsf'] = Math.round(((+$scope.ic.valorhabitacao + +$scope.ic.outroscreditos + +$scope.s.prestacaopretendida + (+$scope.ic.filhos * +ln.filhos)) / (+$scope.RL * +ln.indice_rl)) * 100);
                    //Disponibilidade Orçamental
                    if (ln['parceiro' != 7]) {
                        // Calculo normal
                        ln['disp'] = Math.round(+$scope.RL - (+$scope.ic.valorhabitacao + +$scope.s.prestacaopretendida + +$scope.ic.outroscreditos + (+$scope.ic.filhos * +ln.filhos) + +ln.disp_orcamental));
                    } else {
                        var DispOrcUnicre = 0;
                        // Calculo de disponibilidade para UNICRE - depende do tipo de habitação
                        if ($scope.ic.tipohabitacao == 1) {  // tipo habitação
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


    function getComprovativos(lead) {
        $http({
            url: 'php/analista/getComprovativosList.php',
            method: 'POST',
            data: lead
        }).then(function (answer) {
            $scope.comprovativos = answer.data;
        });
    }
});


/**
 * Modal instance to select required documents, how to send and ETA 
 */
angular.module('appRec').controller('modalInstancePedirDoc', function ($scope, $rootScope, $http, $modalInstance, items) {
    $scope.lead = items;
    console.log($scope.lead);
    $scope.d = {};
    $scope.m = {};

    $scope.e = {};
    $scope.outroDoc = '';

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
        $scope.docs1 = [];
        $scope.docs2 = [];
        $scope.d = [];
        $scope.d.docs1 = [];
        $scope.d.docs2 = [];
        answer.data.forEach(function (ln) {
            if (ln.titular == 1) {
                $scope.docs1.push(ln);
                ln.tipocredito == 'T' ? $scope.d.docs1.push(ln) : null;
                ($scope.lead.tipocredito == "CC" && ln.tipocredito == 'C') ? $scope.d.docs1.push(ln) : null;
                (($scope.lead.tipocredito == "CHCC" || $scope.lead.tipocredito == "CH1" || $scope.lead.tipocredito == "CH2")
                        && ln.tipocredito == 'H') ? $scope.d.docs1.push(ln) : null;
            } else if ($scope.lead.segundoproponente == 1) {
                $scope.docs2.push(ln);
                ln.tipocredito == 'T' ? $scope.d.docs2.push(ln) : null;
                ($scope.lead.tipocredito == "CC" && ln.tipocredito == 'C') ? $scope.d.docs2.push(ln) : null;
            }
        });
    });


    $scope.saveProcess = function (d, m, e) {
        //Validar os dados do formulario do modal
        //Documentação - pelo menos um documento
        var erro = false;
        if (d.docs1 == undefined || d.docs1.length == 0) {
            alert('Atenção! Não selecionou nenhum tipo de documento!');
            //erro = true;
        }
        var docs = {};
        if (d.docs2) {
            docs.docs = d.docs1.concat(d.docs2);
        } else {
            docs.docs = d.docs1;
        }
        if (!erro) {
            //Gravar os dados do formulario
            $rootScope.prograssing = true;
            //Pedir a documentação selecionada
            console.log(docs.docs);
            $http({
                url: "php/recup/sendEmailMissingDocsA.php",
                method: 'POST',
                data: JSON.stringify({'lead': $scope.lead.id, 'docFalta': docs.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
            }).then(function (answer) {
                alert(answer.data);
                $modalInstance.close('OK');
                $rootScope.prograssing = false;
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
angular.module('appRec').controller('modalInstanceAnexarDocsPesq', function ($scope, $http, $modalInstance, items) {

    $scope.lead = items.lead;
    $scope.doc = items.doc;
    $scope.da = {};
    $scope.file = {};
    $scope.novonome = '';
    $scope.maxFileSize = 4000000;
    $scope.wait = true;

    $scope.compressImage = function (event) {

        var file = event.target.files[0];
        //   console.log(file['name']);
        $scope.file.filename = event.target.files[0]['name'];
        $scope.file.filetype = file.type;
        if (file.type == 'image/jpeg' || file.type == 'image/png') {
            ImageTools.resize(file, {
                width: 800, // maximum width
                height: 1000 // maximum height
            }, function (blob, didItResize) {
                //Converter blob to base64
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    $scope.file.base64data = reader.result;
                    $scope.wait = false;
                    //  console.log($scope.file.base64data);
                };
            });
        } else if (file.type == 'application/pdf') {
            //Converter blob to base64
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function () {
                $scope.file.base64data = reader.result;
                $scope.wait = false;
                //   console.log($scope.file.base64data);
            };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };


    //Apenas quando faz a anexação dos documentos no momento
    $scope.saveAttachedDoc = function () {
        //guardar o ficheiro na arq_documentação e alterar o cad_docpedida 
        if ($scope.file && !$scope.wait) {
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
            alert("Tem de selecionar um ficheiro ou aguardar que carregue.");
        }
    };



    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to Merge document to other that exists  
 */
angular.module('appRec').controller('modalInstanceMergeDocs', function ($scope, $http, $modalInstance, items) {

    $scope.lead = items.lead;
    $scope.doc = items.doc;
    console.table(items.doc);
    $scope.da = {};
    $scope.file = {};
    $scope.maxFileSize = 4000000;
    $scope.wait = true;

    $scope.compressImage = function (event) {

        var file = event.target.files[0];
        //   console.log(file['name']);
        $scope.file.filename = event.target.files[0]['name'];
        $scope.file.filetype = file.type;
        if (file.type == 'image/jpeg' || file.type == 'image/png') {
            ImageTools.resize(file, {
                width: 800, // maximum width
                height: 1000 // maximum height
            }, function (blob, didItResize) {
                //Converter blob to base64
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    $scope.file.base64data = reader.result;
                    $scope.wait = false;
                    //  console.log($scope.file.base64data);
                };
            });
        } else if (file.type == 'application/pdf') {
            //Converter blob to base64
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function () {
                $scope.file.base64data = reader.result;
                $scope.wait = false;
                //   console.log($scope.file.base64data);
            };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };


    //Apenas quando faz a anexação dos documentos no momento
    $scope.saveAttachedDoc = function () {
        //guardar o ficheiro na arq_documentação e alterar o cad_docpedida 
        if ($scope.file && !$scope.wait) {
            //Gravar os dados do formulario
            var obj = {};
            obj.lead = items.lead;
            obj.doc = $scope.doc;
            obj.userId = sessionStorage.userId;
//            obj.fxBase64 = 'data:' + $scope.file.filetype + ';base64,' + $scope.file.base64;
            obj.fxBase64 = $scope.file.base64data;
            obj.nomeFx = $scope.novonome;
            obj.type = ($scope.file.filetype).substr(($scope.file.filetype).indexOf('/') + 1);
            obj.op = "Merge";
            $http({
                url: 'sisleadsrest/cltdocs',
                method: 'POST',
                data: JSON.stringify(obj)
            }).then(function (answer) {
                $modalInstance.close(answer.data);
            });
        } else {
            alert("Tem de selecionar um ficheiro ou aguardar que carregue.");
        }
    };



    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to view document. 
 */
angular.module('appRec').controller('modalInstanceViewDoc', function ($scope, $http, $modalInstance, items, $timeout, $rootScope, $sce) {
    $scope.nomedoc = items.nomedoc;


    $rootScope.prograssing = true;
    $http({
        url: 'php/getDocBase64.php',
        method: 'POST',
        data: JSON.stringify(items)
    }).then(function (answer) {
        var fx64 = answer.data.fx64.replace(/[^\x20-\x7E]/gmi, '');
        if (answer.data.tipo == 'jpg') {
            console.log('JPG: ' + answer.data.tipo);
            $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + answer.data.fx64);
        } else if (answer.data.tipo == 'jpeg') {
            console.log('JPEG: ' + answer.data.tipo);
            $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpeg;base64,' + answer.data.fx64);
        } else {
            console.log('PDF: ' + fx64);
            $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + fx64);
        }
        $rootScope.prograssing = false;
    });


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };


});

/**
 * Modal instance to attach documents
 */
angular.module('appRec').controller('modalInstanceAnexarDocsExtra', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.novonome = "";
    $scope.d = {};
    $scope.file = {};
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
        //    console.log(file['name']);
        $scope.file.filename = event.target.files[0]['name'];
        $scope.file.filetype = file.type;
        if (file.type == 'image/jpeg' || file.type == 'image/png') {
            ImageTools.resize(file, {
                width: 800, // maximum width
                height: 1000 // maximum height
            }, function (blob, didItResize) {
                //Converter blob to base64
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    $scope.file.base64data = reader.result;
                    $scope.wait = false;
                    //        console.log($scope.file.base64data);
                };
            });
        } else if (file.type == 'application/pdf') {
            //Converter blob to base64
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function () {
                $scope.file.base64data = reader.result;
                $scope.wait = false;
                //       console.log($scope.file.base64data);
            };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };


    //Atualizar o novoNome
    $scope.upNovoNome = function (d) {
        var novonome = '';
        for (var i = 0; i < d.docs.length; i++) {
            novonome += d.docs[i].sigla + '_';
        }
        $scope.novonome = novonome;
    };

    //Guardar o ficheiro extra
    $scope.saveAttachedDocExtra = function () {


        if ($scope.file && !$scope.wait && $scope.d.docs[0]) {
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
 * Modal instance to register Rejection
 */
angular.module('appRec').controller('modalInstanceRejeitar', function ($scope, $http, $modalInstance, items) {
    $scope.m = {};
    $scope.rejeitar = function () {
        if (!$scope.r) {
            alert("Tem de selecionar um motivo ou descrever!");
        } else {
            var param = {};
            param.user = JSON.parse(sessionStorage.userData);
            param.lead = items;
            param.motivo = $scope.r;
            $http({
                url: 'php/recup/registarRejeicao.php',
                method: 'POST',
                data: JSON.stringify(param)
            }).then(function (answer) {
                console.log(answer);
            });
            window.location.replace("");
        }
    };
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to enviar email a pedir Documentos
 */
angular.module('appRec').controller('modalInstancePedirDoc2', function ($scope, $modalInstance, $http, items) {
    $scope.lead = items;
    $scope.d = {};
    $scope.outroDoc = '';
    //Obter lista de documentação 
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cnf_docnecessaria'
    }).then(function (answer) {
        $scope.docs = answer.data;

        //Pedir a documentação selecionada
        $scope.enviarPedidoDoc = function (d) {
            if (d) {
                $http({
                    url: "php/recup/sendEmailMissingDocsA.php",
                    method: 'POST',
                    data: JSON.stringify({'lead': $scope.lead, 'docFalta': d.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
                }).then(function (answer) {
                    alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
        //Enviar o pedido da documentação em falta
        $scope.enviarPedidoDocEmFalta = function (lead, d) {
            if (d) {
                $http({
                    url: "php/recup/sendEmailMissingDocsA_1.php",
                    method: 'POST',
                    data: JSON.stringify({'lead': lead, 'docFalta': d.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
                }).then(function (answer) {
                    alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
    });


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to change doc name
 */
angular.module('appRec').controller('modalInstanceChangeDoc', function ($scope, $http, $modalInstance, items) {
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
 * Modal instance to view Comprovativo. 
 */
angular.module('appRec').controller('modalInstanceViewComp1', function ($scope, $modalInstance, items, $sce) {
    $scope.nomedoc = items.instituicao;
    if (items.tipodoc === "jpg") {
        $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + items.documento);
    } else {
        $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.documento);
    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to view CONTRATO. 
 */
angular.module('appRec').controller('modalInstanceViewContratoG', function ($scope, $rootScope, $modalInstance, items, $sce) {
    $scope.nomedoc = items.nome;
    $rootScope.prograssing = true;
    $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.fx64);
    $rootScope.prograssing = false;
    //Obter o base64 para a lead e linha que está no doc


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance para fazer o agendamento
 */
angular.module('appRec').controller('modalInstanceAgendamentoRec', function ($scope, $http, $modalInstance, items) {

    $scope.ag = {};
    $scope.ag.lead = items;
    $scope.ag.user = sessionStorage.userId;

    $scope.saveAgendamento = function (ag) {
        if (ag.data) {
            var dia = ag.data.getDate();
            var mes = ag.data.getMonth() + 1;
            var ano = ag.data.getFullYear();
            ag.data = (ano + '-' + mes + '-' + dia).toLocaleString();
        } else {
            ag.data = null;
        }
        $http({
            url: 'php/recup/agendamentoNoDetalhe.php',
            method: 'POST',
            data: JSON.stringify(ag)
        }).then(function (answer) {
            console.log(answer.data);
            $modalInstance.close('OK');
        });
    }


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

/**
 * Modal instance to list historico de leads
 */
angular.module('appRec').controller('modalInstanceHistorico', function ($scope, $http, $modal, $modalInstance, items) {
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

    $scope.anularRepetida = function () {
        $http({
            url: 'php/gestor/anulaRepetida.php',
            method: 'POST',
            data: JSON.stringify({'lead': items.lead, 'user': sessionStorage.userId})
        }).then(function (answer) {
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
angular.module('appRec').controller('modalInstanceHistoricoLeadsDetail', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.readOnly = true;
    if ($scope.lead) {
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
    } else {
        alert("Atenção! Verifique os dados e tente novamente.");
    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

// Modal instance para Listar e selecionar simulações guardadas
angular.module('appRec').controller('modalInstanceGetSimulaDet', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.sim = {};
    $http({
        url: 'php/analista/getSimulacoes.php',
        method: 'POST',
        data: $scope.lead
    }).then(function (answer) {
        $scope.simulacoes = answer.data;
    });

    $scope.selectSimula = function (s) {
        $modalInstance.close(s);
    }



    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});