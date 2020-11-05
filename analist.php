<?php session_start();
    if( !isset($_SESSION['valid_ID']) || $_SESSION['valid_ID']==false ){
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
<html ng-app="appAnalist" style="height: 100%">
    <head>
        <meta charset="UTF-8">
        <title>{{alertaACP}} GestLife - Analista {{flagMural}} </title>
        <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
        <meta name="viewport" content="width=device-width, initial-scale=1">  
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

        <script src="lib/jquery.min.js" type="text/javascript"></script>
        <link href="lib/bootstrap.3.3.7/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="lib/bootstrap.toggle.2.2.0/bootstrap-toggle.css" rel="stylesheet" type="text/css"/>
        <script src="lib/bootstrap3.3.6/bootstrap.min.js" type="text/javascript"></script>
        <link href="lib/fontAwesome.4.7.0/font-awesome.css" rel="stylesheet" type="text/css"/>
        <link href="lib/fontawesome-free-5.0.10/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet" type="text/css"/>
        <script src="lib/angular.1.6.6.min.js" type="text/javascript"></script>
        <script src="lib/angularjs-1.6.6-angular-route.js" type="text/javascript"></script>
        <script src="lib/angular-file-upload.js" type="text/javascript"></script>
        <script src="lib/angular-resource.js" type="text/javascript"></script>
        <script src="lib/angular-animate.min.js" type="text/javascript"></script>
        <script src="lib/angular-sanitize.min.js" type="text/javascript"></script>
        <script src="lib/angular-spinkit.js" type="text/javascript"></script>
        
        <!--a linha a baixo é utilizada para mostrar o modal-->
        <link href="lib/bootstrap.3.3.7/uibootstrap.css" rel="stylesheet" type="text/css"/>
        <script src="lib/ui-bootstrap-tpls-0.12.1.js" type="text/javascript"></script>
        <script src="lib/ng-table.min.js" type="text/javascript"></script>
        <link href="css/css.css" rel="stylesheet" type="text/css"/>
        <script src="lib/pdf.js" type="text/javascript"></script>
        <script src="lib/pdf.worker.js" type="text/javascript"></script>
        <script src="lib/angula-base64-upload.js" type="text/javascript"></script>
        <script src="node_modules/angularjs-scroll-glue/src/scrollglue.js" type="text/javascript"></script>
        <script src="lib/download.js" type="text/javascript"></script>
        <!--graficos-->
        <script src="node_modules/chart.js/dist/Chart.min.js" type="text/javascript"></script>
        <script src="node_modules/angular-chart.js/dist/angular-chart.min.js" type="text/javascript"></script>  
        <!-- Copy to clipboard -->
        <script src="node_modules/clipboard/dist/clipboard.min.js" type="text/javascript"></script>
        <script src="node_modules/ngclipboard/dist/ngclipboard.min.js" type="text/javascript"></script>
        <script src="lib/ImageTools.js" type="text/javascript"></script>
        
        <script src="js/appAnalist.js" type="text/javascript"></script>
        <script src="lib/checklist-model.js" type="text/javascript"></script>
        <script src="js/andashboard.js" type="text/javascript"></script>
        <script src="js/form1.js" type="text/javascript"></script>
        <script src="js/anaListAprovados.js" type="text/javascript"></script>
        <script src="js/anaListPendentes.js" type="text/javascript"></script>
        
        <script src="js/anlistaPesq.js" type="text/javascript"></script>
        <script src="js/adetLead.js" type="text/javascript"></script>
        <script src="js/anaListFin.js" type="text/javascript"></script>
        <script src="js/anaListRec.js" type="text/javascript"></script>
        <script src="js/anaListFinACP.js" type="text/javascript"></script>
        <script src="js/anaListSusp.js" type="text/javascript"></script>
        <script src="cc/js/dashboard.js" type="text/javascript"></script>
        <script src="cc/js/sugerido.js" type="text/javascript"></script>
        <script src="cc/js/aceites.js" type="text/javascript"></script>
        <script src="cc/js/situacaoContratos.js" type="text/javascript"></script>
        <script src="cc/js/listC.js" type="text/javascript"></script>
        <script src="js/cofid.js" type="text/javascript"></script>
        <script src="js/finReport_analista.js" type="text/javascript"></script>
        <script src="js/anlistFinRCP.js" type="text/javascript"></script>
    </head>
    <body ng-controller="annavController">

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
                        <!--empty-->
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="#!user"><span class="glyphicon glyphicon-user"></span> {{userData.nome}}</a></li>
                        <li>
                            <a><span style="font-size: 1.2em; font-weight: 800"><i class="fa fa-chart-line"> </i> {{valorFinanciado}}</span></a>   
                        </li>
                        <li>
                            <a href="#!cc" ng-if="cartoesView"><span style="font-size: 1.2em; font-weight: 800" ng-click="toogle()"><i class="fa fa-credit-card"> </i> Cartões </span></a>   
                            <a href="#!dashboard" ng-if="!cartoesView"><span style="font-size: 1.2em; font-weight: 800" ng-click="toogle()"><i class="fa fa-clipboard"> </i> Leads </span></a>   
                        </li>
                        <li>
                            <a href="#!cofid" title="Listagem dos processos Cofidis Directo"><i class="fa fa-bullseye"> </i> Cofi-D</a>   
                        </li> 
                        <li>
                            <a href="#!/finReport" title="Histórico dos financiamentos"><i class="fa fa-chart-bar"> </i></a>
                        </li>
                        <li>
                            <a href="lib/Manual de credito.pdf" target="_blank" title="Manual do Crédito"><i class="fa fa-question-circle"> </i></a>
                        </li>                              
                        <li>
                            <a href="php/logout.php"><span class="glyphicon glyphicon-log-out"></span> Sair</a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
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
    <div class="modal-footer">
        <div class="text-center">
            <button class="btn btn-warning" ng-click="closeModal()">Fechar</button>
        </div>
    </div>
</script>