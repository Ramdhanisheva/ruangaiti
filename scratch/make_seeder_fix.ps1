$root = 'C:\Users\ramdh\Documents\blog\blog'
$zip  = 'C:\Users\ramdh\Documents\blog\blog\ruangaiti-seeder-fix.zip'

if (Test-Path $zip) { Remove-Item $zip -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$z = [System.IO.Compression.ZipFile]::Open($zip, 'Create')

$files = @(
    'database/seeders/V3TestSeeder.php',
    'public/deploy-v3.php'
)

foreach ($f in $files) {
    $full = Join-Path $root $f
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($z, $full, $f, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    Write-Host "[OK] $f"
}

$z.Dispose()
Write-Host ""
Write-Host "DONE => $zip" -ForegroundColor Green
