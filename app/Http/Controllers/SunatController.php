<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\DetalleVenta;

use Inertia\Inertia;

use Illuminate\Http\Request;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;

use Luecano\NumeroALetras\NumeroALetras;

class SunatController extends Controller
{        
    public function getFacturas() 
    {
        $facturas = Venta::with('cliente')
                        ->with('vendedor')
                        ->where('tipo_comprobante', 'FACTURA')
                        ->latest()->get();
        
        return Inertia::render('Sunat/Facturas', compact('facturas'));
    }
    
    public function enviarFactura($comprobante_id)
    {              
        $direccion_empresa = (new Address())
            ->setUbigueo(config('cardena.direccion.ubigeo'))
            ->setDepartamento(config('cardena.direccion.departamento'))
            ->setProvincia(config('cardena.direccion.provincia'))
            ->setDistrito(config('cardena.direccion.distrito'))
            ->setUrbanizacion(config('cardena.direccion.urbanizacion'))
            ->setDireccion(config('cardena.direccion.direccion'))
            ->setCodLocal(config('cardena.direccion.codigo_local')); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
        
        $empresa = (new Company())
            ->setRuc(config('cardena.empresa.ruc'))
            ->setRazonSocial(config('cardena.empresa.razon_social'))
            ->setNombreComercial(config('cardena.empresa.nombre_comercial'))
            ->setAddress($direccion_empresa);

        $comprobante = Venta::with('cliente')
                            ->with('vendedor:id,usuario')
                            ->with('detalles_venta.producto')
                            ->where('id', $comprobante_id)
                            ->first();                         

        $see = require config_path('Sunat\config.php');                  

        // Cliente
        $cliente = (new Client())
            ->setTipoDoc('6')
            ->setNumDoc($comprobante->cliente->num_documento)
            ->setRznSocial($comprobante->cliente->nombre);   

        $igv_porcentaje = 0.18;
        $factor_porcentaje = 1.18; //solo para op. gravadas
        $op_gravadas = 0.00;
        $igv = 0;        
            
        foreach ($comprobante->detalles_venta as $idx => $detalle_venta) {  
            $igv_detalle = $detalle_venta->precio * $detalle_venta->cantidad * $igv_porcentaje;

            $items[$idx] = (new SaleDetail())
                                ->setCodProducto($detalle_venta->producto->id)
                                ->setUnidad('NIU') // Unidad - Catalog. 03
                                ->setCantidad($detalle_venta->cantidad)
                                ->setMtoValorUnitario($detalle_venta->precio)
                                ->setDescripcion($detalle_venta->producto->nombre)
                                ->setMtoBaseIgv($detalle_venta->precio)
                                ->setPorcentajeIgv(18.00) // 18%
                                ->setIgv($igv_detalle)
                                ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07
                                ->setTotalImpuestos($igv_detalle) // Suma de impuestos en el detalle
                                ->setMtoValorVenta($detalle_venta->precio * $detalle_venta->cantidad)
                                ->setMtoPrecioUnitario($detalle_venta->precio * $factor_porcentaje);
            
            $op_gravadas = $op_gravadas + $detalle_venta->precio * $detalle_venta->cantidad;
            $igv = $igv + $igv_detalle;	
        }

        $total = $op_gravadas;

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta - Catalog. 51
            ->setTipoDoc('01') // Factura - Catalog. 01 
            ->setSerie($comprobante->serie_comprobante)
            ->setCorrelativo($comprobante->num_comprobante)
            ->setFechaEmision(new \DateTime()) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda('PEN') // Sol - Catalog. 02
            ->setCompany($empresa)
            ->setClient($cliente)
            ->setMtoOperGravadas($total)
            ->setMtoIGV($igv)
            ->setTotalImpuestos($igv)
            ->setValorVenta($total)
            ->setSubTotal($total + $igv)
            ->setMtoImpVenta($total + $igv);        

        $formatter = new NumeroALetras();
        $montoLetras = $formatter->toInvoice($total + $igv, 2, 'soles');

        $legend = (new Legend())
            ->setCode('1000') // Monto en letras - Catalog. 52          
            ->setValue($montoLetras);

        $invoice->setDetails($items)->setLegends([$legend]);              

        $result = $see->send($invoice);

        // Guardar XML firmado digitalmente.
        file_put_contents($invoice->getName().'.xml', $see->getFactory()->getLastXml());
        
        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$result->isSuccess()) {
            // Mostrar error al conectarse a SUNAT.
            echo 'Codigo Error: '.$result->getError()->getCode();
            echo 'Mensaje Error: '.$result->getError()->getMessage();
            exit();
        }
        
        // Guardamos el CDR
        file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $code = (int)$cdr->getCode();

        if ($code === 0) {
            echo 'ESTADO: ACEPTADA'.PHP_EOL;
            if (count($cdr->getNotes()) > 0) {
                echo 'OBSERVACIONES:'.PHP_EOL;
                // Corregir estas observaciones en siguientes emisiones.
                var_dump($cdr->getNotes());
            }  
        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA'.PHP_EOL;
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription().PHP_EOL;
    }
}
