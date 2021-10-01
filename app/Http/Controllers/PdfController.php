<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Mpdf\Mpdf;
use App\ShoeDetail;
use App\StockMovement;
use App\StockReset;
use App\StockResetItem;
use App\Sucursal;

use Log;

class PdfController extends Controller
{
    const DESTINATION_PATH = "pdf/labels/";
    const LOG_LABEL = "[PDF LABELS API]";

    public function generatePdf(Request $request) {
        $request->user()->authorizeRoles(['admin']);

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
                if ($i < $item['stock_to_add']-1)
                    $mpdf->AddPage();
            }
        }

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->Output($namefile,\Mpdf\Output\Destination::FILE);

        return response()->json(["url" => $namefile, "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    public function printMovement(Request $request, $id) {
        $request->user()->authorizeRoles(['admin']);
        Log::info(self::LOG_LABEL." New request to create pdf for stock movement received: ".$request->getContent());

        $mov = StockMovement::findOrFail($id);
        $mov->sucursals = $mov->getSucursalNames();
        $mov->items = $mov->getItems();

        $html = view('pdf.movement',['movement' => $mov])->render();
        $namefile = "movimiento-".$mov->id.'.pdf';

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
        $mpdf->Output($namefile,"I");
    }

    public function getStockResetProcessed(Request $request) {
        $request->user()->authorizeRoles(['admin']);
        Log::info(self::LOG_LABEL." New request to retrieve stock reset processed received");
        
        $sucursal = Sucursal::findOrfail($request->input('sucursal'));

        try {
            $stock_reset = StockReset::where([
                    ['status', 0],
                    ['id_sucursal', $sucursal->id]])
                    ->firstOrFail();
            $shoe_details = ShoeDetail::select('shoe_details.*', 'shoes.code as code', 'shoe_colors.name as color', 'shoe_brands.name as brand')
                ->whereIn('shoe_details.id', function($query) use($stock_reset) {
                    $query->select('id_shoe_detail')
                        ->from('stock_reset_items')
                        ->where('id_stock_reset', $stock_reset->id);
                })
                ->join('shoes', 'shoes.id', 'shoe_details.id_shoe')
                ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                ->orderByRaw("shoe_brands.name ASC, shoes.code ASC, shoe_colors.name ASC, shoe_details.number ASC")
                ->get();

            if (!count($shoe_details))
                return "No hay ningún artículo ajustado";
            
            $html = view('pdf.stock.reset_processed',['shoe_details' => $shoe_details, 'sucursal' => $sucursal->name])->render();
            $namefile = "articulos-ajustados.pdf";
    
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
            $mpdf->Output($namefile,"I");
        }
        catch (ModelNotFoundException $e) {
            Log::warning(self::LOG_LABEL." There is not open reset stock process");
            return "No hay ningún artículo ajustado";
        }
    }

    public function getStockResetUnProcessed(Request $request) {
        $request->user()->authorizeRoles(['admin']);
        Log::info(self::LOG_LABEL." New request to retrieve stock reset unprocessed received");
        
        $sucursal = Sucursal::findOrfail($request->input('sucursal'));

        try {
            $stock_reset = StockReset::where([
                    ['status', 0],
                    ['id_sucursal', $sucursal->id]])
                    ->firstOrFail();
            $shoe_details = ShoeDetail::select('shoe_details.*', 'shoes.code as code', 'shoe_colors.name as color', 'shoe_brands.name as brand')
                ->whereNOTIn('shoe_details.id', function($query) use($stock_reset) {
                    $query->select('id_shoe_detail')
                        ->from('stock_reset_items')
                        ->where('id_stock_reset', $stock_reset->id);
                })
                ->join('shoes', 'shoes.id', 'shoe_details.id_shoe')
                ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                ->orderByRaw("shoe_brands.name ASC, shoes.code ASC, shoe_colors.name ASC, shoe_details.number ASC")
                ->get();

            if (!count($shoe_details))
                return "No hay ningún artículo sin ajustar";

            
            $html = view('pdf.stock.reset_unprocessed',['shoe_details' => $shoe_details, 'sucursal' => $sucursal->name])->render();
            $namefile = "articulos-sin-ajustar.pdf";
    
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
            $mpdf->Output($namefile,"I");
        }
        catch (ModelNotFoundException $e) {
            Log::warning(self::LOG_LABEL." There is not open reset stock process");
            return "No hay ningún artículo ajustado";
        }
    }
}
