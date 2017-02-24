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
  $rfc_emisor = "TCM970625MB1";
  
  //Archivos del CSD de prueba proporcionados por el SAT.
  //ver http://developers.facturacionmoderna.com/webroot/CertificadosDemo-FacturacionModerna.zip
  $numero_certificado = "20001000000300022762";
  $archivo_cer = "utilerias/certificados/20001000000300022762.cer";
  $archivo_pem = "utilerias/certificados/20001000000300022762.key.pem";
  
    
  //Datos de acceso al ambiente de pruebas
  $url_timbrado = "https://t1demo.facturacionmoderna.com/timbrado/wsdl";
  $user_id = "UsuarioPruebasWS";
  $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

  //generar y sellar un XML con los CSD de pruebas
  $cfdi = generarXML($rfc_emisor);
  $cfdi = sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem);


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

function sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem){
  
  $private = openssl_pkey_get_private(file_get_contents($archivo_pem));
  $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($archivo_cer)));
  
  $xdoc = new DomDocument();
  $xdoc->loadXML($cfdi) or die("XML invalido");

  $XSL = new DOMDocument();
  $XSL->load('utilerias/xslt32/cadenaoriginal_3_2.xslt');
  
  $proc = new XSLTProcessor;
  $proc->importStyleSheet($XSL);

  $cadena_original = $proc->transformToXML($xdoc);    
  openssl_sign($cadena_original, $sig, $private);
  $sello = base64_encode($sig);

  $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0); 
  $c->setAttribute('sello', $sello);
  $c->setAttribute('certificado', $certificado);
  $c->setAttribute('noCertificado', $numero_certificado);
  return $xdoc->saveXML();

}
function generarXML($rfc_emisor){

  $fecha_actual = substr( date('c'), 0, 19);
  /*
    Puedes encontrar más ejemplos y documentación sobre estos archivos aquí. (Factura, Nota de Crédito, Recibo de Nómina y más...)
    Link: https://github.com/facturacionmoderna/Comprobantes
    Nota: Si deseas información adicional contactanos en www.facturacionmoderna.com
 */

  $cfdi = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" version="3.2" fecha="$fecha_actual" tipoDeComprobante="ingreso" noCertificado="" certificado="" sello="" formaDePago="Pago en una sola exhibición" metodoDePago="Transferencia Electrónica" NumCtaPago="No identificado" LugarExpedicion="San Pedro Garza García, Mty." subTotal="10.00" total="11.60">
<cfdi:Emisor nombre="EMPRESA DEMO" rfc="$rfc_emisor">
  <cfdi:RegimenFiscal Regimen="No aplica"/>
</cfdi:Emisor>
<cfdi:Receptor nombre="PUBLICO EN GENERAL" rfc="XAXX010101000"></cfdi:Receptor>
<cfdi:Conceptos>
  <cfdi:Concepto cantidad="10" unidad="No aplica" noIdentificacion="00001" descripcion="Servicio de Timbrado" valorUnitario="1.00" importe="10.00">
  </cfdi:Concepto>  
</cfdi:Conceptos>
<cfdi:Impuestos totalImpuestosTrasladados="1.60">
  <cfdi:Traslados>
    <cfdi:Traslado impuesto="IVA" tasa="16.00" importe="1.6"></cfdi:Traslado>
  </cfdi:Traslados>
</cfdi:Impuestos>
</cfdi:Comprobante>
XML;
  return $cfdi;
}

?>
