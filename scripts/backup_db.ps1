param(
    [Parameter(Mandatory=$true)][string]$Host,
    [Parameter(Mandatory=$true)][string]$Port,
    [Parameter(Mandatory=$true)][string]$User,
    [Parameter(Mandatory=$true)][string]$Database,
    [Parameter(Mandatory=$true)][string]$OutputDir
)

if (!(Test-Path $OutputDir)) {
    New-Item -Path $OutputDir -ItemType Directory | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$outFile = Join-Path $OutputDir "$Database`_$timestamp.sql"

Write-Host "Nhap mat khau MySQL khi duoc hoi..."
mysqldump -h $Host -P $Port -u $User --single-transaction --routines --triggers --events $Database > $outFile

if ($LASTEXITCODE -eq 0) {
    Write-Host "Backup thanh cong: $outFile"
} else {
    Write-Host "Backup that bai" -ForegroundColor Red
    exit 1
}
