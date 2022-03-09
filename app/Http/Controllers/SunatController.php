<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ComunicacionBaja;
use App\Models\Venta;
use App\Models\DetalleVenta;
use DateTime;
use Inertia\Inertia;

use Illuminate\Http\Request;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Illuminate\Support\Facades\Log;
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
        $see = require config_path('Sunat/config.php');
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
            $valor_uni = $detalle_venta->precio / $factor_porcentaje;
            $igv_detalle = $valor_uni * $detalle_venta->cantidad * $igv_porcentaje;

            $items[$idx] = (new SaleDetail())
                                ->setCodProducto($detalle_venta->producto->id)
                                ->setUnidad('NIU') // Unidad - Catalog. 03
                                ->setDescripcion($detalle_venta->producto->nombre)
                                ->setCantidad($detalle_venta->cantidad)
                                ->setMtoValorUnitario($valor_uni)
                                ->setMtoValorVenta($valor_uni * $detalle_venta->cantidad)
                                ->setMtoBaseIgv($valor_uni * $detalle_venta->cantidad)
                                ->setPorcentajeIgv(18.00) // 18%
                                ->setIgv($igv_detalle)
                                ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07
                                ->setTotalImpuestos($igv_detalle) // Suma de impuestos en el detalle
                                ->setMtoPrecioUnitario($valor_uni * $factor_porcentaje);
            
            $op_gravadas = $op_gravadas + $valor_uni * $detalle_venta->cantidad;
            $igv = $igv + $igv_detalle;	
        }

        $total = $op_gravadas + $igv;

        $fecha_emision = new DateTime(now());
        // $fecha_emision = new DateTime($comprobante->fecha_hora);

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta - Catalog. 51
            ->setTipoDoc('01') // Factura - Catalog. 01 
            ->setSerie($comprobante->serie_comprobante)
            ->setCorrelativo($comprobante->num_comprobante)
            ->setFechaEmision($fecha_emision) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda('PEN') // Sol - Catalog. 02
            ->setCompany($empresa)
            ->setClient($cliente)
            ->setMtoOperGravadas($op_gravadas)
            ->setMtoIGV($igv)
            ->setTotalImpuestos($igv)
            ->setValorVenta($op_gravadas)
            ->setSubTotal($total)
            ->setMtoImpVenta($total);        

        $formatter = new NumeroALetras();
        $montoLetras = $formatter->toInvoice($total, 2, 'soles');

        $legend = (new Legend())
            ->setCode('1000') // Monto en letras - Catalog. 52          
            ->setValue($montoLetras);

        $invoice->setDetails($items)->setLegends([$legend]);              

        $result = $see->send($invoice);

        // Guardar XML firmado digitalmente.
        $dia_actual = date('Y-m-d');
        $path = storage_path('app/public/Sunat/XML/'. $dia_actual) ;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        file_put_contents(storage_path('app/public/Sunat/XML/' . $dia_actual . '/' . $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante . '.xml'), $see->getFactory()->getLastXml());
        
        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$result->isSuccess()) {
            // Mostrar error al conectarse a SUNAT.
            // echo 'Codigo Error: '.$result->getError()->getCode();
            // echo 'Mensaje Error: '.$result->getError()->getMessage();
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'errorCode' => $result->getError()->getCode(), 'errorMessage' => $result->getError()->getMessage(), 'error' => true];
            // exit();
            return $response;
        }
        $path = storage_path('app/public/Sunat/CDR/'. $dia_actual . '/' . $comprobante->serie_comprobante) ;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        // Guardamos el CDR
        file_put_contents(storage_path('app/public/Sunat/CDR/'. $dia_actual . '/' . 'RF-' . $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante . '.zip'), $result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $code = (int)$cdr->getCode();

        if ($code === 0) {
            // echo 'ESTADO: ACEPTADA'.PHP_EOL;
            $comprobante->estado = 'Enviada';
            $comprobante->fecha_hora = $fecha_emision;
            $comprobante->update();
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'successMessage' => $cdr->getDescription(), 'error' => false];
            if (count($cdr->getNotes()) > 0) {
                // echo 'OBSERVACIONES:'.PHP_EOL;
                $comprobante->estado = 'Observada';
                $comprobante->update();
                $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'successMessage' => $cdr->getDescription(), 'observaciones' => $cdr->getNotes(), 'error' => false];
                // Corregir estas observaciones en siguientes emisiones.
                // var_dump($cdr->getNotes());
            }  
        } else if ($code >= 2000 && $code <= 3999) {
            // echo 'ESTADO: RECHAZADA'.PHP_EOL;
            $comprobante->estado = 'Rechazada';
            $comprobante->update();
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'errorCode' => $code, 'errorMessage' => $cdr->getDescription(), 'error' => true];
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'errorCode' => $code, 'errorMessage' => $cdr->getDescription(), 'error' => true];
            // echo 'Excepción';
        }

        // echo $cdr->getDescription().PHP_EOL;
        return $response;
    }

    public function comBajaFactura($comprobante_id){ //comBajaFactura
        $see = require config_path('Sunat/config.php');

        $comunicacion_baja = new ComunicacionBaja();


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

        $comprobante = Venta::where('id', $comprobante_id)->first();
        $comunicacion_baja->serie_doc = $comprobante->serie_comprobante;
        $comunicacion_baja->correlativo_doc = $comprobante->num_comprobante;
        $comunicacion_baja->save();

        $correlativo = str_pad($comunicacion_baja->id,5,"0",STR_PAD_LEFT);
        Log::info($correlativo);

        $detail1 = new VoidedDetail();
        $detail1->setTipoDoc('01') // Factura
            ->setSerie($comprobante->serie_comprobante)
            ->setCorrelativo($comprobante->num_comprobante)
            ->setDesMotivoBaja('ERROR DE CREACIÓN'); // Motivo por el cual se da de baja.

        // $detail2 = new VoidedDetail();
        // $detail2->setTipoDoc('07') // Nota de Crédito
        //     ->setSerie('FC01')
        //     ->setCorrelativo('2')
        //     ->setDesMotivoBaja('ERROR DE RUC');

        $cDeBaja = new Voided();
        $cDeBaja->setCorrelativo($correlativo) // Correlativo, necesario para diferenciar c. de baja de en un mismo día.
            ->setFecGeneracion(new \DateTime($comprobante->fecha_hora)) // Fecha de emisión de los comprobantes a dar de baja
            ->setFecComunicacion(new \DateTime(now())) // Fecha de envio de la C. de baja
            ->setCompany($empresa)
            ->setDetails([$detail1]);
            // ->setDetails([$detail1, $detail2]);

        $result = $see->send($cDeBaja);

        $dia_actual = date('Y-m-d');
        $path = storage_path('app/public/Sunat/XML/'. $dia_actual . '/' . $comprobante->serie_comprobante) ;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Guardar XML
        file_put_contents(storage_path('app/public/Sunat/XML/' . $dia_actual . '/' . $comprobante->serie_comprobante . '/' . $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante . '.xml'),
                        $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            // Si hubo error al conectarse al servicio de SUNAT.
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'errorCode' => $result->getError()->getCode(), 'errorMessage' => $result->getError()->getMessage(), 'error' => true];
            // exit();
            return $response;
            // var_dump($result->getError());
            // exit();
        }
    
        $ticket = $result->getTicket();

        $comunicacion_baja->correlativo = $correlativo;
        $comunicacion_baja->ticket = $ticket;
        $comunicacion_baja->update();

        $comprobante->estado = 'Baja en proceso';
        $comprobante->update();
        // echo 'Ticket : '.$ticket.PHP_EOL;
        return $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'ticket' => $ticket, 'error' => false];

        

    }

    public function consultarTicket($comprobante_id){ //consultaTicket
        $see = require config_path('Sunat/config.php');
        $comprobante = Venta::where('id', $comprobante_id)->first();  
        $comunicacion_baja = ComunicacionBaja::where('serie_doc', $comprobante->serie_comprobante)
                                            ->where('correlativo_doc', $comprobante->num_comprobante)
                                            ->orderByDesc('created_at')
                                            ->first();
        $dia_actual = date('Y-m-d');
        $statusResult = $see->getStatus($comunicacion_baja->ticket);
        if (!$statusResult->isSuccess()) {
            // Si hubo error al conectarse al servicio de SUNAT.
            $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'errorCode' => $statusResult->getError()->getCode(), 'errorMessage' => $statusResult->getError()->getMessage(), 'error' => true];
            // exit();
            return $response;
            // var_dump($statusResult->getError());
            // return;
        }

        // echo $statusResult->getCdrResponse()->getDescription();
        $path = storage_path('app/public/Sunat/CDR/'. $dia_actual ) ;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        // $comprobante->estado = 'Anulada';
        // $comprobante->update();
        // Guardar CDR
        file_put_contents(storage_path('app/public/Sunat/CDR/'. $dia_actual . '/' . 'R-'. $comunicacion_baja->ticket . '.zip'), $statusResult->getCdrZip());
        if ($statusResult->getCdrResponse()->getCode() == 0) {
            $comprobante->estado = 'Baja aceptada';
            $comprobante->update();
        } else {
            $comprobante->estado = 'Baja error';
            $comprobante->update();
        }
        return $response = ['doc' => $comprobante->serie_comprobante . '-' . $comprobante->num_comprobante, 'successCode' => $statusResult->getCdrResponse()->getCode(), 'successMessage' => $statusResult->getCdrResponse()->getDescription(), 'error' => false];
    }
}
