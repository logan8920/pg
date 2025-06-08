<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <style>
        body {
            background-color: #f4f8fb;
        }
        .success-card {
            max-width: 500px;
            margin: 50px auto;
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            width: 100px;
            margin-bottom: 20px;
        }
        .company-logo {
            width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="success-card">
    <!-- Company Logo -->
    <img src="https://via.placeholder.com/150x50?text=Company+Logo" class="company-logo" alt="Company Logo">

    <!-- Success Icon -->
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" class="success-icon" alt="Success">

    <h3 class="text-success">Payment Successful</h3>
    <p class="mb-4">{{$message}}</p>

    <form class="mb-3" method="POST" action="{{ $returnUrl }}">
        <input type="hidden" value="{{$encResponse}}" name="encResponse" required>
        {{-- <a download id="downloadBtn" href="{{ $receiptTempUrl }}" class="btn btn-primary me-2">Download Receipt</a> --}}
        <button type="submit" id="returnBtn" class="btn btn-outline-secondary">Return to Client</button>
    </form>

    <p class="text-muted">Redirecting in <span id="countdown">5</span> seconds...</p>
</div>

<script>
    const returnBtn = document.querySelector("#returnBtn");    
    // Countdown and auto redirect
    let counter = 5;
    const interval = setInterval(function () {
        counter--;
        $("#countdown").text(counter);
        if (counter <= 0) {
            clearInterval(interval);
            returnBtn.click();
        }
    }, 1000);
</script>

</body>
</html>
