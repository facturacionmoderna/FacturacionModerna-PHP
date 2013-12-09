<?php

include("FacturacionModerna/FacturacionModerna.php");

/***************************************************************************
* Descripción: Ejemplo del uso del metodo activarCancelacion de la clase FacturacionModerna
* 
* Facturación Moderna :  (http://www.facturacionmoderna.com)
* @author Edgar Durán <edgar.duran@facturacionmoderna.com>
* @package FacturacionModerna
* @version 1.0
*
*****************************************************************************/

pruebaActivacionCancelacion();

function pruebaActivacionCancelacion(){
  /**
  * Niveles de debug:
  * 0 - No almacenar
  * 1 - Almacenar mensajes SOAP en archivo log.
  */
  $debug = 1;
  
  /*RFC utilizado para el ambiente de pruebas*/
  $rfc_emisor = "ESI920427886";
  
  /*Datos de acceso al ambiente de pruebas*/
  $url_timbrado = "https://t1demo.facturacionmoderna.com/timbrado/wsdl";
  $user_id = "UsuarioPruebasWS";
  $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

  $parametros = array('emisorRFC' => $rfc_emisor,'UserID' => $user_id,'UserPass' => $user_password);
  $cliente = new FacturacionModerna($url_timbrado, $parametros, $debug);

  /*Cambiar las variables de acuerdo a los archivos y pass del CSD que se desea activar*/
  $archCer = "utilerias/certificados/20001000000200000192.cer";
  $archKey = "utilerias/certificados/20001000000200000192.key";
  $passKey = "12345678a";
  
  if($cliente->activarCancelacion($archCer,$archKey,$passKey)){
    echo "Activación de Cancelación exitosa\n";
  }else{
    echo "[".$cliente->ultimoCodigoError."] - ".$cliente->ultimoError."\n";
  }    
}

?>