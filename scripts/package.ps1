$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot

if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'afd-spritpreise.php'))) {
    throw 'Ungültiger Projektordner.'
}

$BuildRoot = Join-Path $ProjectRoot 'build'
$Stage     = Join-Path $BuildRoot 'afd-spritpreise'
$Zip       = Join-Path $BuildRoot 'afd-spritpreise.zip'

# Alten Build sicher entfernen
if (Test-Path -LiteralPath $BuildRoot) {
    $ResolvedBuild   = [System.IO.Path]::GetFullPath($BuildRoot)
    $ResolvedProject = [System.IO.Path]::GetFullPath($ProjectRoot)

    if (-not $ResolvedBuild.StartsWith(
        $ResolvedProject + [System.IO.Path]::DirectorySeparatorChar
    )) {
        throw 'Unsicherer Build-Pfad.'
    }

    Remove-Item -LiteralPath $BuildRoot -Recurse -Force
}

# Staging-Verzeichnis erstellen
New-Item -ItemType Directory -Path $Stage | Out-Null

# Einzeldateien kopieren
foreach ($File in @(
    'afd-spritpreise.php',
    'uninstall.php',
    'README.md',
    'readme.txt',
    'CHANGELOG.md',
    'TESTING.md',
    'LICENSE'
)) {
    $Source = Join-Path $ProjectRoot $File

    if (-not (Test-Path -LiteralPath $Source)) {
        throw "Datei fehlt: $File"
    }

    Copy-Item -LiteralPath $Source -Destination $Stage
}

# Plugin-Verzeichnisse kopieren
foreach ($Directory in @(
    'assets',
    'block',
    'includes'
)) {
    $Source = Join-Path $ProjectRoot $Directory

    if (-not (Test-Path -LiteralPath $Source)) {
        throw "Verzeichnis fehlt: $Directory"
    }

    Copy-Item -LiteralPath $Source -Destination $Stage -Recurse
}

# ZIP mit garantiert WordPress-/Linux-kompatiblen Pfaden erzeugen
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$ZipStream = [System.IO.File]::Open(
    $Zip,
    [System.IO.FileMode]::Create
)

try {
    $Archive = [System.IO.Compression.ZipArchive]::new(
        $ZipStream,
        [System.IO.Compression.ZipArchiveMode]::Create,
        $true
    )

    try {
        $StageFull = [System.IO.Path]::GetFullPath($Stage)

        Get-ChildItem -LiteralPath $Stage -File -Recurse | ForEach-Object {
            $RelativePath = $_.FullName.Substring($StageFull.Length)
            $RelativePath = $RelativePath -replace '^[\\/]+', ''
            $RelativePath = $RelativePath -replace '\\', '/'

            # Oberster Plugin-Ordner muss exakt "afd-spritpreise" heißen
            $EntryName = 'afd-spritpreise/' + $RelativePath

            $Entry = $Archive.CreateEntry(
                $EntryName,
                [System.IO.Compression.CompressionLevel]::Optimal
            )

            $EntryStream = $Entry.Open()

            try {
                $InputStream = [System.IO.File]::OpenRead($_.FullName)

                try {
                    $InputStream.CopyTo($EntryStream)
                }
                finally {
                    $InputStream.Dispose()
                }
            }
            finally {
                $EntryStream.Dispose()
            }
        }
    }
    finally {
        $Archive.Dispose()
    }
}
finally {
    $ZipStream.Dispose()
}

Write-Output "ZIP erstellt:"
Write-Output $Zip