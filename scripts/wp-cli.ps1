param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$WpCliArgs
)

$ErrorActionPreference = 'Stop'

function Resolve-Binary {
    param(
        [string[]]$Candidates,
        [string]$Description
    )

    foreach ($candidate in $Candidates) {
        if (-not $candidate) {
            continue
        }

        $expanded = [Environment]::ExpandEnvironmentVariables($candidate)
        try {
            $resolved = Resolve-Path -LiteralPath $expanded -ErrorAction Stop
            if (Test-Path -LiteralPath $resolved.Path -PathType Leaf) {
                return $resolved.Path
            }
        }
        catch {
            continue
        }
    }

    throw "Unable to locate $Description. Checked: $($Candidates -join ', ')"
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$defaultPhpCandidates = @(
    $env:WP_LOCAL_PHP
    (Join-Path $repoRoot 'tools\PHP\8.3\php.exe')
    'c:\tools\PHP\8.3\php.exe'
)
$defaultWpCliCandidates = @(
    $env:WP_LOCAL_WPCLI
    (Join-Path $repoRoot 'tools\wp-cli\wp-cli.phar')
    'c:\tools\wp-cli\wp-cli.phar'
)

$phpPath = Resolve-Binary -Candidates $defaultPhpCandidates -Description 'PHP executable (set WP_LOCAL_PHP to override)'
$wpCliPath = Resolve-Binary -Candidates $defaultWpCliCandidates -Description 'wp-cli.phar (set WP_LOCAL_WPCLI to override)'

$hasPathSwitch = $false
for ($i = 0; $i -lt $WpCliArgs.Count; $i++) {
    $arg = $WpCliArgs[$i]
    if ($arg -like '--path=*') {
        $hasPathSwitch = $true
        break
    }

    if ($arg -eq '--path' -and $i -lt ($WpCliArgs.Count - 1)) {
        $hasPathSwitch = $true
        break
    }
}

if (-not $hasPathSwitch) {
    $defaultPath = Join-Path $repoRoot 'html'
    $WpCliArgs = @('--path=' + $defaultPath) + $WpCliArgs
}

& $phpPath $wpCliPath @WpCliArgs
