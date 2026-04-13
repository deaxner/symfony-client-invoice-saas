$preferredDbPort = 3308
$maxDbPort = 3400
$selectedDbPort = $preferredDbPort

while ($selectedDbPort -le $maxDbPort) {
    $inUse = Get-NetTCPConnection -LocalPort $selectedDbPort -ErrorAction SilentlyContinue
    if (-not $inUse) {
        break
    }

    $selectedDbPort++
}

if ($selectedDbPort -gt $maxDbPort) {
    throw "No free MySQL port found between $preferredDbPort and $maxDbPort."
}

$preferredAppPort = 8085
$maxAppPort = 8185
$selectedAppPort = $preferredAppPort

while ($selectedAppPort -le $maxAppPort) {
    $inUse = Get-NetTCPConnection -LocalPort $selectedAppPort -ErrorAction SilentlyContinue
    if (-not $inUse) {
        break
    }

    $selectedAppPort++
}

if ($selectedAppPort -gt $maxAppPort) {
    throw "No free app port found between $preferredAppPort and $maxAppPort."
}

$networkName = "portfolio_suite_network"
$existingNetwork = docker network ls --format "{{.Name}}" | Where-Object { $_ -eq $networkName }
if (-not $existingNetwork) {
    docker network create $networkName | Out-Null
}

$env:MYSQL_PORT = [string]$selectedDbPort
$env:APP_PORT = [string]$selectedAppPort

Write-Host "Using app host port $selectedAppPort"
Write-Host "Using MySQL host port $selectedDbPort"
Write-Host "Using shared Docker network $networkName"
docker compose up --build -d

if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host "App available at http://127.0.0.1:$selectedAppPort"
Write-Host "Database available at 127.0.0.1:$selectedDbPort"
