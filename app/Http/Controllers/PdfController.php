<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Log;

class PdfController extends Controller
{
    const DESTINATION_PATH = "pdf/labels/";
    const LOG_LABEL = "[PDF LABELS API]";

    public function generatePdf(Request $request) {
        Log::info(self::LOG_LABEL." New request to create pdf received: ".$request->getContent());

        $items = $request->input('items');
        $color = $request->input('color');
        $brand_name = $request->input('brand_name');
        $code = $request->input('code');

        $namefile = self::DESTINATION_PATH.$brand_name."_$code-".time().'.pdf';
        $status = "¡Bien papá!";
        $message = "Todo piola";
        $statusCode = 200;
        

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
 
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $mpdf = new Mpdf([
            'margin_left' => '0',
            'margin_right' => '0',
            'margin_top' => '0',
            'margin_bottom' => '0',
            'margin_header' => '0',
            'margin_footer' => '0',
            "format" => [55,44],
        ]);
        // $mpdf->SetTopMargin(5);
        $total = count($items);
        $j = 1;
        foreach ($items as $item) {
            $html = view('pdf.generar', ["barcode" => $item['barcode'], "number" => $item['number'], "color" => $color, "brand_name" => $brand_name, "code" => $code]);
            for ($i = 0; $i < $item['stock_to_add']; $i++){
                $mpdf->WriteHTML($html);
                $mpdf->AddPage();
            }

        }

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->Output($namefile,\Mpdf\Output\Destination::FILE);

        return response()->json(["status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }
}
