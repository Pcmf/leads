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
<html ng-app="appGest" style="height: 100%">
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
        <script src="js/appGest.js" type="text/javascript"></script>
        <script src="lib/checklist-model.js" type="text/javascript"></script>
        <script src="js/gcreateLead.js" type="text/javascript"></script>
        <script src="js/gdashboard.js" type="text/javascript"></script>
        <script src="js/gcontact.js" type="text/javascript"></script>
        <script src="js/gcontactRec.js" type="text/javascript"></script>
        <script src="js/gdocs.js" type="text/javascript"></script>
        <script src="js/gdocDet.js" type="text/javascript"></script>
        <script src="js/glistaPesq.js" type="text/javascript"></script>
        <script src="js/ganuladas.js" type="text/javascript"></script>
        <script src="js/gagendadas.js" type="text/javascript"></script>
        <script src="js/detLead.js" type="text/javascript"></script>
        <script src="js/listFin.js" type="text/javascript"></script>
        <script src="js/glist.js" type="text/javascript"></script>
        <script src="js/finReport.js" type="text/javascript"></script>
        <script src="js/callAskForDoc.js" type="text/javascript"></script>
        <script src="js/gPortal.js" type="text/javascript"></script>
        <script src="js/process_form.js" type="text/javascript"></script>
        <script src="js/listSpeed.js" type="text/javascript"></script>
        
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
                    <!-- Lado Esquerdo 
                    <ul class="nav navbar-nav">
                        <li><a class="btn {{btnSaldo}} btn-block " ng-show="showTx" style="color: white"> <i class="fa fa-balance-scale fa-2x"> </i> <strong> Saldo: {{saldo}}</strong></a></li>
                    </ul>-->
                    
                    <!-- Lado direito -->
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#!/user"><span class="glyphicon glyphicon-user"></span> {{userData.nome}}</a></li>
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
                            <a href="#!/listSpeed" ><i class="fa fa-rocket"> </i> Speed Up {{speedup.length}}</a>
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
    <div class="modal-footer">
        <div class="text-center">
            <button class="btn btn-danger" ng-click="badDoc= true"><i class="fa fa-thumbs-down"></i></button>
            <button class="btn btn-success" ng-click="okDoc()"><i class="fa fa-thumbs-up"></i></button>
            <button class="btn btn-warning" ng-click="closeModal()">Fechar</button>
        </div>
        <div class="justify-bad-doc" ng-if="badDoc">    
                <label>Motivo de não aceitação do documento:</label>
                <input type="text"  ng-model="m.motivoBadDoc" style="width:300px">
                <button class="btn btn-info" ng-click="guardarMotivo(m)" ng-disabled="m.motivoBadDoc==''">Guardar</button>
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
                <option value='Simulação'>Simulação</option>
                <option value='Teste'>Teste</option>
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