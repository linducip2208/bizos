$results = @{}
Get-ChildItem -Recurse -Filter "*Resource.php" -Path "app\Filament\Resources" | ForEach-Object {
    $content = Get-Content $_.FullName -Raw -Encoding UTF8
    $dir = $_.Directory.Name
    if ($content -match "function\s+getNavigationGroup\(\).*?\n.*?return\s+'([^']+)'") {
        $results[$dir] = $matches[1]
    } elseif ($content -match "function\s+getNavigationGroup\(\).*?\n.*?return\s+null") {
        $results[$dir] = "null"
    }
}
$results.GetEnumerator() | Sort-Object Name | ForEach-Object { Write-Output "$([char]0x2500)$($_.Key) -> $($_.Value)" }
