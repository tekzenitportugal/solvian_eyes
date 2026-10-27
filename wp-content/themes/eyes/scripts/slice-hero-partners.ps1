Add-Type -AssemblyName System.Drawing
$root = Split-Path $PSScriptRoot -Parent
$path = Join-Path $root "assets\images\hero-partners-row.png"
$outDir = Join-Path $root "assets\images\partners"
if (-not (Test-Path $outDir)) {
  New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

$src = [System.Drawing.Bitmap]::FromFile($path)
Write-Host "Source size:" $src.Width "x" $src.Height

$y = 92
$h = [Math]::Max(40, $src.Height - $y - 8)
$band = New-Object System.Drawing.Bitmap($src.Width, $h)
$g = [System.Drawing.Graphics]::FromImage($band)
$destRect = New-Object System.Drawing.Rectangle 0, 0, $band.Width, $band.Height
$srcRectBand = New-Object System.Drawing.Rectangle 0, $y, $src.Width, $h
$g.DrawImage($src, $destRect, $srcRectBand, [System.Drawing.GraphicsUnit]::Pixel)
$g.Dispose()

$n = 5
$slice = [int][Math]::Floor($band.Width / $n)
$names = @(
  "portugal-2030.png",
  "eu-nextgenerationeu.png",
  "prr.png",
  "portugal-2030-2.png",
  "eu-nextgenerationeu-2.png"
)
for ($i = 0; $i -lt $n; $i++) {
  $x = [int]($i * $slice)
  $wi = if ($i -eq ($n - 1)) { [int]($band.Width - $x) } else { $slice }
  $tile = New-Object System.Drawing.Bitmap($wi, $band.Height)
  $gg = [System.Drawing.Graphics]::FromImage($tile)
  $d = New-Object System.Drawing.Rectangle 0, 0, $wi, $band.Height
  $s = New-Object System.Drawing.Rectangle $x, 0, $wi, $band.Height
  $gg.DrawImage($band, $d, $s, [System.Drawing.GraphicsUnit]::Pixel)
  $gg.Dispose()
  $dest = Join-Path $outDir $names[$i]
  $tile.Save($dest, [System.Drawing.Imaging.ImageFormat]::Png)
  Write-Host "Wrote" $dest "size" $wi "x" $band.Height
  $tile.Dispose()
}

$band.Dispose()
$src.Dispose()
