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
<html ng-app="appRec" style="height: 100%">
    <head>
        <meta charset="UTF-8">
        <title>GestLife - Gestor {{flagMural}}</title>
        <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
        <meta name="viewport" content="width=device-width, initial-scale=1">  
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <!--<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">-->
        <link href="css/ng-table.min.css" rel="stylesheet" type="text/css"/>
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
        <script src="lib/download.js" type="text/javascript"></script>
        
        <!--a linha a baixo é utilizada para mostrar o modal-->
        <link href="lib/bootstrap.3.3.7/uibootstrap.css" rel="stylesheet" type="text/css"/>
        <script src="lib/ui-bootstrap-tpls-0.12.1.js" type="text/javascript"></script>
        
        <link href="css/css.css" rel="stylesheet" type="text/css"/>
        <script src="lib/pdf.js" type="text/javascript"></script>
        <script src="lib/pdf.worker.js" type="text/javascript"></script>
        <script src="lib/angula-base64-upload.js" type="text/javascript"></script>
        <script src="lib/ng-table.min.js" type="text/javascript"></script>
        <script src="node_modules/angularjs-scroll-glue/src/scrollglue.js" type="text/javascript"></script>
        <script src="lib/ImageTools.js" type="text/javascript"></script>
        <!--graficos-->
        <script src="node_modules/chart.js/dist/Chart.min.js" type="text/javascript"></script>
        <script src="node_modules/angular-chart.js/dist/angular-chart.min.js" type="text/javascript"></script>
        <!-- input MASK -->
        <script src="lib/ngMask.min.js" type="text/javascript"></script>
        <!-- Modulos AngularJS proprios -->
        <script src="js/recup/appRec.js" type="text/javascript"></script>
        <script src="lib/checklist-model.js" type="text/javascript"></script>
        <script src="js/recup/gcreateLead.js" type="text/javascript"></script>
        <script src="js/recup/gdashboard.js" type="text/javascript"></script>
<!--        <script src="js/Recup/gcontact.js" type="text/javascript"></script>-->
<!--        <script src="js/gcontact.js" type="text/javascript"></script>-->
        <script src="js/recup/gdocs.js" type="text/javascript"></script>
        <script src="js/recup/gdocDet.js" type="text/javascript"></script>
        <script src="js/recup/glistaPesq.js" type="text/javascript"></script>
        <script src="js/recup/ganuladas.js" type="text/javascript"></script>
        <script src="js/recup/gagendadas.js" type="text/javascript"></script>
        <script src="js/recup/detLeadRec.js" type="text/javascript"></script>
        <script src="js/recup/listFin.js" type="text/javascript"></script>
        <script src="js/recup/glist.js" type="text/javascript"></script>
        <script src="js/recup/finReport.js" type="text/javascript"></script>
        <script src="js/recup/callAskForDoc.js" type="text/javascript"></script>
        <script src="js/recup/gPortal.js" type="text/javascript"></script>
        <script src="js/recup/process_form.js" type="text/javascript"></script>
        <script src="js/recup/searchNew.js" type="text/javascript"></script>
        <script src="js/recup/chGest.js" type="text/javascript"></script>
        <script src="js/recup/gcreateLead_1.js" type="text/javascript"></script>
        
    </head>
    <body ng-controller="gnavController">
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
                   
                    <!-- Lado direito -->
                    <ul class="nav navbar-nav navbar-right">
                        <!--Funcionalidades-->
                        <li class="dropdown">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Funcionalidades <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li ><a href="#!/searchNew">Pesquisar em novas</a></li>
                                <li><a href="#!/chGest">Mudar de gestor</a></li>
<!--                                <li><a href="#!/chAnalist">Mudar de analista</a></li>-->
                                <!--<li><a href="#!/chStatus">Alterar Status</a></li>--> 
<!--                                <li><a href="#!/chOrder">Colocar em Analise</a></li>
                                <li><a href="#!/rgpd">Listar RGPD</a></li>-->
                            </ul>
                        </li>
                        <li><a href="#!/user"><span class="glyphicon glyphicon-user"></span>Recuperação: {{userData.nome}}</a></li>
                        <li>
                            <a href="#!/listFin"><span style="font-size: 1.2em; font-weight: 800">{{financiados}} - <i class="fa fa-chart-line"> </i> {{valorFinanciado}}</span></a>   
                        </li>
                        <li>
                            <a href="#!/new"><i class="fa fa-file-alt"> </i> Criar Lead</a>
                        </li>
                        <li>
                            <a href="#!/portal"><i class="fa fa-lock"> </i> Portal Cliente</a>
                        </li>                        
                        <li>
                            <a href="#!/finReport" title="Histórico dos financiamentos"><i class="fa fa-chart-bar"> </i></a>
                        </li>
                        <li>
                            <a href="lib/Manual de credito.pdf" target="_blank" title="Manual do Crédito"><i class="fa fa-question-circle"> </i></a>
                        </li>                        
                        <li><a href="php/logout.php"><span class="glyphicon glyphicon-log-out"></span> Sair</a></li>
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
    <div class="modla-footer">
        <div class="text-center">
            <button class="btn btn-warning" ng-click="closeModal()">Fechar</button>
        </div>
    </div>
</script>


<!-- Modal para registar o motivo da Rejeição -->
<script type="text/ng-template" id="modalRejeitar.html" ng-controller="modalInstanceRejeitar"> 
<div class="modal-header bg-info">
    <h3 class="modal-title">Rejeitar / Anular LEAD
        <span class="closeModal" ng-click="closeModal()">X</span>
    </h3>
</div>
<form ng-submit="rejeitar()">
    <div class="modal-body">
        <div class="form-group">
            <label>Motivos comuns:</label>
            <select ng-model="r.motivoComum" class="form-control">
                <option value=''></option>
                <option value='Falta de Documentação'>Falta de Documentação</option>
                <option value='Incidentes Bancários'>Incidentes Bancários</option>
                <option value='Crédito Renegociado .'>Crédito Renegociado </option>
                <option value='Cancelado a pedido do cliente'>Cancelado a pedido do cliente</option>
                <option value='Não declara rendimentos'>Não declara rendimentos</option>
                <option value='Penhora de rendimentos'>Penhora de rendimentos</option>
                <option value='Atividade aberta recentemente sem histórico'>Atividade aberta recentemente sem histórico</option>
                <option value='Titulo de residência temporário'>Titulo de residência temporário</option>
                <option value='Taxa de esforço elevada'>Taxa de esforço elevada</option>
                <option value='Instabilidade Profissional'>Instabilidade Profissional</option>
                <option value='Necessita de 2º Titular'>Necessita de 2º Titular</option>
                <option value='RGPD'>RGPD</option>
                <option value='Outros'>Outros</option>
                <option value='OutrosNoEmail'>Outros SEM ENVIAR EMAIL</option>
            </select>
        </div>
        <div class="form-group">
            <label>Motivo / Justificação</label>
            <textarea class="form-control" rows="3" ng-model="r.motivo"></textarea>
        </div>
        <hr/>
        <div class="form-group">
            <label>Observações internas</label>
            <textarea class="form-control" rows="3" ng-model="r.obs"></textarea>
        </div>
            
    </div>
    <div class="modal-footer">
        <div class="text-center">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a class="btn btn-warning" ng-click="closeModal()">Cancelar</a>
        </div>         
    </div>
</form> 
</script>