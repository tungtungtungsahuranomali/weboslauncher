param(
  [string]$DeviceName = "myTV",
  [string]$ServerUrl = "",
  [switch]$Install,
  [switch]$Launch,
  [switch]$Rebuild
)

$WebosDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$BuildDir = Join-Path $WebosDir "..\build"

if ($Rebuild -or !$ServerUrl) {
  Write-Host "=== Building IPK ===" -ForegroundColor Cyan
  if (!(Test-Path $BuildDir)) { New-Item -ItemType Directory -Path $BuildDir -Force | Out-Null }

  if ($ServerUrl) {
    $configJs = Join-Path $WebosDir "config.js"
    Set-Content -Path $configJs -Value "var APP_CONFIG = {`n  defaultServerUrl: '$ServerUrl',`n  appTitle: 'TakeOff Hotel'`n};`n"
  }

  & ares-package --no-minify -o $BuildDir "$WebosDir"
  if ($LASTEXITCODE -ne 0) { Write-Host "Build failed!" -ForegroundColor Red; exit 1 }
  Write-Host "Build success!" -ForegroundColor Green
}

$ipkFile = Get-ChildItem $BuildDir -Filter "*.ipk" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
if (!$ipkFile) { Write-Host "No IPK found in build directory!" -ForegroundColor Red; exit 1 }

if ($Install) {
  Write-Host "=== Installing to $DeviceName ===" -ForegroundColor Cyan
  & ares-install -d $DeviceName $ipkFile.FullName
  if ($LASTEXITCODE -ne 0) { Write-Host "Install failed!" -ForegroundColor Red; exit 1 }
  Write-Host "Install success!" -ForegroundColor Green
}

if ($Launch) {
  Write-Host "=== Launching on $DeviceName ===" -ForegroundColor Cyan
  $params = @{}
  if ($ServerUrl) { $params['serverUrl'] = $ServerUrl }
  if ($params.Count -gt 0) {
    $json = ConvertTo-Json $params -Compress
    & ares-launch -d $DeviceName com.takeoff.launcher -p $json
  } else {
    & ares-launch -d $DeviceName com.takeoff.launcher
  }
  Write-Host "Launch done!" -ForegroundColor Green
}

Write-Host "`nDone!" -ForegroundColor Cyan
