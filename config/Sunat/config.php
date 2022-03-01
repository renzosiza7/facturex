<?php
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;

$pfx = file_get_contents(__DIR__.'/grupocarden.pfx');
$password = 'rafael2021';
$certificate = new X509Certificate($pfx, $password);

$see = new See();

// $see->setCertificate($certificate->export(X509ContentType::PEM));
// $see->setService(SunatEndpoints::FE_BETA);
// $see->setClaveSOL('20539375866', 'RAFAEL21', 'Rafael2021');

/************************** GREENTER ***************************/
$see->setCertificate(file_get_contents(__DIR__.'/certificate.pem'));
$see->setService(SunatEndpoints::FE_BETA);
$see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos');

return $see;