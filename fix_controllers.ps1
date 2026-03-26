$files = Get-ChildItem -Path d:\Projects\zhiji-finance\app\Containers\Finance\Foundation\UI\API\Controllers\*.php
foreach ($file in $files) {
    if ($file.Name -eq "ListPeriodsController.php") { continue } # Already fixed
    $content = Get-Content $file.FullName -Raw
    
    # Add use statement if not exists
    if ($content -notmatch "use Apiato\\Support\\Facades\\Response;") {
        $content = $content -replace "namespace App\\Containers\\Finance\\Foundation\\UI\\API\\Controllers;", "namespace App\Containers\Finance\Foundation\UI\API\Controllers;`r`n`r`nuse Apiato\Support\Facades\Response;"
    }
    
    # Replace $this->transform(...) with Response::create(...)->ok()
    # Handle single line
    $content = $content -replace "return \$this->transform\(([^,]+),\s*([^:]+)Transformer::class\);", "return Response::create(`$1, `$2Transformer::class)->ok();"
    
    Set-Content $file.FullName $content -NoNewline
}
