<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PdfController extends Controller
{
    public function getIndex()
    {
        return view('pdf.index');
    }
    public function getGenerar(Request $request)
    {
        $accion = $request->get('accion');
        $tipo = $request->get('tipo');
        return $this->pdf($accion,$tipo);
    }
    public function pdf($accion='ver',$tipo='digital')
    {
        $ruc = "10072486893";
        $numero = "00000412";
        $nombres = "DAVID OLIVARES PEÑA";
        $dia = "09";
        $mes = "04";
        $ayo = "17";
        $direccion = "Lima Perú";
        $dni = "23918745";
        $total = 0;
        $articulos = [
            [
                "cantidad" => 3,
                "descripcion" => "COCINA A GAS",
                "precio" => 400.00,
                "importe" => 1200,
            ],
            [
                "cantidad" => 1,
                "descripcion" => "PLANCHA",
                "precio" => 85.00,
                "importe" => 85.00,
            ],
        ];
        foreach ($articulos as $key => $value) {
            $total += $value["importe"];
            $articulos[$key]["precio"] = number_format($value["precio"],2,'.',' ');;
            $articulos[$key]["importe"] = number_format($value["importe"],2,'.',' ');;
 
        }
        $total = number_format($total,2,'.',' ');
 
        $data['ruc'] = $ruc;
        $data['numero'] = $numero;
        $data['nombres'] = $nombres;
        $data['dia'] = $dia;
        $data['mes'] = $mes;
        $data['ayo'] = $ayo;
        $data['direccion'] = $direccion;
        $data['dni'] = $dni;
        $data['articulos'] = $articulos;
        $data['total'] = $total;
        $data['tipo'] = $tipo;
 
        if($accion=='html'){
            return view('pdf.generar',$data);
        }else{
            $html = view('pdf.generar',$data)->render();
        }
        $namefile = 'boleta_de_venta_'.time().'.pdf';
 
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
 
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $mpdf = new Mpdf([
            
            // "format" => "A4",
            "format" => [264.8,188.9],
        ]);
        // $mpdf->SetTopMargin(5);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);
        // dd($mpdf);
        if($accion=='ver'){
            $mpdf->Output($namefile,"I");
        }elseif($accion=='descargar'){
            $mpdf->Output($namefile,"D");
        }
    }
}
