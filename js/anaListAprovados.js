/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appAnalist').controller('anaListAprController', function ($scope, $http, $modal) {
    $scope.resultados = {};
    //Get submetidos
    getResultados();

    //Modal para selecionar forma de envio do contrato para o cliente
    $scope.enviarContratoCliente = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalEnvioContrato.html',
            controller: 'modalInstanceEnvioContrato',
            size: 'md'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    };

    //Abrir modal para perguntar se envia 2 via ou para o parceiro
    $scope.enviarContrato = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalEnvioSegundaVia.html',
            controller: 'modalInstanceEnvioSegundaVia',
            size: 'md'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    }

    //Abrir modal para alterar a forma de envio
    $scope.changeForma = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalFormaEnvio.html',
            controller: 'modalInstanceFormaEnvio',
            size: 'md'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    }

    //Abrir modal para Ver e ou alterar os dados de financiamento
    $scope.changeFinanc = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalChangeFinanciamento.html',
            controller: 'modalInstanceChangeFinanciamento',
            size: 'lg'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    }

    //Enviar para o parceiro
    $scope.enviarContratoParceiro = function (p) {
        if (confirm("Enviar para o parceiro?")) {
            $http({
                url: 'php/analista/updateEnvioParceiro.php',
                method: 'POST',
                data: JSON.stringify({'process': p})
            }).then(function (answer) {
                getResultados();
            });
        }
    }

    //Finalizar o processo
    $scope.registarFinal = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalFinalizeFin.html',
            controller: 'modalInstanceFinalizeFin',
            size: 'lg'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    }

    //Registar Desistencia
    $scope.desistencia = function (p) {
        var modalInstance = $modal.open({
            templateUrl: 'modalDesistencia.html',
            controller: 'modalInstanceDesistencia',
            size: 'md'
            , resolve: {items: function () {
                    return p;
                }
            }
        });
        modalInstance.result.then(function () {
            getResultados();
        });
    }

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
    //Botão para Anexar Documento
    $scope.upContrato = function (lead) {
        //open modal to attach documentation
        var modalInstance = $modal.open({
            templateUrl: 'modalAnexarContrato.html',
            controller: 'modalInstanceAnexarContrato',
            size: 'lg',
            resolve: {items: function () {
                    return lead;
                }
            }
        });
        modalInstance.result.then(function () {
            getLeadAllInfo();
        });
    };
    //Botão para descarregar todos os CONTRATOS para o ambiente de trabalho
    $scope.downContratos = function (lead) {
        $http({
            url: 'php/analista/getContratos.php',
            method: 'POST',
            data: lead
        }).then(function (answer) {
            if (answer.data == '') {
                alert("Este processo não tem contratos!");
            } else {
                answer.data.forEach(function (ln) {
                    if (ln.tipo == 'pdf') {
                        download("data:application/pdf;base64," + ln.fx64, ln.nome);
                    } else {
                        alert("Este ficheiro não está no formato correto. Entre em contacto com o suporte!");
                    }
                });
            }
        });
    };
    
    //Imprimir envelope
    $scope.imprimir = function(lead){
        $http({
            url: 'php/analista/imprimirEnvelope.php',
            method: 'POST',
            data:lead
        }).then(function(answer){
           //  alert("Vai abrir uma janela com folha para imprimir!");
             window.open(answer.data);
        });
    }
    /**
     * Colocar a lead num novo status 
     * @param {type} lead
     * @return {undefined}
     */
    $scope.suspender = function(lead){
        if(confirm("Vai colocar esta lead em suspenso. Continuar?")){
            $http({
                url: 'php/analista/suspenderLeadAprovada.php',
                method: 'POST',
                data: JSON.stringify({'lead': lead, 'status':41})
            }).then(function(answer){
                getResultados();
            });
        }            
    }

    //FUNCTIONS
    function getResultados() {
        $http({
            url: 'php/analista/anaGetAprovados.php',
            method: 'POST',
            data: sessionStorage.userId
        }).then(function (answer) {
            $scope.resultados = answer.data;
        });


    }
});

//MODAL instance controller para registar a forma de envio do contrato para o cliente
angular.module('appAnalist').controller('modalInstanceEnvioContrato', function ($scope, $http, $modalInstance, items) {
    $scope.onCall = false;
    $scope.contactedByG = 'btn btn-info';
    $scope.situacaoChamada='';
    
    console.log(items);

    $scope.updateInfo = function () {
        if ($scope.envContrato) {
            $http({
                url: 'php/analista/updateFormaEnvio.php',
                method: 'POST',
                data: JSON.stringify({'process': items, 'formEnv': $scope.envContrato, 'outraInfo': $scope.outraInfo})
            }).then(function (answer) {
                $modalInstance.close();
            });
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
    
    $scope.contactedByGestor = function(){
        $scope.contactedByG = 'btn btn-success';
        $http({
                url: 'php/registoContacto.php',
                method: 'POST',
                data: JSON.stringify({"user": sessionStorage.userId, "lead": items.lead, 'motivo': 31})   //31 -motivo: contactado pelo gestor
            });
    }
  
     //Button para fazer a chamada
    $scope.makeCall = function () {
        
        if (!$scope.onCall) {
            $scope.onCall = !$scope.onCall;
            $scope.situacaoChamada="Ligação / Tentativa";
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": items.telefone, "lead": items.lead})
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
    $scope.noAnswer = function () {
         $scope.onCall = false;
         $scope.situacaoChamada="Não atendeu";
        var param = {};
        param.lead = items.lead;
        param.user = JSON.parse(sessionStorage.userData);
        $http({
            url: 'php/gestor/detLeadNaoAtende.php',
            method: 'POST',
            data: JSON.stringify(param)
        }).then(function (answer) {
            // Terminar a chamada
            $http({
                url: 'restful/makeCall.php',
                method: 'POST',
                data: JSON.stringify({"user": JSON.parse(sessionStorage.userData), "telefone": 0, "lead": 0})
            });
            // Enviar a SMS
            user = JSON.parse(sessionStorage.userData);
            var sms = "O seu pedido de credito ja se encontra aprovado, precisamos de falar consigo. " + user.email;
            $http({
                url: 'php/sendSMS.php',
                method: 'POST',
                data: JSON.stringify({"user": user.id, "telefone": items.telefone, "lead": items.lead, 'sms': sms})
            }).then(function (answer) {
                console.log(answer.data);
            });
        });
       

    };
    
    
    

});

//MODAL instance controller para finalizar o processo de financiamento
angular.module('appAnalist').controller('modalInstanceFinalizeFin', function ($scope, $http, $modalInstance, items) {
    $scope.c = {};
    $scope.op = {};
    $scope.updateFinal = function () {
        if ($scope.stsFin == 23 && ($scope.c.comp == undefined || $scope.c.comp[0].instituicao == undefined || $scope.c.comp[0].tipo == undefined || $scope.c.comp[0].montante == undefined)) {
            alert("Tem de preencher os campos do comprovativo ");
        } else if ($scope.stsFin == 5 && ($scope.op == undefined || $scope.op.motivoTipo == undefined)) {
            alert("Tem de selecionar um motivo");
        } else {
            $http({
                url: 'php/analista/updateFinal.php',
                method: 'POST',
                data: JSON.stringify({'process': items, 'status': $scope.stsFin, 'outraInfo': $scope.outraInfo, 'cpag': $scope.c, 'opRej': $scope.op})
            }).then(function (answer) {
                $modalInstance.close();
            });
        }
    };

    //Comprovativos  
    var cp = [];
    cp.push({});
    $scope.comprovativos = cp;
    $scope.addLineComprovativos = function () {
        cp.push({});
        $scope.comprovativos = cp;
    };
    $scope.removeLineComprovativos = function () {
        cp.pop();
    };



    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});


//MODAL instance controller para registar Desistencia do cliente
angular.module('appAnalist').controller('modalInstanceDesistencia', function ($scope, $http, $modalInstance, items) {

    $scope.updateInfo = function () {
        $http({
            url: 'php/analista/updateDesistencia.php',
            method: 'POST',
            data: JSON.stringify({'lead': items, 'motivo': $scope.motivo})
        }).then(function (answer) {
            $modalInstance.close();
        });
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

//MODAL instance controller para registar o envio para o parceiro ou uma segunda via para o cliente
angular.module('appAnalist').controller('modalInstanceEnvioSegundaVia', function ($scope, $http, $modalInstance, items) {

    $scope.updateInfo = function () {
        if ($scope.envContrato == 'segundaVia') {
            $http({
                url: 'php/analista/updateEnvio2Via.php',
                method: 'POST',
                data: JSON.stringify({'process': items, 'outraInfo': $scope.outraInfo})
            }).then(function (answer) {
                $modalInstance.close();
            });
        }
        if ($scope.envContrato == 'Parceiro') {
            $http({
                url: 'php/analista/updateEnvioParceiro.php',
                method: 'POST',
                data: JSON.stringify({'process': items})
            }).then(function (answer) {
                $modalInstance.close();
            });
        }
        if ($scope.envContrato == 'incompleto') {
            $http({
                url: 'php/analista/updateIncompleto.php',
                method: 'POST',
                data: JSON.stringify({'process': items})
            }).then(function (answer) {
                $modalInstance.close();
            });
        }
    };

    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };

});

//MODAL instance controller para alterar os dados do financiamento
angular.module('appAnalist').controller('modalInstanceChangeFinanciamento', function ($scope, $http, $modalInstance, items) {

    $scope.sbm = items;
    var oldF = items;
    $scope.justificacao = '';


    $scope.saveChanges = function (f) {
        var opc=true;
        if (f.status != 6 && f.dtcliente) {
            opc = confirm("ATENÇÃO! O contrato já foi enviado para o cliente. Pretende continuar?");
        }
        if (opc) {
            if ($scope.justificacao) {
                $http({
                    url: 'php/analista/saveChangesFinaciamento.php',
                    method: 'POST',
                    data: JSON.stringify({'f': f, 'justif': $scope.justificacao, 'user': JSON.parse(sessionStorage.userData), 'oldF': oldF})
                }).then(function (answer) {
                    $modalInstance.close();
                });
            } else {
                alert("Tem de preencher uma justificação");
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
angular.module('appAnalist').controller('modalInstanceAnexarContrato', function ($scope, $http, $modalInstance, items) {
    $scope.lead = items;
    $scope.novonome = "";


    $scope.saveAttachedDoc = function () {
        if ($scope.file && $scope.c.tipodoc) {
            //se for contrato
            if ($scope.c.tipodoc == "1") {
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
            //se for documento vai iserir como diversos
            if ($scope.c.tipodoc == "2") {
                var parm = {};
                parm.lead = $scope.lead;
                parm.file = $scope.file;
                parm.novonome = $scope.novonome;
                $http({
                    url: 'php/saveAttachDocumentExtra.php',
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
 * Modal instance para alterar a forma de envio
 */
angular.module('appAnalist').controller('modalInstanceFormaEnvio', function ($scope, $http, $modalInstance, items) {
    $scope.envContrato = '';
    $scope.changeForma = function () {
        $http({
            url: 'php/analista/changeFormaEnvio.php',
            method: 'POST',
            data: JSON.stringify({'p': items, 'formaEnv': $scope.envContrato})
        }).then(function (answer) {
            $modalInstance.close();
        });
    };



    $scope.closeModal = function () {
        $modalInstance.dismiss('Cancel');
    };
});