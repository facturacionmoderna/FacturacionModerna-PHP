<?php
date_default_timezone_set('America/Mexico_City');

include("FacturacionModerna/FacturacionModerna.php");

/***************************************************************************
* Descripción: Ejemplo del uso de la clase FacturacionModerna, generando un
* archivo XML de un CFDI 3.2 y enviandolo a certificar.
*
* Nota: Esté ejemplo pretende ilustrar de manera general el proceso de sellado y
* timbrado de un XML que cumpla con los requerimientos del SAT.
* 
* Facturación Moderna :  (http://www.facturacionmoderna.com)
* @author Edgar Durán <edgar.duran@facturacionmoderna.com>
* @package FacturacionModerna
* @version 1.0
*
*****************************************************************************/

pruebaTimbrado();

function pruebaTimbrado(){

  /**
  * Niveles de debug:
  * 0 - No almacenar
  * 1 - Almacenar mensajes SOAP en archivo log.
  */
  
  $debug = 1;
  
  //RFC utilizado para el ambiente de pruebas
  $rfc_emisor = "ESI920427886";
  
  //Archivos del CSD de prueba proporcionados por el SAT.
  //ver http://developers.facturacionmoderna.com/webroot/CertificadosDemo-FacturacionModerna.zip
  $numero_certificado = "20001000000200000192";
  $archivo_cer = "utilerias/certificados/20001000000200000192.cer";
  $archivo_pem = "utilerias/certificados/20001000000200000192.key.pem";
  
    
  //Datos de acceso al ambiente de pruebas
  $url_timbrado = "https://t1demo.facturacionmoderna.com/timbrado/wsdl";
  $user_id = "UsuarioPruebasWS";
  $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

  //generar y sellar un XML con los CSD de pruebas
  $cfdi = generarXML($rfc_emisor, $numero_certificado, $archivo_cer);  
  $cfdi = sellarXML($cfdi, $archivo_pem);  
  
  $parametros = array('emisorRFC' => $rfc_emisor,'UserID' => $user_id,'UserPass' => $user_password);

  $opciones = array();
  
  /**
  * Establecer el valor a true, si desea que el Web services genere el CBB en
  * formato PNG correspondiente.
  * Nota: Utilizar está opción deshabilita 'generarPDF'
  */     
  $opciones['generarCBB'] = false;
  
  /**
  * Establecer el valor a true, si desea que el Web services genere la
  * representación impresa del XML en formato PDF.
  * Nota: Utilizar está opción deshabilita 'generarCBB'
  */
  $opciones['generarPDF'] = false;
  
  /**
  * Establecer el valor a true, si desea que el servicio genere un archivo de
  * texto simple con los datos del Nodo: TimbreFiscalDigital
  */
  $opciones['generarTXT'] = false;
 
  $cliente = new FacturacionModerna($url_timbrado, $parametros, $debug);
  if($cliente->timbrar($cfdi, $opciones)){

    //Almacenanos en la raíz del proyecto los archivos generados.
    $comprobante = 'comprobantes/'.$cliente->UUID;
    
    if($cliente->xml){
      echo "XML almacenado correctamente en $comprobante.xml\n";        
      file_put_contents($comprobante.".xml", $cliente->xml);
    }
    if(isset($cliente->pdf)){
      echo "PDF almacenado correctamente en $comprobante.pdf\n";
      file_put_contents($comprobante.".pdf", $cliente->pdf);
    }
    if(isset($cliente->png)){
      echo "CBB en formato PNG almacenado correctamente en $comprobante.png\n";
      file_put_contents($comprobante.".png", $cliente->png);
    }
    
    echo "Timbrado exitoso\n";
    
  }else{
    echo "[".$cliente->ultimoCodigoError."] - ".$cliente->ultimoError."\n";
  }    
}

function sellarXML($cfdi,$archivo_pem){

  $private = openssl_pkey_get_private(file_get_contents($archivo_pem));   
  
  $xdoc = new DomDocument();
  $xdoc->loadXML($cfdi) or die("XML invalido"); 
  
  $XSL = new DOMDocument();
  $XSL->load('utilerias/xsltretenciones/retenciones.xslt');
  
  $proc = new XSLTProcessor;
  $proc->importStyleSheet($XSL);

  $cadena_original = $proc->transformToXML($xdoc);
  openssl_sign($cadena_original, $sig, $private);
  $sello = base64_encode($sig);
  
  $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/esquemas/retencionpago/1', 'Retenciones')->item(0); 
  $c->setAttribute('Sello', $sello);  
  return $xdoc->saveXML();

}
function generarXML($rfc_emisor,$numero_certificado, $archivo_cer){

  $fecha_actual = str_replace(' ', 'T', date('Y-m-d H:i:s')) . '-06:00';
  $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($archivo_cer)));
  /*
    Puedes encontrar más ejemplos y documentación sobre estos archivos aquí. (Factura, Nota de Crédito, Recibo de Nómina y más...)
    Link: https://github.com/facturacionmoderna/Comprobantes
    Nota: Si deseas información adicional contactanos en www.facturacionmoderna.com
 */

  $cfdi = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<retenciones:Retenciones xmlns:retenciones="http://www.sat.gob.mx/esquemas/retencionpago/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation=" http://www.sat.gob.mx/esquemas/retencionpago/1 http://www.sat.gob.mx/esquemas/retencionpago/1/retencionpagov1.xsd" Version="1.0" FolioInt="RetA" Sello="" NumCert="$numero_certificado" Cert="$certificado" FechaExp="$fecha_actual" CveRetenc="05">
  <retenciones:Emisor RFCEmisor="$rfc_emisor" NomDenRazSocE="Empresa retenedora ejemplo"/>
  <retenciones:Receptor Nacionalidad="Nacional">
  <retenciones:Nacional RFCRecep="XAXX010101000" NomDenRazSocR="Publico en GENERAL"/>
  </retenciones:Receptor>
  <retenciones:Periodo MesIni="1" MesFin="1" Ejerc="2014" />
  <retenciones:Totales montoTotOperacion="33783.75" montoTotGrav="35437.50" montoTotExent="0.00" montoTotRet="7323.75">
  <retenciones:ImpRetenidos BaseRet="35437.50" Impuesto="02" montoRet="3780.00" TipoPagoRet="Pago definitivo"/>
  <retenciones:ImpRetenidos BaseRet="35437.50" Impuesto="01" montoRet="3543.75" TipoPagoRet="Pago provisional"/>
  </retenciones:Totales>
  <retenciones:Complemento>
  </retenciones:Complemento>
 </retenciones:Retenciones>
  
XML;
  return $cfdi;
}

?>
