<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <style type="text/css">
        body, html {
            width: 55mm;
            height: 45mm;
        }
        body{
            margin-top: 5mm
        }
        .number{
            color:white;
            font-size: 36px;
            font-weight: bold;
            padding: 5px 10px;
            background: black;
            text-align:center;
            border-radius: 15px !important;
        }
        .number-label{
            font-size: 18px;
            font-weight: bold;
        }
        .value{
            font-size: 16px;
            font-weight: bold;
        }
        .text{
            font-size: 12px;
        }
        .column{
            width: 40%
        }
        .row{
            width: 100%;
        }
</style>
</head>
<body>
<table style="width:100%;">
                    <tr style="width:100%;">
                       <td style="width:100%;text-align: center;"><h4 class="card-title" style="font-size: 1px"></h4></td>
                   </tr>
</table>

<div class="row">
    <div style="float:right" class="column">
        <table width="100%">
            <tr>
                <td class="number-label">Nro</td>
                <td class="number">{{ $number }}</td>
            </tr>            
        </table>
    </td>

    </div>
    <div style="float:left" class="column">
        <table width="100%">
            <tr>
                <td width="100%">
                    <div class="text">COLOR:</div>
                    <div class="value">{{ $color }}</div>
                </td>
            </tr>           
        </table>
    </div>
</div>
    
    <div>
        <table width="100%">
            <tr>
                <td  width="40%">
                    <div class="text">{{ $brand_name }}</div>
                    <div class="value">{{ $code }}</div>
                </td>
                <td><barcode code="{{ $barcode }}" type="EAN13" size="0.8" class="barcode" text="0" /></td>
            </tr>
            <tr>
                <td>
                    <div class="text">{{ now()->month }}/{{ now()->year }}</div>
                </td>
            </tr>  
        </table>

    </div>
</body>
</html>