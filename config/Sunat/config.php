

<?php
// use Greenter\XMLSecLibs\Certificate\X509Certificate;
// use Greenter\XMLSecLibs\Certificate\X509ContentType;

// require 'vendor/autoload.php';

// $pfx = file_get_contents(__DIR__.'/maravillas.pfx');
// $password = 'Maravi512407';

// $certificate = new X509Certificate($pfx, $password);
// $pem = $certificate->export(X509ContentType::PEM);
    
// file_put_contents('certificate.pem', $pem);


use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
// use Greenter\XMLSecLibs\Certificate\X509Certificate;
// use Greenter\XMLSecLibs\Certificate\X509ContentType;

// $pfx = file_get_contents(__DIR__.'/grupocarden.pfx');
// $password = 'rafael2021';
// $certificate = new X509Certificate($pfx, $password);

// $see = new See();

// // $see->setCertificate($certificate->export(X509ContentType::PEM));
// // $see->setService(SunatEndpoints::FE_BETA);
// // $see->setClaveSOL('20539375866', 'RAFAEL21', 'Rafael2021');

// /************************** GREENTER ***************************/
// $see->setCertificate(file_get_contents(__DIR__.'/certificate.pem'));
// $see->setService(SunatEndpoints::FE_BETA);
// $see->setClaveSOL('20000000001', 'MODDATOS', 'moddatos');

$see = new See();
$see->setService(SunatEndpoints::FE_PRODUCCION); // Cambiar la url para cuando sea Percepción/Retención o Guía de Remisión. 
$see->setCertificate(file_get_contents(__DIR__.'/certificate.pem'));
$see->setClaveSOL('20605610821', 'D4N13LZ4', 'Daniel512407');

return $see;