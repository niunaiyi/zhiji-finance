# 启动脚本 - 知己财务系统
# 用法：在项目根目录运行 .\start.ps1

$ROOT = $PSScriptRoot

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   知己财务系统 - 一键启动" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# ── 检查依赖 ──────────────────────────────────────────────────
$phpOk  = (Get-Command php   -ErrorAction SilentlyContinue) -ne $null
$npmOk  = (Get-Command npm   -ErrorAction SilentlyContinue) -ne $null

if (-not $phpOk)  { Write-Host "[错误] 未找到 php 命令" -ForegroundColor Red;  exit 1 }
if (-not $npmOk)  { Write-Host "[错误] 未找到 npm 命令" -ForegroundColor Red;  exit 1 }

# ── 环境检查 ──────────────────────────────────────────────────
Write-Host "[1/4] 检查环境..." -ForegroundColor Yellow

$envFile = Join-Path $ROOT ".env"
if (-not (Test-Path $envFile)) {
    Write-Host "      [!] 未找到 .env 文件，从 .env.example 复制..." -ForegroundColor Yellow
    Copy-Item (Join-Path $ROOT ".env.example") $envFile
}

# ── 清除 Laravel 缓存 ─────────────────────────────────────────
Write-Host "[2/4] 清除 Laravel 缓存..." -ForegroundColor Yellow
Set-Location $ROOT
php artisan optimize:clear --quiet 2>$null
Write-Host "      完成" -ForegroundColor Green

# ── 安装前端依赖（如 node_modules 不存在）─────────────────────
Write-Host "[3/4] 检查前端依赖..." -ForegroundColor Yellow
$frontendModules = Join-Path $ROOT "frontend\node_modules"
if (-not (Test-Path $frontendModules)) {
    Write-Host "      安装前端依赖（首次运行）..." -ForegroundColor Yellow
    Set-Location (Join-Path $ROOT "frontend")
    npm install --silent
    Write-Host "      完成" -ForegroundColor Green
} else {
    Write-Host "      已存在，跳过" -ForegroundColor Green
}

# ── 启动服务 ──────────────────────────────────────────────────
Write-Host "[4/4] 启动所有服务..." -ForegroundColor Yellow
Write-Host ""

# 配色函数（独立窗口标题）
$laravelArgs = @{
    FilePath         = "pwsh"
    ArgumentList     = "-NoExit", "-Command", "Set-Location '$ROOT'; Write-Host '[Laravel 后端] http://localhost:8000' -ForegroundColor Green; php artisan serve --host=127.0.0.1 --port=8000"
    WindowStyle      = "Normal"
}
$queueArgs = @{
    FilePath         = "pwsh"
    ArgumentList     = "-NoExit", "-Command", "Set-Location '$ROOT'; Write-Host '[Queue Worker] 运行中...' -ForegroundColor Magenta; php artisan queue:work --tries=3 --timeout=60"
    WindowStyle      = "Normal"
}
$frontendArgs = @{
    FilePath         = "pwsh"
    ArgumentList     = "-NoExit", "-Command", "Set-Location '$(Join-Path $ROOT 'frontend')'; Write-Host '[前端 Vite] http://localhost:5173' -ForegroundColor Cyan; npm run dev"
    WindowStyle      = "Normal"
}

# 分别启动三个独立终端窗口
$laravelProc  = Start-Process @laravelArgs  -PassThru
Start-Sleep -Seconds 2   # 等 Laravel 先启动
$queueProc    = Start-Process @queueArgs    -PassThru
$frontendProc = Start-Process @frontendArgs -PassThru

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  所有服务已启动！" -ForegroundColor Green
Write-Host ""
Write-Host "  后端 API  →  http://localhost:8000/api/v1" -ForegroundColor White
Write-Host "  前端界面  →  http://localhost:5173" -ForegroundColor White
Write-Host "  API 文档  →  http://localhost:8000/docs" -ForegroundColor White
Write-Host ""
Write-Host "  按 Ctrl+C 或关闭各窗口来停止服务" -ForegroundColor DarkGray
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
