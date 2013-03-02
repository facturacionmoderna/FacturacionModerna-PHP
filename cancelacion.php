<?php


//Datos de configuracion para ambientes demo
$UserID = "UsuarioPruebasWS";
$UserPass = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";
$urlTimbrado = "https://t2demo.facturacionmoderna.com/timbrado/wsdl";
$options = array(
   'location' => 'https://t2demo.facturacionmoderna.com/timbrado/soap',
   'uri'      => 'https://t2demo.facturacionmoderna.com/timbrado/soap'
);

//Datos del emisor
$rfcEmisor = "ESI920427886";
$uuid = 'ABC1147C-D41E-4596-9C3E-45629B097CDB';

try {
  $client = new SoapClient($urlTimbrado, array('trace' => 1));  
  $array = array('UserPass' => $UserPass, 
                 'UserID' => $UserID,
                 'emisorRFC' => $rfcEmisor,
                 'uuid' => $uuid
                 );

  $objeto = (object) $array;
  
  $id = $client->requestCancelarCFDI($objeto);
  print_r($id);

  echo $client->__getLastRequest();
  echo $client->__getLastResponse();

}catch (SoapFault $s){
  echo "<h1>SoapFault</h1><br>";
  die('ERROR: [' . $s->faultcode . '] ' . $s->faultstring);
}catch (Exception $e){
   die('ERROR: ' . $e->getMessage());
}

?>