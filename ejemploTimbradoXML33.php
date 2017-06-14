<?php 
date_default_timezone_set('America/Mexico_City');
include("FacturacionModerna/FacturacionModerna.php");

/**
 * Descripción: Ejemplo del uso de la clase FacturacionModerna, generando un
 * archivo XML de un CFDI 3.3 y enviandolo a certificar.
 *
 * Nota: Esté ejemplo pretende ilustrar de manera general el proceso de sellado y
 * timbrado de un XML que cumpla con los requerimientos del SAT.
 * 
 * Facturación Moderna :  (http://www.facturacionmoderna.com)
 * @author Ivan Aquino <ivan.aquino@facturacionmoderna.com>
 * @package FacturacionModerna
 * @version 1.0
 */

pruebaTimbrado();

/**
 * Prueba de timbrado con la conexion a Facturacion Moderna
 * @return void
 */
function pruebaTimbrado() {
    /**
    * Niveles de debug:
    * 0 - No almacenar
    * 1 - Almacenar mensajes SOAP en archivo log.
    */
    
    $debug = 1;
    
    // RFC utilizado para el ambiente de pruebas
    $rfc_emisor = "TCM970625MB1";
    
    /**
     * Archivos del CSD de prueba proporcionados por el SAT.
     * Ver http://developers.facturacionmoderna.com/webroot/CertificadosDemo-FacturacionModerna.zip
     */
    $numero_certificado = "20001000000300022762";
    $archivo_cer = "utilerias/certificados/20001000000300022762.cer";
    $archivo_pem = "utilerias/certificados/20001000000300022762.key.pem";
    
      
    // Datos de acceso al ambiente de pruebas
    $url_timbrado = "https://t1demo.facturacionmoderna.com/timbrado/wsdl";
    $user_id = "UsuarioPruebasWS";
    $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

    // generar y sellar un XML con los CSD de pruebas
    $cfdi = generarXML($rfc_emisor);
    $cfdi = sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem);

    // die(var_dump($cfdi));

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
        // Almacenanos en la raíz del proyecto los archivos generados.
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

/**
 * Sellar el comprobante
 * @param  string $cfdi               XML a sellar
 * @param  string $numero_certificado Numero del certificado
 * @param  string $archivo_cer        Ruta del archivo .cer
 * @param  string $archivo_pem        Ruta del archivo .pem
 * @return string                     XML sellado
 */
function sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem) {
    $private = openssl_pkey_get_private(file_get_contents($archivo_pem));
    $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($archivo_cer)));

    $xdoc = new DomDocument();
    $xdoc->loadXML($cfdi) or die("XML invalido");

    $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0); 
    $c->setAttribute('Certificado', $certificado);
    $c->setAttribute('NoCertificado', $numero_certificado);

    $XSL = new DOMDocument();
    $XSL->load('utilerias/xslt33/cadenaoriginal_3_3.xslt');
    
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($XSL);

    $cadena_original = $proc->transformToXML($xdoc);
    openssl_sign($cadena_original, $sig, $private, OPENSSL_ALGO_SHA256);
    $sello = base64_encode($sig);

    $c->setAttribute('Sello', $sello);
    
    return $xdoc->saveXML();
}


/**
 * Generar el xml basico para el trimbrado
 * @param  string $rfc_emisor RFC del emisor
 * @return string XML valido
 */
function generarXML ($rfc_emisor) {
    $fecha_actual = substr( date('c'), 0, 19);

    $cfdi = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd" Version="3.3" Serie="A" Folio="01" Fecha="$fecha_actual" Sello="" FormaPago="03" NoCertificado="" Certificado="" CondicionesDePago="CONTADO" SubTotal="1850" Descuento="175.00" Moneda="MXN" Total="1943.00" TipoDeComprobante="I" MetodoPago="PUE" LugarExpedicion="68050">
  <cfdi:Emisor Rfc="$rfc_emisor" Nombre="FACTURACION MODERNA SA DE CV" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="XAXX010101000" Nombre="PUBLICO EN GENERAL" UsoCFDI="G01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" NoIdentificacion="AULOG001" Cantidad="5" ClaveUnidad="H87" Unidad="Pieza" Descripcion="Aurriculares USB Logitech" ValorUnitario="350.00" Importe="1750.00" Descuento="175.00">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1575.00" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="252.00"/>
      </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Concepto>
<cfdi:Concepto ClaveProdServ="43201800" NoIdentificacion="USB" Cantidad="1" ClaveUnidad="H87" Unidad="Pieza" Descripcion="Memoria USB 32gb marca Kingston" ValorUnitario="100.00" Importe="100.00">
  <cfdi:Impuestos>
    <cfdi:Traslados>
      <cfdi:Traslado Base="100.00" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="16.00"/>
  </cfdi:Traslados>
</cfdi:Impuestos>
</cfdi:Concepto>
</cfdi:Conceptos>
<cfdi:Impuestos TotalImpuestosTrasladados="268.00">
    <cfdi:Traslados>
      <cfdi:Traslado Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="268.00"/>
  </cfdi:Traslados>
</cfdi:Impuestos>
</cfdi:Comprobante>
XML;
    return $cfdi;
}