<?php

include("FacturacionModerna/FacturacionModerna.php");

/***************************************************************************
* Descripción: Ejemplo del uso del metodo cancelar de la clase FacturacionModerna
* 
* Facturación Moderna :  (http://www.facturacionmoderna.com)
* @author Edgar Durán <edgar.duran@facturacionmoderna.com>
* @package FacturacionModerna
* @version 1.0
*
*****************************************************************************/

pruebaCancelacion();

function pruebaCancelacion(){
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

  /*Cambiar este valor por el UUID que se desea cancelar*/
  $uuid = "3F938316-7E5E-4EE4-9D38-A8C3023120C9";
  $opciones=null;
  
  if($cliente->cancelar($uuid, $opciones)){
    echo "Cancelación exitosa\n";
  }else{
    echo "[".$cliente->ultimoCodigoError."] - ".$cliente->ultimoError."\n";
  }    
}

?>