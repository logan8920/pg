<?php

namespace App\Traits;

trait CommanTrait
{
    public function uploadFile(string $fileName, string $uPath, bool $mulitple = false)
    {
        try {
            $files = request()->file($fileName);
            //dd($files);
            $storedPaths = [];
            if ($mulitple) {
                foreach ($files as $index => $file) {
                    $filename = 'key_' . $index . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $storedPath = $file->storeAs($uPath, $filename);
                    // chmod(storage_path($storedPath), 0600);
                    $storedPaths[] = $storedPath;
                }
            } else {
                $storedPaths[] = $files->storeAs(
                    $uPath,
                    'key_' . time() . $files->getClientOriginalExtension()
                );
                // chmod(storage_path($storedPaths[0]), 0600);
            }
            return ['uploaded' => true, "paths" => $storedPaths];
        } catch (\Exception $e) {
            return ['uploaded' => false, "message" => $e->getMessage()];
        }
    }

    public function createServiceFile($serviceName = 'PaytmService')
    {

        $directory = app_path('Services');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $className = ucfirst(str_replace("_", "", clearSpeicalCharacter(trim($serviceName))));
        $filePath = $directory . '/' . $className . '.php';
        $template = '';
        include base_path('app/Template/ServiceTemplate.php');
        file_put_contents($filePath, $template);

        return $filePath;
    }

    public function calculateCharge($amount, $charges)
    {
        $amount = (float) $amount;

        foreach ($charges as $charge) {
            $min = (float) $charge['min'];
            $max = (float) $charge['max'];
            $type = $charge['charges_type'];
            $amt = (float) $charge['amt'];

            if ($amount >= $min && $amount <= $max) {
                if ($type === 'Flat') {
                    $chargeAmount = $amt;
                } elseif ($type === 'Percentage') {
                    $chargeAmount = ($amount * $amt) / 100;
                } else {
                    throw new \Exception("Unknown charge type", 1051);
                }

                $gstAmount = ($chargeAmount * 18) / 100;
                $totalDeduction = $chargeAmount + $gstAmount;
                $finalAmount = $amount - $totalDeduction;

                return [
                    'original_amount' => $amount,
                    'charge_amount' => round($chargeAmount, 2),
                    'gst_amount' => round($gstAmount, 2),
                    'total_deduction' => round($totalDeduction, 2),
                    'final_amount' => round($finalAmount, 2),
                ];
            }
        }

        throw new \Exception("No matching slab found for Amount", 1050);
    }

}