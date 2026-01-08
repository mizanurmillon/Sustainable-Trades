<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Pickup Request</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .header {
            background-color: #274F45;
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 120px;
            color: #666;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .product-table th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }

        .product-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .footer {
            background-color: #f1f1f1;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #777;
        }

        .message-box {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Local Pickup Request</h2>
            <p style="margin:5px 0 0;">Order Reference: #{{ $cart->id }}</p>
        </div>

        <div class="content">
            <div class="section-title">Customer Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Name:</td>
                    <td>{{ $data['name'] }}</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td>{{ $data['phone'] }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $data['email'] }}</td>
                </tr>
            </table>

            @if ($data['message'])
                <div class="section-title">Customer Message</div>
                <div class="message-box">
                    "{{ $data['message'] }}"
                </div>
                <br>
            @endif

            <div class="section-title">Requested Products</div>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: center;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($cart) && $cart->CartItems)
                        @foreach ($cart->CartItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->product_name }}</strong><br>
                                </td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2">No products found in the cart.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <p style="margin-top: 25px;">Please contact the customer to finalize the pickup time and location.</p>
        </div>

        <div class="footer">
            <p>This is notification from <strong>{{ config('app.name') }}</strong>.</p>
            <p>&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
