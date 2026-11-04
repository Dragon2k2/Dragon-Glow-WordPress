<#
.SYNOPSIS
    Vendor the Chrome DevTools MCP source into vendor/chrome-devtools-mcp.

.DESCRIPTION
    vendor/ is gitignored, so a fresh clone starts without it. Run this once
    after cloning, or after bumping the version pinned in .cursor/mcp.json.
    Nothing else needs updating after a bump: the MCP server reads its args
    from .cursor/mcp.json at launch (there is no sessionStart hook).

    The MCP server itself runs from npm via npx. The vendored copy is reference
    material (docs + skills), not a dependency of the theme.

.PARAMETER Version
    Release version to fetch, e.g. 1.12.0. Defaults to the version pinned in
    .cursor/mcp.json so the docs and the running server never drift apart.
    Remote tags are named chrome-devtools-mcp-v<version>; the prefix is added
    automatically.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/setup-chrome-devtools-mcp.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/setup-chrome-devtools-mcp.ps1 -Version 1.13.0
#>
[CmdletBinding()]
param(
    [string] $Version = ''
)

$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
$dest = Join-Path $repoRoot 'vendor\chrome-devtools-mcp'
$repoUrl = 'https://github.com/ChromeDevTools/chrome-devtools-mcp.git'

# Pin to whatever the MCP config runs, so docs and server stay in lockstep.
if (-not $Version) {
    $cfg = Join-Path $repoRoot '.cursor\mcp.json'
    if (Test-Path $cfg) {
        $raw = Get-Content $cfg -Raw -Encoding UTF8
        if ($raw -match 'chrome-devtools-mcp@v?([0-9]+\.[0-9]+\.[0-9]+)') { $Version = $Matches[1] }
    }
}
if (-not $Version) { $Version = 'latest' }

# Releases are tagged chrome-devtools-mcp-v<semver>; branches are used as-is.
$ref = if ($Version -eq 'latest') { 'main' } else { "chrome-devtools-mcp-v$Version" }

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw 'git is required on PATH to vendor the source.'
}

Write-Host "Vendoring chrome-devtools-mcp $Version (ref: $ref) into $dest"

if (Test-Path (Join-Path $dest '.git')) {
    Write-Host 'Existing clone found - fetching the requested ref.'
    Push-Location $dest
    try {
        # git writes progress to stderr, and with $ErrorActionPreference = 'Stop'
        # PowerShell turns that into a NativeCommandError. Relax it for the
        # duration of the git calls and check exit codes instead.
        $prevEap = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        try {
            git fetch --depth 1 origin $ref 2>&1 | Out-Null
            if ($LASTEXITCODE -ne 0) { throw "Could not fetch ref '$ref'. Does that release exist?" }
            git checkout --detach FETCH_HEAD 2>&1 | Out-Null
            if ($LASTEXITCODE -ne 0) { throw "Could not check out '$ref'." }
        } finally {
            $ErrorActionPreference = $prevEap
        }
    } finally {
        Pop-Location
    }
} else {
    if (Test-Path $dest) {
        Write-Host 'Removing an incomplete copy before cloning.'
        Remove-Item $dest -Recurse -Force
    }
    New-Item -ItemType Directory -Path (Split-Path -Parent $dest) -Force | Out-Null
    # --branch accepts a tag name, so one clone gets us the exact release.
    $prevEap = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        git clone --depth 1 --branch $ref $repoUrl $dest 2>&1 | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "Clone failed for ref '$ref'." }
    } finally {
        $ErrorActionPreference = $prevEap
    }
}

$pkg = Join-Path $dest 'package.json'
if (-not (Test-Path $pkg)) { throw "Vendored copy looks incomplete: $pkg is missing." }
$ver = (Get-Content $pkg -Raw -Encoding UTF8 | ConvertFrom-Json).version

Write-Host "Done - vendored v$ver."
Write-Host 'The sessionStart hook picks this up automatically in the next new conversation.'
