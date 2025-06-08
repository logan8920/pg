<?php

if (!function_exists('clearSpeicalCharacter')) {
    function clearSpeicalCharacter($text)
    {
        // Replace all non-alphanumeric characters with "_"
        $text = preg_replace('/[^a-zA-Z0-9]/', '_', $text);

        // Optional: Collapse multiple underscores and trim
        return preg_replace('/_+/', '_', trim($text, '_'));
    }
}

if (!function_exists('formatRupees')) {
    function formatRupees($amount, $symbol = true)
    {
        $amount = number_format((float)$amount, 2, '.', '');
        $exploded = explode('.', $amount);
        $integerPart = $exploded[0];
        $decimalPart = isset($exploded[1]) ? $exploded[1] : '00';

        $lastThree = substr($integerPart, -3);
        $restUnits = substr($integerPart, 0, -3);
        if ($restUnits != '') {
            $lastThree = ',' . $lastThree;
        }
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);

        $formatted = $restUnits . $lastThree . '.' . $decimalPart;
        return $symbol ? 'Rs. ' . $formatted : $formatted;
    }
}

