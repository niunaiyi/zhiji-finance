$files = Get-ChildItem -Path d:\Projects\zhiji-finance\app\Containers\Finance\*\UI\API\Requests\*.php -Recurse
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    # More robust regex for any check([ ... ]) pattern
    $content = [regex]::Replace($content, "return \$this->check\(\s*\[\s*'hasAccess',?\s*\]\s*\);", "return true;")
    Set-Content $file.FullName $content -NoNewline
}
