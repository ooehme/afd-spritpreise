$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'afd-spritpreise.php'))) {
    throw 'Ungültiger Projektordner.'
}

$BuildRoot = Join-Path $ProjectRoot 'build'
$Stage = Join-Path $BuildRoot 'afd-spritpreise'
$Zip = Join-Path $BuildRoot 'afd-spritpreise.zip'

if (Test-Path -LiteralPath $BuildRoot) {
    $ResolvedBuild = [System.IO.Path]::GetFullPath($BuildRoot)
    $ResolvedProject = [System.IO.Path]::GetFullPath($ProjectRoot)
    if (-not $ResolvedBuild.StartsWith($ResolvedProject + [System.IO.Path]::DirectorySeparatorChar)) {
        throw 'Unsicherer Build-Pfad.'
    }
    Remove-Item -LiteralPath $BuildRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $Stage | Out-Null
foreach ($File in @('afd-spritpreise.php', 'uninstall.php', 'README.md', 'readme.txt', 'CHANGELOG.md', 'TESTING.md', 'LICENSE')) {
    Copy-Item -LiteralPath (Join-Path $ProjectRoot $File) -Destination $Stage
}
foreach ($Directory in @('assets', 'block', 'includes')) {
    Copy-Item -LiteralPath (Join-Path $ProjectRoot $Directory) -Destination $Stage -Recurse
}

Compress-Archive -LiteralPath $Stage -DestinationPath $Zip -CompressionLevel Optimal
Write-Output $Zip
