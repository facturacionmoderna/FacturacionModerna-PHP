# Cliente de Timbrado y Cancelación de CFDI

Clase Genérica para realizar el timbrado y cancelación de un CFDI con [Facturación Moderna][1], utilizando PHP. 


## Carácteristicas
* Soporte para el timbrado de diversos [tipos de documento (Factura, Nota de Crédito, Recibo de Nómina)][2] 
* Clase genérica lista para ser implementada en tu proyecto.
* Timbrado de XML CFDI versión 3.2 
* Timbrado de un archivo de texto simple
* Ejemplo de generación del sello digital

## Preparaciones: Linux
* Dar permisos de escritura a la carpeta comprobantes:
```
chmod -R 777 comprobantes
```

* Instalar libreria para procesamiento de archivos XSLT
```
apt-get install php5-xsl
#recargar apache
/etc/init.d/apache2 reload
```

## Preparaciones: Windows
* Habilitar extensiones requeridas en PHP

```
#Descomentar las líneas de extensiones en el archivo php.ini:
extension=php_soap.dll
extension=php_openssl.dll
extension=php_xsl.dll

#reiniciar el servicio apache
```

## Ejemplos de uso

Ejecución utilizando el interprete PHP

```
#En Terminal (linux):

php ejemploTimbradoXML.php 
php ejemploTimbradoLayout.php
php ejemploCancelacion.php
php ejemploActivacionCancelacion.php

#En Símbolo de sistema (Windows)
#el directorio de instalación de php para el ejemplo es  C:\php\bin\php5.4\:

C:\php\bin\php5.4\php.exe ejemploTimbradoXML.php 
C:\php\bin\php5.4\php.exe ejemploTimbradoLayout.php
C:\php\bin\php5.4\php.exe ejemploCancelacion.php
C:\php\bin\php5.4\php.exe ejemploActivacionCancelacion.php

```

Ejecución utilizando navegador web

```
En el navegador poner:

http://localhost/FacturacionModerna-PHP/ejemploTimbradoXML.php
http://localhost/FacturacionModerna-PHP/ejemploTimbradoLayout.php
http://localhost/FacturacionModerna-PHP/ejemploCancelacion.php
http://localhost/FacturacionModerna-PHP/ejemploActivacionCancelacion.php

reemplazar localhost por la ip o url del servidor en donde estén ubicados los archivos de ejemplo.
```

## Dudas

Si tiene alguna duda sobre la implementación de está clase, puede contactarnos a: 

desarrollo@facturacionmoderna.com 

[1]: http://www.facturacionmoderna.com
[2]: https://github.com/facturacionmoderna/Comprobantes

