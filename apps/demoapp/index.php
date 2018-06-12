<?php
//\OCP\User::checkLoggedIn();
//\OCP\App::checkAppEnabled('demoapp');
$tpl = new OCP\Template("demoapp", "uploadpopup", "user");
$tpl->printPage();
