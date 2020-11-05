/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
angular.module('appGest').controller('gdashboardController', function ($scope, $http, $interval, $rootScope) {
    $rootScope.saldo = 0;
    $rootScope.showTx = false;
    $scope.n = 4;
    $scope.t = 1.3;
    $scope.s = {};
    $scope.alerta = "default";

    $scope.userData = JSON.parse(sessionStorage.userData);




    //Get parceiros
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cad_parceiros'
    }).then(function (answer) {
        $scope.parceiros = answer.data;
    });


    //Get utilizadores
    $http({
        url: 'php/getData.php',
        method: 'POST',
        data: 'cad_utilizadores'
    }).then(function (answer) {
        $scope.utilizadores = answer.data;
    });
    //Get LEADS information
    getInfo();
    $interval(getInfo, 60000);

    $scope.puxarLead = function () {
        $http({
            url: 'php/gestor/getPushedLead_2.php',
            method: 'POST',
            data: JSON.stringify({'user': $scope.userData, 'turn': sessionStorage.turn})
        }).then(function (answer) {
            console.log(answer.data);
            if (answer.data > 0) {
                sessionStorage.turn == 'N' ? sessionStorage.turn='A' : sessionStorage.turn = 'N';
                window.location.replace('#!/detLead/' + answer.data);
            } else {
                alert('Não há leads para puxar!');
            }
        });
    }


//Mural
    $scope.selectDestino = function (conv) {
        if (!$scope.clicked || $scope.clicked != conv.id) {
            $scope.clicked = conv.id;
            $scope.destino = conv.origem;
        } else {
            $scope.clicked = 0;
            $scope.destino = 0;
        }
        $rootScope.flagMural = "";
        $scope.alerta = "default";
    }
    //Botão enviar para
    $scope.enviarPara = function (u) {
        conversa = {'id': '', 'origem': sessionStorage.userId, 'destino': u.id, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto': '', 'status': 0, 'sentido': 'msg-out'};
        $http({
            url: 'php/enviarParaMural.php',
            method: 'POST',
            data: JSON.stringify(conversa)
        }).then(function (answer) {
            getInfo();
        });
        $scope.assunto = '';
        $scope.clicked = 0;
    }
    //Enviar resposta para o selecionado
    $scope.enviarResposta = function () {
        if ($scope.clicked && $scope.clicked != sessionStorage.userId) {
            conversa = {'id': '', 'origem': sessionStorage.userId, 'destino': $scope.destino, 'assunto': $scope.assunto, 'dataenvio': '', 'datavisto': '', 'status': 0, 'sentido': 'msg-out'};
            $http({
                url: 'php/enviarParaMural.php',
                method: 'POST',
                data: JSON.stringify(conversa)
            }).then(function (answer) {
                getInfo();
            });
            $scope.assunto = '';
            $scope.clicked = 0;
        }
    }



    function getInfo() {
        $rootScope.flagMural = "";
        //Get LEADS information
        $http({
            url: 'php/gestor/dashboardInfo.php',
            method: 'POST',
            data: sessionStorage.userData
        }).then(function (answer) {
            // se for Gestor de Recuperação 
            if ($scope.userData.tipo == 'GRec') {
                $http({
                    url: 'php/gestor/gestRecDashInfo.php',
                    method: 'POST',
                    data: sessionStorage.userData
                }).then(function (answerRec) {
                    $scope.recnovas = answerRec.data.novas;
                    $scope.recativa = answerRec.data.ativa;
                    $scope.recagenda = answerRec.data.agenda;
                });
            }

            $scope.novas = answer.data.novas;
            $scope.ativa = answer.data.ativa;
            $scope.ativas = answer.data.ativas;
            $scope.agendadas = answer.data.agendadas;
            $scope.aguardaDoc = answer.data.aguardaDoc;
            $scope.agDocAnalist = answer.data.agDocAnalist;
            $scope.chegouDoc = answer.data.chegouDoc;
            $scope.atrasadaDoc = answer.data.atrasadaDoc;
            $scope.atrasadaDocParcial = answer.data.atrasadaDocParcial;
            $scope.portalClient = answer.data.portalClient;
            $scope.portalClientDocRec = answer.data.portalClientDocRec;
            $scope.bpsDocs = answer.data.bpsDocs;
            $scope.anuladasGestor = answer.data.anuladasGestor;
            $scope.anuladasNaoAtende = answer.data.anuladasNaoAtende;
            $scope.anuladasFaltaDoc = answer.data.anuladasFaltaDoc;
            $scope.anuladas = answer.data.anuladas;
            $scope.tentativas = answer.data.tentativas;
            $scope.contactados = answer.data.contactados;
            $scope.sucesso = answer.data.sucesso;
            $scope.agendaAtiva = answer.data.agendaAtiva;
            $scope.agendaDoc = answer.data.agendaDoc;
            $rootScope.valorFinanciado = answer.data.valorFinanciado;
            $rootScope.financiados = answer.data.financiados;

            $rootScope.speedup = answer.data.speedup;


            $scope.agendadasAtivas = +$scope.agendaAtiva + +$scope.agendaDoc;
            //Mural
            $scope.conversas = answer.data.conversas;
            var convArray = answer.data.conversas;
            $rootScope.flagMural = "";
            var now = Date.now();
            for (var i = convArray.length - 1; i > 0; i--) {
                if (convArray[i].sentido == "msg-in" && Math.floor((now - Date.parse(convArray[i].dataenvio)) / 60000) < 2) {
                    $rootScope.flagMural = "[msg]";
                    $scope.alerta = "danger";
                    break;
                }
            }
            //Verificar se são 18horas - se forem chama rotina para enviar emails 
            //para lembrar os clientes de enviarem  a documentação. 
            var d = new Date();
            var h = d.getHours();

        });
    }


    $scope.searchLead = function (s) {
        if (!s.lead && !s.nome && !s.telefone && !s.email && !s.nif && !s.process && !s.parceiro && !s.leadorig) {
            alert("Tem de preencher pelo menos um campo!");
        } else {
            window.location.replace("#!/listPesq/" + JSON.stringify(s));
        }

    };

    //Clear fields
    $scope.clearSearch = function () {
        $scope.s = {};
    };




});

