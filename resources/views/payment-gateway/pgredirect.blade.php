<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Processing...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
            text-align: center;
        }

        .loader {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .message {
            font-size: 18px;
            color: #000;
        }

        .warning {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <img src="data:image/gif;base64,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"
        alt="Loading..." class="loader" />

    <div class="message">
        Please wait while we process your request<br />
        <span class="warning">Please do not refresh the page or disconnect from the internet</span>
    </div>
    @if(isset($formFields))
    <form id="paymentForm" action="{{ $posturl }}" method="{{  $method ?? 'POST' }}" hidden>
        @foreach ($formFields as $key => $value)
            <input type="hidden" name="{{ $key }}" id="{{ $key }}" value="{{ $value }}">
        @endforeach
        <button type="submit" id="payButton">Pay</button>
    </form>
    @endif

    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @if(isset($formFields))
    <script type="text/javascript">
        $("#paymentForm").submit();
    </script>
    @endif

    @if(isset($pg_script))
        {!! $pg_script !!}
    @endif
</body>

</html>
