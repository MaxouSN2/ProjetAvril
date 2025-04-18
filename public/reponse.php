<?php
require_once "../vendor/autoload.php";

use Symfony\Component\HttpFoundation\Response;

$maReponse = new Response();

$maReponse->setStatusCode (code:Response::HTTP_OK);
$maReponse->headers->set(key: 'Content-Type', values: 'text/html');
$maReponse->setContent(content: '<html><title>Reponse</title><body>Resultat d\'un objet reponse<body></html>');

$maReponse->send();
