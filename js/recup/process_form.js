/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appRec').controller('processFormController', function ($scope, $http, $routeParams, $sce) {
    $scope.lead = $routeParams.lead;
    $scope.showDoc = false;
    $scope.titular = "primeiro";
    $scope.firstT ="active";
    $scope.secondT = "";
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
    //Nacionalidades
    $http.get('lib/nacionalidades.json').then(function (answer) {
        $scope.nacionalidades = answer.data;
    });
    //Obter informação do process_form ou do processo
    $http({
        'url': 'php/gestor/getProcessForm.php',
        'method': 'POST',
        'data': $scope.lead
    }).then(function (answer) {
        console.log(answer.data);
        $scope.p = answer.data;
        if(!$scope.p.nacionalidade || $scope.p.nacionalidade==''){
            $scope.p.nacionalidade = 'portuguesa';
        }
        if(!$scope.p.tipodoc || $scope.p.tipodoc==0){
            $scope.p.tipodoc = 1;
        }
        if(!$scope.p.iban || $scope.p.iban==''){
            $scope.p.iban = 'PT50 ';
        }
    });

    //Obter a documentação
    $http({
        'url': 'php/gestor/getDocPedidos.php',
        'method': 'POST',
        'data': $scope.lead
    }).then(function (answer) {
        $scope.docs = answer.data;
    });
    
    //Limpar os dados do segundo titular quando limpa a checkbox
    $scope.clear2TitularDados = function(p){
        
        if(p.segundoproponente==0){
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

    //Guardar e enviar para analise
    $scope.sendToAnalise = function (p) {
        $http({
            'url': 'php/analista/saveProcessForm.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.lead, 'process': p})
        }).then(function (answer) {
            if (answer.data == 'OK') {
                //Send to Analise with full documentation or incomplete
                if (confirm("Vai enviar para a Analise! Pretende continuar?")) {
                    $http({
                        url: 'php/gestor/sendToAnalise.php',
                        method: 'POST',
                        data: JSON.stringify({'lead': $scope.lead, 'gestor': sessionStorage.userData})
                    }).then(function (answer) {
                        if (answer.data != '') {
                            alert(answer.data);
                        }
                        window.location.replace('#!/dashboard');
                    });
                }
            } else {
                alert('Não foi possivel guardar os dados. Lead não foi enviado para analise!');
            }
        });
    }

    //Guardar sem enviar para analise
    $scope.saveProcesso = function (p) {
        $http({
            'url': 'php/analista/saveProcessForm.php',
            'method': 'POST',
            'data': JSON.stringify({'lead': $scope.lead, 'process': p})
        }).then(function (answer) {
            if (answer.data == 'OK') {
                alert('Processo guardado sem enviar para analise');
                window.location.replace('#!/detLead/'+$scope.lead);
            } else {
                alert('Não foi possivel guardar os dados. Lead não foi enviado para analise!');
            }
        });
    }

// Funcionalidades da documentação
    //Ver DOC
    $scope.verDoc = function (doc) {
        doc.lead = $scope.lead;
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
    //Fechar visualização de doc
    $scope.closeShowDoc = function () {
        $scope.showDoc = false;
    };

    //Botão para descarregar um documento (fx)
    $scope.descarregarDoc = function (doc) {
        $http({
            url: 'php/getDocumentacao.php',
            method: 'POST',
            data: JSON.stringify({'lead': $scope.lead, 'linha': doc.linha})
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
            data: JSON.stringify({'lead': $scope.lead})
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

});

