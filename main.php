<?php
session_start();
if (!isset($_SESSION['valid_ID']) || $_SESSION['valid_ID'] == false) {
    header('Location: index.php');
    die();
}
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html ng-app="appMain" style="height: 100%">
    <head>
        <meta charset="UTF-8">
        <title>GestLife - Admin {{flagMural}}</title>
        <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
        <meta name="viewport" content="width=device-width, initial-scale=1">   

        <script src="lib/jquery.min.js" type="text/javascript"></script>
        <link href="lib/bootstrap.3.3.7/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="lib/bootstrap.toggle.2.2.0/bootstrap-toggle.css" rel="stylesheet" type="text/css"/>
        <script src="lib/bootstrap3.3.6/bootstrap.min.js" type="text/javascript"></script>
        <link href="lib/fontAwesome.4.7.0/font-awesome.css" rel="stylesheet" type="text/css"/>
        <script src="lib/angular.1.6.6.min.js" type="text/javascript"></script>
        <script src="lib/angularjs-1.6.6-angular-route.js" type="text/javascript"></script>
        <script src="lib/angular-file-upload.js" type="text/javascript"></script>
        <script src="lib/angular-resource.js" type="text/javascript"></script>
        <script src="lib/angular-sanitize.min.js" type="text/javascript"></script>
        <script src="lib/angular-animate.min.js" type="text/javascript"></script>
        <script src="lib/ngMask.min.js" type="text/javascript"></script>
        <!--a linha a baixo é utilizada para mostrar o modal-->
        <link href="lib/bootstrap.3.3.7/uibootstrap.css" rel="stylesheet" type="text/css"/>
        <script src="lib/ui-bootstrap-tpls-0.12.1.js" type="text/javascript"></script>
        <link href="css/css.css" rel="stylesheet" type="text/css"/>
        <script src="lib/pdf.js" type="text/javascript"></script>
        <script src="lib/pdf.worker.js" type="text/javascript"></script>
        <script src="lib/angula-base64-upload.js" type="text/javascript"></script>
        <script src="lib/ng-table.min.js" type="text/javascript"></script>
        <script src="lib/download.js" type="text/javascript"></script>
        <script src="lib/angular-spinkit.js" type="text/javascript"></script>
        <script src="node_modules/chart.js/dist/Chart.min.js" type="text/javascript"></script>
        <script src="node_modules/angular-chart.js/dist/angular-chart.min.js" type="text/javascript"></script>
        <script src="node_modules/angularjs-scroll-glue/src/scrollglue.js" type="text/javascript"></script>
        <!-- Modulos AngularJS proprios -->
        <script src="js/appMain.js" type="text/javascript"></script>
        <script src="js/adashboard.js" type="text/javascript"></script>
        <script src="js/users.js" type="text/javascript"></script>
        <script src="js/suppliers.js" type="text/javascript"></script>
        <script src="js/aListaParceiros.js" type="text/javascript"></script>
        <script src="js/searchNew.js" type="text/javascript"></script>
        <script src="js/chOrder.js" type="text/javascript"></script>
        <script src="js/chGest.js" type="text/javascript"></script>
        <script src="js/chAnalist.js" type="text/javascript"></script>
        <script src="js/admDetLead.js" type="text/javascript"></script>
        <script src="js/filtros.js" type="text/javascript"></script>
<!--        <script src="statistic/js/detLead.js" type="text/javascript"></script>-->
        <script src="statistic/js/estForn.js" type="text/javascript"></script>
        <script src="statistic/js/listA.js" type="text/javascript"></script>
        <script src="statistic/js/list.js" type="text/javascript"></script>
        <script src="statistic/js/estGest.js" type="text/javascript"></script>
        <script src="statistic/js/estAnalist.js" type="text/javascript"></script>
        <script src="statistic/js/listG.js" type="text/javascript"></script>
        <script src="statistic/js/listApv.js" type="text/javascript"></script>
        <script src="statistic/js/listFin.js" type="text/javascript"></script>
        <script src="relatorios/js/relForn.js" type="text/javascript"></script>
        <script src="relatorios/js/relHon.js" type="text/javascript"></script>
        <script src="relatorios/js/relParceiros.js" type="text/javascript"></script>
        <script src="relatorios/js/rDet.js" type="text/javascript"></script>
        <script src="js/listRGPD.js" type="text/javascript"></script>
        <script src="statistic/js/previsoes.js" type="text/javascript"></script>
        <script src="relatorios/js/relatorioLeads.js" type="text/javascript"></script>
        <script src="statistic/js/estRecup.js" type="text/javascript"></script>
        <script src="AUDIT/js/dash.js" type="text/javascript"></script>
        <script src="AUDIT/js/reanalise.js" type="text/javascript"></script>
        <script src="AUDIT/js/byIdades.js" type="text/javascript"></script>
        <script src="AUDIT/js/sitContratos.js" type="text/javascript"></script>
        <script src="AUDIT/js/byVencimentos.js" type="text/javascript"></script>
        <script src="AUDIT/js/desempenho.js" type="text/javascript"></script>
        <script src="AUDIT/js/detalhe.js" type="text/javascript"></script>
        
    </head>
    <body ng-controller="navController">
        <nav class="navbar navbar-default">
            <div class="container">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">
                        <img src="img/logo email.png" height="30px" alt=""/>
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configurações <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#!/users">Utilizadores</a></li>
                                <li><a href="#!/suppliers">Fornecedores de Leads</a></li>
                                <!--<li><a href="#!/reqDocs">Documentação necessária</a></li>-->
                                <li><a href="#!/listParc">Parceiros Financeiros</a></li>
                                <li><a href="#!/filtros">Filtros</a></li>
                            </ul>
                        </li>                
                        <!--Funcionalidades-->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Funcionalidades <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li ><a href="#!/searchNew">Pesquisar em novas</a></li>
                                <li><a href="#!/chGest">Mudar de gestor</a></li>
                                <li><a href="#!/chAnalist">Mudar de analista</a></li>
                                <!--<li><a href="#!/chStatus">Alterar Status</a></li>--> 
                                <li><a href="#!/chOrder">Colocar em Analise</a></li>
                                <li><a href="#!/rgpd">Listar RGPD</a></li>
                            </ul>
                        </li>
                        <!--Estatisticas-->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Estatisticas <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li ><a href="#!/estForn">Fornecedores</a></li>
                                <li><a href="#!/estAnalist">Analistas</a></li>
                                <li><a href="#!/estGest">Gestores</a></li>
                                <li><a href="#!/prev">Previsões</a></li>
                                <li><a href="#!/estRecup">Recuperação</a></li>
                            </ul>
                        </li>
                        <!--Relatorios-->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Relatorios <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li ><a href="#!/relForn">Fornecedores</a></li>
                                <li ><a href="#!/relHon">Honorarios</a></li>
                                <li><a href="#!/relAnalist">Analistas</a></li>
                                <li><a href="#!/relParceiros">Parceiros</a></li>
                                <li><a href="#!/relLeads">Leads</a></li>
                            </ul>
                        </li>
                        <!-- AUDITS-->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-haspopup="true" aria-expanded="false">Auditoria <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                            <li>
                               <a href="#!/audit">Auditoria</a> 
                               <a href="#!/reanalise">Em Reanalise</a> 
                               <a href="#!/ByIdades">Leads por Idades</a> 
                               <a href="#!/ByVencimentos">Leads por Vencimentos</a> 
                               <a href="#!/sitContratos">Situação dos Contratos de Aprovados</a> 
                               <a href="#!/desempenho">Analise de Desempenho</a> 
                               <a href="#!/detalhe">Analise de Detalhada</a> 
                            </li>
                            </ul>
                        </li>
                        <!-- Pesquisa de leads -->
                        <li>
                            <div class="form-group" style="margin-top: 7px">
                                <input type="text" class="form-control" name="pesq" ng-model="pesq" style="width: 80px; display: inline" placeholder="lead" >
                                <a class="btn btn-info" href="#!/detLead/{{pesq}}" ><i class="fa fa-search"></i></a>
                            </div>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#!user"><span class="glyphicon glyphicon-user"></span> {{userData.nome}}</a></li>
                        <li><a href="php/logout.php"><span class="glyphicon glyphicon-log-out"></span> Sair</a></li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <!-- Mural -->
        <button class="btn btn-{{alerta}} btn-sm {{btnMural}}"  ng-click="toogle()">{{accao}}</button>          
        <div ng-class="myClass">
            <div class="form-group">
                <div class="panel" style="height: 330px; background-color: #fff; overflow-y: scroll" scroll-glue-bottom>
                    <!-- Bloco de conversa -->
                    <div ng-repeat="conv in conversas" ng-click="selectDestino(conv)">
                        <div ng-class="conv.sentido" ng-if="clicked != conv.id">
                            <span class="pull-right">
                                <small>{{conv.dataenvio}} 
                                    <strong ng-if="conv.sentido === 'msg-in'"> <- {{conv.userorigem}}</strong>
                                    <strong ng-if="conv.sentido === 'msg-out'"> -> {{conv.userdestino}}</strong>
                                </small>
                            </span> </br> 
                            <p ng-hide="clicked != conv.id">{{conv.assunto}}</p>
                        </div>
                        <div ng-class="conv.sentido" style="border: gray thick solid" ng-if="clicked == conv.id">
                            <span class="pull-right">
                                <small>{{conv.dataenvio}} 
                                    <strong ng-if="conv.sentido === 'msg-in'"> <- {{conv.userorigem}}</strong>
                                    <strong ng-if="conv.sentido === 'msg-out'"> -> {{conv.userdestino}}</strong>
                                </small>
                            </span> </br> 
                            <p ng-show="clicked == conv.id">{{conv.assunto}}</p>                                    
                        </div>
                    </div>

                </div>
                <input class="form-control" type="text" ng-model="assunto"/>
                <div class="col-xs-12">
                    <div class="col-xs-8">
                        <!-- Split button -->
                        <div class="btn-group dropup">
                            <button type="button" class="btn btn-success" ng-click="enviarResposta()">Enviar para</button>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu"  >
                                <li ng-repeat="u in utilizadores| orderBy:'nome' " ng-if="u.id != sessionStorage.userId && u.ativo==1" ng-click="enviarPara(u)"><a ng-if="u.mural == 1" href="" >{{u.nome}} - {{u.tipo}}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Main View-->
        <div ng-view=""></div>
        <!-- SPINNER -->
        <chasing-dots-spinner  ng-show="prograssing"></chasing-dots-spinner>
        <!--Footer-->
        <h2>&nbsp;</h2>
        <footer class="navbar navbar-fixed-bottom bg-info" style="padding-top: 15px;">
            <div class="container text-center">
                <em>Copyright <span class="fa fa-copyright"></span>
                    2018 - GestLife. All rights reserved. Design by 
                    <a href="http://jsalgado.com" target="_blank">JSalgado Unipessoal, Lda</a>
                </em>
            </div>
        </footer>
    </body>
</html>

<!-- Modal para visualizar documentação   -->
<script type="text/ng-template" id="modalViewDoc.html" ng-controller="modalInstanceViewDoc">
    <div class="modal-header bg-info">
    <h3 class="modal-title">{{nomedoc}}
    <span class="closeModal" ng-click="closeModal()">X</span>
    </h3>
    </div>
    <div class="modal-body">
        <iframe ng-if="imagePath" ng-src="{{imagePath}}" width="875px" height="800px"></iframe>
    </div>
    <div class="modla-footer">
    <div class="text-center">
    <button class="btn btn-warning" ng-click="closeModal()">Fechar</button>
    </div>
    </div>
</script>