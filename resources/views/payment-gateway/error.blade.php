<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment System</title>

    <!-- Bootstrap CSS CDN -->
    <link href="{{ asset('assets/css/bootstrap4-toggle.min.css') }}" rel="stylesheet">

    <!-- Optional: Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <!-- Optional Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40"> {{-- Replace with your logo --}}
                MyCompany
            </a>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-5">
        <div class="container text-center mt-5">
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" width="100" alt="Failed">
            <h2 class="text-danger mt-4">Payment Failed</h2>

            @if ($error ?? false)
                <div class="alert alert-danger mt-3" role="alert">
                    {{ $error }}
                </div>
            @endif

            <p class="mt-3">
                {{ $message ??
                    'Something went wrong during the payment process. Please try again or contact support if
                                needed.' }}
            </p>
            @if (isset($returnUrl) && !empty($returnUrl))
                <form class="mt-4" action="{{ $returnUrl }}" method="post">
                    <input type="hidden" name="encResponse" value="{{ $encResponse }}" required>
                    <button type="submit" id="returnBtn" class="btn btn-primary">Back to Client</button>
                </form>
                <p class="text-muted">Redirecting in <span id="countdown">5</span> seconds...</p>
            @endif
        </div>
    </main>

    <!-- Bootstrap JS (with Popper) -->
    <script src="{{ asset('assets/js/bootstrap4-toggle.min.js') }}"></script>

    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
     @if (isset($returnUrl) && !empty($returnUrl))
    <script>
        const returnBtn = document.querySelector("#returnBtn");
        // Countdown and auto redirect
        let counter = 5;
        const interval = setInterval(function() {
            counter--;
            $("#countdown").text(counter);
            if (counter <= 0) {
                clearInterval(interval);
                returnBtn.click();
            }
        }, 1000);
    </script>
    @endif
</body>

</html>
