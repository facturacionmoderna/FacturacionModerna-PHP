# Cliente de Timbrado y Cancelación de CFDI

Clase Genérica para realizar el timbrado y cancelación de un CFDI con [Facturación Moderna][1], utilizando PHP. 


## Carácteristicas

* Clase genérica lista para ser implementada en tu proyecto.
* Timbrado de XML CFDI versión 3.2 
* Timbrado de un archivo de texto simple
* Ejemplo de generación del sello digital

## Preparaciones
* Dar permisos de escritura a la carpeta comprobantes: 
  chmod -R 777 comprobantes
* Instalar libreria para procesamiento de archivos XSLT
  apt-get install php5-xsl
  #recargar apache
  /etc/init.d/apache2 reload

## => Ejemplo de uso

Ejecución utilizando el interprete PHP

```en consola 
php ejemploTimbradoXML.php 
php ejemploTimbradoLayout.php
php ejemploCancelacion.php

```
Ejecución utilizando navegador web

``en el navegador poner
http://localhost/FacturacionModerna-PHP/ejemploTimbradoXML.php
http://localhost/FacturacionModerna-PHP/ejemploTimbradoLayout.php
http://localhost/FacturacionModerna-PHP/ejemploCancelacion.php
``reemplazar localhost por la ip o url del servidor en donde estén ubicados los archivos de ejemplo, si aplica
```

## <= Ejemplo de uso
Si tiene alguna duda sobre la implementación de está clase, puede contactarnos a: 

desarrollo@facturacionmoderna.com 

[1]: http://www.facturacionmoderna.com

