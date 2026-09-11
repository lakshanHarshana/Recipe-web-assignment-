# PowerShell Script: Deploy project to XAMPP htdocs
param (
    [string]$XamppPath = "C:\xampp"
)

$targetDir = Join-Path $XamppPath "htdocs\digital-recipe-book"

if (Test-Path $XamppPath) {
    Write-Host "XAMPP installation found at $XamppPath" -ForegroundColor Green
    Write-Host "Copying project files to $targetDir..." -ForegroundColor Yellow
    
    if (-not (Test-Path $targetDir)) {
        New-Item -ItemType Directory -Force -Path $targetDir | Out-Null
    }

    Copy-Item -Path "$PSScriptRoot\*" -Destination $targetDir -Recurse -Force -Exclude ".git", "setup_xampp.ps1"
    Write-Host "Project successfully deployed to $targetDir!" -ForegroundColor Green
    Write-Host "Next step: Open http://localhost/phpmyadmin and import database.sql" -ForegroundColor Cyan
    Write-Host "Access app at: http://localhost/digital-recipe-book/" -ForegroundColor Cyan
} else {
    Write-Host "XAMPP directory not found at $XamppPath." -ForegroundColor Red
    Write-Host "Please install XAMPP or specify your XAMPP path: .\setup_xampp.ps1 -XamppPath 'D:\xampp'" -ForegroundColor Yellow
}
