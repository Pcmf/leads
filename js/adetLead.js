/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module("appAnalist").directive("ngUploadChange", function () {
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

angular.module('appAnalist').controller('detLeadController', function ($scope, $http, $routeParams, $modal) {
    $scope.lead = $routeParams.id;
    $scope.tipoUser = JSON.parse(sessionStorage.userData).tipo;
    $scope.editar = false;
    $scope.readOnly = true;
    $scope.comunicacoes = [];
    $scope.show = false;
    $scope.e = {};
    $scope.addNewOR = false;
    $scope.addNewOC = false;
    $scope.titular = "primeiro";
    $scope.firstT = "active";
    $scope.secondT = "";
    $scope.reverse = true;
    //Obter todos os dados da LEAD/Processo
    getLeadAllInfo();


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
            id: 'four.tpl',
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
            title: 'Cartão C.',
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
        $scope.currentTab = 'zero.tpl';
    }

    //Função para navegar nas tabs da listagem de requisições
    $scope.onClickTab = function (tab) {
        onClickTabFunc(tab);
    };
    $scope.isActiveTab = function (tabId) {
        return tabId == $scope.currentTab;
    };

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
        // Atualizar o registo de contactos
        $http({
            url: 'php/getRegistoContactos.php',
            method: 'POST',
            data: JSON.stringify({"lead": lead})
        }).then(function (answer) {
            $scope.contactos = answer.data;
        });

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
                if (answer.data.results[0].error) {
                    alert(answer.data.results[0].error);
                }
//                TODO
                user = JSON.parse(sessionStorage.userData);
                var sms = "Para podermos efetuar uma analise ao seu pedido de credito, indique-nos qual a melhor hora para contacto. " + user.email;
                $http({
                    url: 'php/sendSMS.php',
                    method: 'POST',
                    data: JSON.stringify({"user": user.id, "telefone": $scope.ic.telefone, "lead": lead, 'sms': sms})
                }).then(function (answer) {
                    console.log(answer.data);
                      // Atualizar o registo de contactos
                    $http({
                        url: 'php/getRegistoContactos.php',
                        method: 'POST',
                        data: JSON.stringify({"lead": lead})
                    }).then(function (answer) {
                        $scope.contactos = answer.data;
                    });      

                });
            });
        });

    };

    //Botão de agendamento
    $scope.agendar = function (lead) {
        //abrir modal para fazer o agendamento
        var modalInstance = $modal.open({
            templateUrl: 'modalAgendamento.html',
            controller: 'modalInstanceAgendamento',
            size: 'sm',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    }



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
    //Save Processo 
    $scope.saveProcesso = function (p) {
        $http({
            'url': 'php/analista/saveProcessForm.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.dl.id, 'process': p})
        }).then(function (answer) {
            if (answer.data == 'OK') {
                alert("Guardado");
            } else {
                alert("Atenção!!! Não gravou!");
            }
        });
    }

    //Ver documentação
    $scope.verDoc = function (doc) {  //doc inclui o lead id
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
        modalInstance.result.then(function () {
            getLeadAllInfo();
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
    $scope.descarregarDocs = function (lead) {
        $http({
            url: 'php/getDocumentacao.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead})
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
    //Alterar o status para RE-Analise (20)
    $scope.changeToReanalise = function (lead) {
        if (confirm("Pretende por esta LEAD em Re-analise?")) {
            $http({
                url: 'php/updateLeadStatus.php',
                method: 'POST',
                data: JSON.stringify({'userId': $scope.dl.user, 'status': 20, 'lead': lead})
            }).then(function () {
                window.location.replace("#!/dashboard");
            });
        }
    };
    //Pedir Documentação em falta
    $scope.pedirDoc = function (lead) {
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
    //Anexar documento
    $scope.anexarDoc = function (d, lead) {
        //open modal to attach documentation
        var parm = {};
        parm.lead = lead;
        parm.doc = d;
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
            controller: 'modalInstanceViewContrato',
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
    //Botãop para remover o contrato
    $scope.removerContrato = function (ctr) {
        if (confirm("Atenção!!\nVai remover o contrato.\nPretende continuar?")) {
            $http({
                url: 'php/analista/removerContrato.php',
                method: 'POST',
                data: JSON.stringify(ctr)
            }).then(function (answer) {
                getLeadAllInfo();
            })
        }
    };
    //Anexar Contrato
    $scope.anexarContrato = function (lead) {
        //open modal to attach contrato
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarContrato2.html',
            controller: 'modalInstanceAnexarContrato2',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
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



    //RGPD - Eliminar dados
    $scope.delRGPD = function (lead) {
        if (confirm("Vai eliminar os dados pessoais para esta LEAD. Pretende continuar?")) {
            $http({
                url: 'php/delRGPD.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'user': sessionStorage.userId})
            }).then(function (answer) {
                alert("Toda a informação pessoal foi eleminada!");
                window.location.replace('#!/adashboard');
            });
        }
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
            $scope.ic.vencimento2 = null;
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

    //Comunicações enviar email
    $scope.enviarComunicacao = function (e) {
        if (e.assunto && e.texto) {
            $http({
                url: 'php/sendComunicacao.php',
                method: 'POST',
                data: JSON.stringify({'lead': $scope.lead, 'e': e, 'tipo': 'A'})
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
    }
    
         
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
            //Estados Civil
            $http({
                url: 'php/getData.php',
                method: 'POST',
                data: 'cnf_sitfamiliar'
            }).then(function (answer) {
                $scope.estadoscivis = answer.data;
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
                $scope.calculos = answer.data.calculos;
                $scope.contratos = answer.data.contratos;
                $scope.s = answer.data.simula;
                $scope.p = answer.data.processo;
                $scope.cc = answer.data.cc;

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
                // Obter o histórico
                checkHistorico($scope.lead, $scope.ic.telefone, $scope.ic.email, $scope.ic.nif);
            });
            getComprovativos($scope.lead);
        } else {
            alert("Atenção! Verifique os dados e tente outra vez.");
        }

    }
    //Função para verificar se existem lead em aberto para o cliente
    function checkHistorico(lead, telefone, email, nif) {
        $http({
            url: 'php/gestor/getHistorico.php',
            method: 'POST',
            data: JSON.stringify({'lead': lead, 'telefone': telefone, 'email': email, 'nif': nif})
        }).then(function (answer) {
            $scope.listaHistorico = answer.data;
            if ($scope.listaHistorico.length > 0) {
                $scope.temHistorico = true;
            } else {
                $scope.temHistorico = false;
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
            console.log('Teste ' + (+$scope.ic.vencimento + +$scope.ic.vencimento2) );
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
                        && ((+$scope.ic.vencimento + +$scope.ic.vencimento2) < +ln.soma_venc) && ln.indice_rl == '1.00') {
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
});

/**
 * Modal instance to view document. 
 */
angular.module('appAnalist').controller('modalInstanceViewDoc', function ($scope, $rootScope, $http, $modalInstance, items, $timeout, $sce) {
    $scope.nomedoc = items.nomedoc;
    $scope.show = false;
    $rootScope.prograssing = true;
    $http({
        url: 'php/getDocBase64.php',
        method: 'POST',
        data: JSON.stringify(items)
    }).then(function (answer) {

        if (answer.data.tipo == 'jpg') {
            $scope.imagePath = $sce.trustAsResourceUrl('data:image/jpg;base64,' + answer.data.fx64);
        } else {
            $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + answer.data.fx64);
        }
        $rootScope.prograssing = false;
    });

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };


});

/**
 * Modal instance to view CONTRATO. 
 */
angular.module('appAnalist').controller('modalInstanceViewContrato', function ($scope, $rootScope, $modalInstance, items, $sce) {
    $scope.nomedoc = items.nome;
    console.log(items);
    $rootScope.prograssing = true;
    $scope.imagePath = $sce.trustAsResourceUrl('data:application/pdf;base64,' + items.fx64);
    $rootScope.prograssing = false;
    //Obter o base64 para a lead e linha que está no doc


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to attach documents 
 */
angular.module('appAnalist').controller('modalInstanceAnexarDocs', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items.lead;
    $scope.doc = items.doc;
    $scope.da = {};
    $scope.file = {};
    $scope.novonome = '';

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
                    //  console.log($scope.file.base64data);
                };
            });
        } else if (file.type == 'application/pdf') {
            //Converter blob to base64
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function () {
                $scope.file.base64data = reader.result;
                //   console.log($scope.file.base64data);
            };
        } else {
            alert("Este tipo de ficheiro não é aceite! Somente JPG, PNG ou PDF");
        }
    };

    //Apenas quando faz a anexação dos documentos no momento
    $scope.saveAttachedDoc = function () {
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

        $scope.closeModal = function () {
            $modalInstance.dismiss('Cancel');
        };

    };
});
/**
 * Modal instance to enviar email a pedir Documentos
 */
angular.module('appAnalist').controller('modalInstancePedirDoc', function ($scope, $modalInstance, $http, items) {
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
        //validações
        $scope.enviarPedidoDoc = function (d) {
            if (d) {
                $http({
                    url: "php/sendEmailMissingDocsA.php",
                    method: 'POST',
                    data: JSON.stringify({'lead': $scope.lead, 'docFalta': d.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
                }).then(function (answer) {
                    //       alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
        //Enviar o pedido

    });


    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});
/**
 * Modal instance to attach documents
 */
angular.module('appAnalist').controller('modalInstanceAnexarDocsExtra', function ($scope, $http, $modalInstance, items) {
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
            novonome = d.docs[i].sigla;
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
 * Modal instance to attach document 
 */
angular.module('appAnalist').controller('modalInstanceAnexarDocsPesq', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items.lead;
    $scope.doc = items.doc;
    $scope.da = {};
    $scope.file = {};
    $scope.novonome = '';
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
            alert("Tem de selecionar um ficheiro! ou aguardar que carregue.");
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});
/**
 * Modal instance to register Rejection
 */
angular.module('appAnalist').controller('modalInstanceRejeitar', function ($scope, $http, $modalInstance, items) {
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
                url: 'php/registarRejeicao.php',
                method: 'POST',
                data: JSON.stringify(param)
            }).then(function (answer) {
                alert(answer);
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
angular.module('appAnalist').controller('modalInstancePedirDoc', function ($scope, $modalInstance, $http, items) {
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
        //validações
        $scope.enviarPedidoDoc = function (d) {
            if (d) {
                $http({
                    url: "php/sendEmailMissingDocsA.php",
                    method: 'POST',
                    data: JSON.stringify({'lead': $scope.lead, 'docFalta': d.docs, 'outroDoc': $scope.outroDoc, 'user': JSON.parse(sessionStorage.userData)})
                }).then(function (answer) {
                    //       alert(answer.data);
                    $modalInstance.close('OK');
                });
            }
        };
        //Enviar o pedido

    });


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
 * Modal instance to attach Comprovativos
 */
angular.module('appAnalist').controller('modalInstanceAnexarComprovativo', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items.lead;
    $scope.linha = items.linha;


    $scope.saveAttachedComprovativo = function () {
        if ($scope.file) {
            if ($scope.file.filetype != 'application/pdf') {
                alert("Apenas é permitida a anexação de PDF!");
            } else {
                //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.linha = $scope.linha;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url: 'php/analista/saveComprovativo.php',
                    method: 'POST',
                    data: JSON.stringify(parm)
                }).then(function (answer) {
                    $modalInstance.close();
                });
            }
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to attach Contrato
 */
angular.module('appAnalist').controller('modalInstanceAnexarContrato2', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;

    $scope.anexarContrato = function () {
        if ($scope.file) {
            if ($scope.file.filetype != 'application/pdf') {
                alert("Apenas é permitida a anexação de PDF!");
            } else {
                //se for contrato
                var parm = {};
                parm.lead = $scope.lead;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url: 'php/analista/attachContrato.php',
                    method: 'POST',
                    data: JSON.stringify(parm)
                }).then(function (answer) {
                    $modalInstance.close();
                });
            }
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});


/**
 * Modal instance to view Comprovativo. 
 */
angular.module('appAnalist').controller('modalInstanceViewComp1', function ($scope, $modalInstance, items, $sce) {
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
 * Modal instance to list historico de leads
 */
angular.module('appAnalist').controller('modalInstanceHistorico', function ($scope, $http, $modal, $modalInstance, items) {
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

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});

/**
 * Modal instance to list open lead detail
 */
angular.module('appAnalist').controller('modalInstanceHistoricoLeadsDetail', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    if ($scope.lead) {
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
    } else {
        alert("Atenção! Verifique os dados e tente novamente.");
    }
    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});


// Modal instance para Listar e selecionar simulações guardadas
angular.module('appAnalist').controller('modalInstanceGetSimulaDet', function ($scope, $http, $modalInstance, items) {
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