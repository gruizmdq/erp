<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <style>
        .table{
            margin-top: 15px;
            font-size: 16px;
            border-collapse: collapse;
        }
        .table td{
            border: 1px solid #000;
            margin: 0
        }
        .title{
            font-size: 22px;
        }
        .background {
            background: #eee;
        }
    </style>
</head>
<body>
    <h1>Artículos sin ajustados en: {{ $sucursal }}</h1>
    <h3>Zapatillas:</h3>
    <table width="100%" class="table">
        <tr>
            <td class='background' width="20%">Marca</td>
            <td class='background' width="20%">Artículo</td>
            <td class='background' width="20%">Color</td>
            <td class='background' width="20%">Número</td>
        </tr>
        @foreach ($shoe_details as $item)
        
        <tr>
            <td>{{ $item->brand }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->color }}</td>
            <td>{{ $item->number }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>