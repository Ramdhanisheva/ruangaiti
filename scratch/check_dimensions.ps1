[Reflection.Assembly]::LoadWithPartialName("System.Drawing")
$img = [System.Drawing.Image]::FromFile("c:\Users\ramdh\Documents\blog\blog\assets\frontend\ruangaiti_flat_text_logo.jpg")
Write-Host "Width: $($img.Width)"
Write-Host "Height: $($img.Height)"
$img.Dispose()
