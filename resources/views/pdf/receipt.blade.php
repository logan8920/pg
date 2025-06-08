<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            width: 95%;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        h2 {
            color: #28a745;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        td {
            padding: 8px 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Payment Receipt</h2>
        <p><strong>Transaction Date:</strong> {{ now()->format('d M Y, h:i A') }}</p>

        <table>
            <tr>
                <td><strong>Receipt ID:</strong></td>
                <td>{{ $data['id'] }}</td>
            </tr>
            <tr>
                <td><strong>Order ID:</strong></td>
                <td>{{ $data['orderid'] }}</td>
            </tr>
            <tr>
                <td><strong>Bank Txn ID:</strong></td>
                <td>{{ $data['banktxnid'] }}</td>
            </tr>
            <tr>
                <td><strong>Amount:</strong></td>
                <td>{{ (string) formatRupees($data['amount']) }}</td>
            </tr>

            <tr>
                <td><strong>Charges:</strong></td>
                <td>{{ (string) formatRupees($data['charges']) }}</td>
            </tr>
            <tr>
                <td><strong>Payment Mode:</strong></td>
                <td>{{ $data['paymentmode'] }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ $data['status'] }}</td>
            </tr>
            <tr>
                <td><strong>Message:</strong></td>
                <td>{{ $data['message'] }}</td>
            </tr>
        </table>

        <div class="footer">Thank you for your payment. This receipt is system-generated and does not require a
            signature.</div>
    </div>
</body>

</html>
