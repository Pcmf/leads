<!DOCTYPE html>
<!--
    This page is used to Login
    Require this files:
    appLogIn.js, checkUser.php, passwordHash.php
-->
<html ng-app="appLogIn"  style="height: 100%">
    <head>
        <meta charset="UTF-8">
        <title>GestLife - SiSLead</title>
        <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <link href="css/css.css" rel="stylesheet" type="text/css"/>
        <link href="lib/bootstrap.3.3.7/bootstrap.css" rel="stylesheet" type="text/css"/>
        <script src="lib/angular.1.6.6.min.js" type="text/javascript"></script>
        <script src="js/appLogIn.js" type="text/javascript"></script>
        
    </head>
    <body ng-controller="loginController" style="min-height: 890px">
        <div class="container text-center">
            <div>
                <img class="imagemCentral" src="img/gestlifes logo.png" alt=""/>
            </div>
            <br/><br/><br/><br/>
            <div class="row">
            <div class="col-xs-1 col-sm-4">&nbsp;</div>
            <div class="container col-xs-12 col-sm-4" style="margin: auto">
                <div class="well well-lg">
                    <form ng-submit="login(u)">
                          <div class="col-xs-12 text-center">
                          <label class="col-xs-5 text-right text-primary" for="userName">Utilizador: </label>
                          <div class="form-group col-xs-4">
                              <input style="min-width:100px" type="text" class="form-control" ng-model="u.userName" placeholder="utilizador" required="true"/>
                          </div>
                          <div class="col-xs-3">&nbsp;</div>
                          </div>
                          <br/><br/>
                          <div class="col-xs-12">
                          <label class="col-xs-5 text-right text-primary"  for="pwd">Password:</label>
                          <div class="form-group col-xs-4">
                              <input style="min-width:100px" type="password" class="form-control" ng-model="u.pwd" placeholder="senha" required="true"/>
                          </div>
                          <div class="col-xs-3">&nbsp;</div>
                          </div>
                        
                        <div class="row text-center" ng-show="!validUser">
                        <button type="submit" class="btn btn-success btn-lg">Validar</button>
                            <h5 class="text-danger">{{error}}</h5>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xs-1 col-sm-4">&nbsp;</div>
            </div>
        </div>
        <!--Footer-->
        <footer class="navbar navbar-fixed-bottom bg-info" style="padding-top: 15px;">
            <div class="container text-center">
                <em>Copyright <span class="fa fa-copyright"></span>
                    2018 GestLife -. All rights reserved. Design by 
                    <a>JSalgado</a>
                </em>
            </div>
        </footer>
    </body>
</html>
