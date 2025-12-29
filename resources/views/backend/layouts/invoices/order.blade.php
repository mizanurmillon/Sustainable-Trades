<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .invoice-box {
            width: 100%;
        }

        .header {
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
            border: none;
        }

        .header td {
            border: none;
            padding: 5px;
            vertical-align: top;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .section {
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .two-column {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .two-column td {
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.items th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        table.items td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 100%;
            margin-top: 15px;
        }

        .summary table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }

        .summary td {
            padding: 6px;
            border: 1px solid #ddd;
        }

        .summary .label {
            background: #f9f9f9;
        }

        .summary .total {
            font-weight: bold;
            font-size: 14px;
            background: #f2f2f2;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="invoice-box">

        <!-- Header -->
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="title">Order Invoice</div>
                        <div>Order ID: #{{ $order->id }}</div>
                        <div>Order Number: {{ $order->order_number }}</div>
                        <div>Date: {{ $order->created_at->format('d M Y') }}</div>
                    </td>
                    <td class="right">
                        <strong>{{ $order->shop->shop_name }}</strong><br>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Shipping & Customer Info -->
        <div class="section">
            <table class="two-column">
                <tr>
                    <td>
                        <div class="section-title">Shipping Address</div>
                        <div>
                            {{ $order->shippingAddress->address }}<br>
                            {{ $order->shippingAddress->city }},
                            {{ $order->shippingAddress->state }},
                            {{ $order->shippingAddress->country }}<br>
                            {{ $order->shippingAddress->postal_code }}<br>
                            @if ($order->shippingAddress->apt)
                                Apt/Suite: {{ $order->shippingAddress->apt }}
                            @endif
                        </div>
                    </td>

                    <td>
                        <div class="section-title">Customer Information</div>
                        <div>
                            {{ $order->shippingAddress->first_name }}
                            {{ $order->shippingAddress->last_name }}<br>
                            {{ $order->shippingAddress->phone }}<br>
                            {{ $order->shippingAddress->email }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items -->
        <div class="section">
            <div class="section-title">Order Items</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td>{{ $item->product->product_name }}</td>
                            <td class="text-right">{{ number_format($item->product->product_price, 2) }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">
                                {{ number_format($item->product->product_price * $item->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="text-right">{{ number_format($order->sub_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Shipping Fee</td>
                    <td class="text-right">{{ number_format($order->shipping_fee, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Tax</td>
                    <td class="text-right">{{ number_format($order->tax, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Discount</td>
                    <td class="text-right">- {{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="total">Total</td>
                    <td class="total text-right">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            Thank you for your order
        </div>

    </div>
</body>

</html>
