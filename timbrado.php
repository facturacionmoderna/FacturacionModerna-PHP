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
$ruta_del_archivo_a_timbrar = 'factura_en_texto_ejemplo.txt';

try {
  $client = new SoapClient($urlTimbrado);


  
  $array = array('UserPass' => $UserPass, 
                 'UserID' => $UserID,
                 'emisorRFC' => $rfcEmisor,
                 'text2CFDI' => base64_encode(implode('',  file($ruta_del_archivo_a_timbrar))));

  $objeto = (object) $array;
  
  $id = $client->requestTimbrarCFDI($objeto);
  file_put_contents("representacion_impresa.pdf", base64_decode($id->pdf));
  file_put_contents("cfdi.xml", base64_decode($id->xml));

  echo "<h1>Response</h1><br>";
  echo $client->getLastResponse();
  echo htmlentities( $client->getLastResponse());

  echo "<h1>Request</h1><br>";
  echo htmlentities( $client->getLastRequest());
  echo $client->getLastRequest(); 

}catch (SoapFault $s){
  echo "<h1>SoapFault</h1><br>";
  die('ERROR: [' . $s->faultcode . '] ' . $s->faultstring);
}catch (Exception $e){
   die('ERROR: ' . $e->getMessage());
}

?>