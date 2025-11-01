param(
    [Parameter(Mandatory = $true)]
    [ValidateNotNullOrEmpty()]
    [string]$Url,

    [ValidateNotNullOrEmpty()]
    [string]$WpCliPath = "C:\\tools\\wp-cli\\wp.bat",

    [string]$SitePath
)

$ErrorActionPreference = 'Stop'

if (-not $SitePath) {
    $SitePath = Resolve-Path (Join-Path $PSScriptRoot '..' 'html')
}

if (-not (Test-Path $WpCliPath)) {
    throw "WP-CLI executable not found at '$WpCliPath'. Update the -WpCliPath parameter if it's installed elsewhere."
}

if (-not (Test-Path $SitePath)) {
    throw "Unable to find the WordPress install at '$SitePath'. Pass -SitePath with the correct directory."
}

function Invoke-WpCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Arguments
    )

    & $WpCliPath "--path=$SitePath" @Arguments '--skip-plugins' '--skip-themes'
}

$normalizedUrl = $Url.TrimEnd('/')

Invoke-WpCommand -Arguments @('option', 'update', 'home', $normalizedUrl, '--quiet') | Out-String | Write-Verbose
Invoke-WpCommand -Arguments @('option', 'update', 'siteurl', $normalizedUrl, '--quiet') | Out-String | Write-Verbose
Invoke-WpCommand -Arguments @('cache', 'flush', '--quiet') | Out-String | Write-Verbose

Write-Host "Home and Site URL updated to $normalizedUrl" -ForegroundColor Green
Write-Host "WordPress cache flushed." -ForegroundColor Green
