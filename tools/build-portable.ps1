# Builds a native Windows desktop app: WebView2 window + bundled PHP + SQLite.
# Run: powershell -ExecutionPolicy Bypass -File tools/build-portable.ps1

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$dist = Join-Path $root "dist\DE-2027"
$phpSource = "C:\OSPanel\modules\PHP-8.5"
$csc = Join-Path $env:WINDIR "Microsoft.NET\Framework64\v4.0.30319\csc.exe"
$webviewLib = Join-Path $root "tools\desktop\lib"

if (-not (Test-Path $phpSource)) {
    throw "PHP not found: $phpSource"
}

if (-not (Test-Path $csc)) {
    throw "csc.exe not found. Install .NET Framework 4.x."
}

if (-not (Test-Path (Join-Path $webviewLib "Microsoft.Web.WebView2.WinForms.dll"))) {
    throw "WebView2 SDK not found in tools\desktop\lib."
}

Write-Host "1/5 Cleaning dist..."
if (Test-Path $dist) {
    Remove-Item -Recurse -Force $dist
}
New-Item -ItemType Directory -Force -Path $dist | Out-Null

Write-Host "2/5 Copying application..."
robocopy $root (Join-Path $dist "app") /E `
    /XD (Join-Path $root ".git") (Join-Path $root "node_modules") (Join-Path $root "dist") `
        (Join-Path $root "desktop") (Join-Path $root "tools") (Join-Path $root "tests") `
        (Join-Path $root "storage\logs") (Join-Path $root "storage\framework\cache") `
        (Join-Path $root "storage\framework\sessions") (Join-Path $root "storage\framework\views") `
    /XF "*.log" "*.sqlite" "installed" ".phpunit.result.cache" "phpunit.xml" `
        "start.bat" "AGENTS.md" "CLAUDE.md" "DESIGN.md" `
    /NFL /NDL /NJH /NJS /NP | Out-Null

$app = Join-Path $dist "app"
foreach ($dir in @("storage\logs", "storage\framework\cache", "storage\framework\sessions", "storage\framework\views", "storage\app\public")) {
    New-Item -ItemType Directory -Force -Path (Join-Path $app $dir) | Out-Null
}

# Версия кода: по ней EXE понимает, что приложение в %APPDATA% пора обновить.
$buildVersion = Get-Date -Format "yyyyMMddHHmmss"
Set-Content -Path (Join-Path $app "version.txt") -Value $buildVersion -Encoding ASCII

Write-Host "3/5 Copying PHP..."
robocopy $phpSource (Join-Path $dist "runtime\php") /E `
    /XD (Join-Path $phpSource "ospanel_data") (Join-Path $phpSource "extras") (Join-Path $phpSource "3rd-party") `
    /NFL /NDL /NJH /NJS /NP | Out-Null

Write-Host "4/5 Compiling native window..."
$exe = Join-Path $dist "DE-2027.exe"
$source = Join-Path $root "tools\desktop\Program.cs"
& $csc /nologo /target:winexe /platform:x64 /codepage:65001 "/out:$exe" `
    /r:System.Windows.Forms.dll /r:System.Drawing.dll `
    "/r:$(Join-Path $webviewLib 'Microsoft.Web.WebView2.Core.dll')" `
    "/r:$(Join-Path $webviewLib 'Microsoft.Web.WebView2.WinForms.dll')" `
    $source

Copy-Item (Join-Path $webviewLib "*.dll") $dist -Force
Copy-Item (Join-Path $root "desktop\build\icon.ico") (Join-Path $dist "icon.ico") -Force
$launcher = Get-ChildItem (Join-Path $root "tools\desktop") -Filter "*.bat" | Select-Object -First 1
if ($launcher) {
    Copy-Item $launcher.FullName (Join-Path $dist $launcher.Name) -Force
}

Write-Host "5/5 Writing README..."
Copy-Item (Join-Path $root "tools\desktop\README-desktop.txt") (Join-Path $dist "README.txt") -Force

$size = [math]::Round((Get-ChildItem $dist -Recurse -File | Measure-Object Length -Sum).Sum / 1MB, 1)
Write-Host ""
Write-Host "Done: $dist"
Write-Host "Size: $size MB"
Write-Host "Run:  $exe"
