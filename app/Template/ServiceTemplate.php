<?php

$template = "<?php

namespace App\Services;

class {$className}
{
    function __consturct() {
        
        throw new \Exception('{$className} Service is Not Set.');
    }

    public function init()
    {
        throw new \Exception('{$className} Service is Not Set.');
    }

    public function handlePgCallback(){
        throw new \Exception('{$className} Service is Not Set.');
    }
}
";
?>