<!DOCTYPE html>
<html>
<head>
    <title>{{ $config['receipt_title'] }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid{{ $color['primary'] }};
            font-size: 16px;
            line-height: 24px;
            color: black;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: black;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: {{ $color['primary'] }};
            border-bottom: 1px solid{{ $color['accent'] }};
            color: {{ $color['primary_context'] }};
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid{{ $color['primary'] }};
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid{{ $color['primary'] }};
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <strong>{{ $config['receipt_title'] }} #:</strong> {{ $receipt['invoice_number'] }}<br>
                            <strong>Date:</strong> {{ \Inovector\MixpostEnterprise\Util::dateFormat(\Illuminate\Support\Carbon::parse($receipt['created_at'])) }}
                            <br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            <strong>From:</strong> <br>
                            {!! nl2br($config['company_details']) !!}
                        </td>

                        <td>
                            <strong>To:</strong> <br>
                            {!! nl2br($to) !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>{{ __('mixpost-enterprise::general.description') }}</td>
            <td>{{ __('mixpost-enterprise::finance.price') }}</td>
        </tr>

        <tr class="item last">
            <td>{{ $receipt['description'] }}</td>
            <td>{{ $receipt['amount'] }} {{ $receipt['currency'] }}</td>
        </tr>


        <tr class="total">
            <td></td>
            <td>{{ __('mixpost-enterprise::finance.total') }} : {{ $receipt['amount'] }} {{ $receipt['currency'] }}</td>
        </tr>
    </table>
</div>
</body>
</html>
