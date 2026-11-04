# =========================================
# DRAGON GLOW - CHROME DEVTOOLS MCP BOOTSTRAP
# Cursor sessionStart hook (second entry, independent of superpowers).
#
# Tells the agent that Chrome DevTools MCP is available in this repo, where the
# vendored source and docs live, which skills ship with it, and which tools are
# ACTUALLY exposed right now - without the agent having to grep 17 MB of vendor
# code or guess at a tool name that the running server does not provide.
#
# Everything here is DERIVED AT RUN TIME from disk or from the live server:
#   - version        <- vendor/chrome-devtools-mcp/package.json
#   - skill catalog  <- vendor/chrome-devtools-mcp/skills/*/SKILL.md frontmatter
#   - tool names     <- live `tools/list` handshake, cached per version
#   - doc groups     <- vendor/chrome-devtools-mcp/docs/tool-reference.md
#   - server status  <- .cursor/mcp.json
# Bump the vendor clone or change the MCP args and the output follows.
#
# The live probe costs ~3s cold, so its result is cached in
# .cursor/hooks/.chrome-devtools-tools-cache.json keyed by server version.
# If the probe fails, the hook falls back to the doc list and says so rather
# than pretending the tool exists.
#
# Fail-open: any error still emits valid JSON. Never blocks the session.
# ASCII only: PS 5.1 reads a BOM-less .ps1 as ANSI, so never emit raw Unicode.
#
# Cursor pipes hook JSON in on stdin and reads JSON on stdout.
# Output field: additional_context (string).
#
# Sentinel: the emitted context starts with [DG-CHROME-DEVTOOLS v1]. If an
# agent cannot find that string in its system context, this hook did not run,
# so none of the tool claims below are trustworthy. Documented in CLAUDE.md
# section C.1b.
# =========================================

$ErrorActionPreference = 'Stop'

$root = $PWD.Path
if (-not (Test-Path (Join-Path $root '.cursor\skills'))) {
    $root = 'c:\du-an-html\Dragon-Glow-WordPress-main'
}

# Drain stdin - Cursor writes the hook payload there.
try { $null = [Console]::In.ReadToEnd() } catch { }

$sections = New-Object System.Collections.Generic.List[string]

# Collapse a string to plain printable ASCII. The vendored skills and docs are
# full of em dashes and smart quotes; emitting those raw produces mojibake.
function ConvertTo-Ascii([string] $s) {
    if (-not $s) { return '' }
    $s = $s -replace [char]0x2014, '-' -replace [char]0x2013, '-' `
            -replace [char]0x2018, "'" -replace [char]0x2019, "'" `
            -replace [char]0x201C, '"' -replace [char]0x201D, '"' `
            -replace [char]0x2026, '...'
    $s = ($s.ToCharArray() | ForEach-Object { if ([int]$_ -ge 32 -and [int]$_ -le 126) { $_ } else { ' ' } }) -join ''
    return ($s -replace '\s+', ' ').Trim()
}

try {
    $vendor = Join-Path $root 'vendor\chrome-devtools-mcp'
    $mcpCfg = Join-Path $root '.cursor\mcp.json'
    $docsDir = Join-Path $vendor 'docs'
    $cacheFile = Join-Path $root '.cursor\hooks\.chrome-devtools-tools-cache.json'

    if (-not (Test-Path $vendor)) {
        # Nothing vendored: say how to get it and stop. No point listing skills.
        # Single-quoted here-string: a double-quoted one expands backticks and
        # silently eats characters.
        $sections.Add(@'
[DG-CHROME-DEVTOOLS v1 | NOT VENDORED]

### Chrome DevTools MCP - source not vendored

vendor/chrome-devtools-mcp is missing. Restore it with:

    powershell -ExecutionPolicy Bypass -File scripts/setup-chrome-devtools-mcp.ps1

Until then no chrome-devtools MCP tools are available. Do not claim you can
drive a browser.
'@)
    } else {
        $lines = New-Object System.Collections.Generic.List[string]

        # ---- version from the vendored manifest, so it tracks the clone ----
        $ver = 'unknown'
        $pkgPath = Join-Path $vendor 'package.json'
        if (Test-Path $pkgPath) {
            try {
                $pkg = Get-Content $pkgPath -Raw -Encoding UTF8 | ConvertFrom-Json
                if ($pkg.version) { $ver = "$($pkg.version)" }
            } catch { }
        }

        # ---- is the server registered, and how is it launched? ----
        $registered = $false
        $launchArgs = ''
        $commandName = ''
        if (Test-Path $mcpCfg) {
            try {
                $cfg = Get-Content $mcpCfg -Raw -Encoding UTF8 | ConvertFrom-Json
                $server = $cfg.mcpServers.'chrome-devtools'
                if ($null -ne $server) {
                    $registered = $true
                    $commandName = "$($server.command)"
                    $argList = @()
                    foreach ($a in @($server.args)) {
                        if ("$a" -notmatch '^-y$') { $argList += "$a" }
                    }
                    $launchArgs = ($argList -join ' ')
                }
            } catch { }
        }

        # Windows trap: Cursor spawns stdio servers WITHOUT a shell. Node then
        # refuses to run a .cmd shim (CVE-2024-27980) and a bare "npx" is not an
        # executable at all, so the server registers and never starts - it sits
        # at "disconnected" forever with no error in the UI. Wrapping the launch
        # in cmd /c is the portable fix.
        $usesCmdWrapper = ($commandName -match '^(cmd|cmd\.exe)$')

        # ---- Chrome presence: the server drives a real browser ----
        $chromeFound = $false
        foreach ($candidate in @(
            "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
            "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
            "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe"
        )) {
            if ($candidate -and (Test-Path $candidate)) { $chromeFound = $true; break }
        }

        $status = if ($registered) { "registered in .cursor/mcp.json (launch: $launchArgs)" } else { 'NOT registered - no chrome-devtools tools are available until .cursor/mcp.json lists it' }
        $chromeLabel = if ($chromeFound) { 'found' } else { 'NOT FOUND - install Chrome, this server launches it' }

        $lines.Add("Vendored source: ``vendor/chrome-devtools-mcp`` (v$ver, from github.com/ChromeDevTools/chrome-devtools-mcp). Read it before guessing at tool behaviour.")
        $lines.Add("MCP server: $status. Chrome: $chromeLabel.")
        if ($registered -and -not $usesCmdWrapper) {
            $lines.Add('WARNING: launched without a cmd /c wrapper. On Windows Cursor spawns stdio servers shell-free, so npx fails with ENOENT and the server stays disconnected. Use command "cmd" with args ["/c","npx","-y","chrome-devtools-mcp@<version>"].')
        }
        $lines.Add('It is a stdio MCP server run through npx; no build step, nothing in the theme imports it, and the WordPress constraints in CLAUDE.md still apply. It only inspects a browser.')

        # ---- live tool list, cached per server version ----
        $liveTools = $null
        $probeState = ''
        if (Test-Path $cacheFile) {
            try {
                $cached = Get-Content $cacheFile -Raw -Encoding UTF8 | ConvertFrom-Json
                if ("$($cached.version)" -eq $ver -and "$($cached.args)" -eq $launchArgs -and $cached.tools) {
                    $liveTools = @($cached.tools)
                    $probeState = 'cached'
                }
            } catch { }
        }

        if ($null -eq $liveTools -and $registered) {
            # Cold probe: one initialize + tools/list handshake, then kill it.
            # It replays the category flags from .cursor/mcp.json, so turning a
            # category on in the config changes the reported tool list instead of
            # the hook quietly reporting the default surface. Args travel through
            # a temp JSON file to sidestep shell quoting. The probe spawns through
            # a shell on purpose, which is immune to the .cmd restriction that
            # affects Cursor's own spawn.
            $probeSource = @'
const { spawn } = require('child_process');
const fs = require('fs');
const raw = fs.readFileSync(process.argv[2], 'utf8').replace(/^\uFEFF/, '');
const args = JSON.parse(raw);
const p = spawn('npx.cmd', args, { shell: true, env: Object.assign({}, process.env, {
  CHROME_DEVTOOLS_MCP_NO_USAGE_STATISTICS: '1',
  CHROME_DEVTOOLS_MCP_NO_UPDATE_CHECKS: '1'
})});
let buf = ''; let done = false;
const finish = (out) => { if (done) return; done = true; if (out) console.log(out); try { p.kill(); } catch (e) {} process.exit(0); };
setTimeout(() => finish(''), 10000);
p.on('error', () => finish(''));
p.stdout.on('data', (d) => {
  buf += d.toString();
  for (const line of buf.split('\n')) {
    if (!line.trim()) continue;
    let j; try { j = JSON.parse(line); } catch (e) { continue; }
    if (j.id === 1) {
      p.stdin.write(JSON.stringify({ jsonrpc: '2.0', id: 2, method: 'tools/list', params: {} }) + '\n');
    } else if (j.id === 2 && j.result) {
      finish(JSON.stringify((j.result.tools || []).map((t) => t.name).sort()));
    }
  }
});
p.stdin.write(JSON.stringify({
  jsonrpc: '2.0', id: 1, method: 'initialize',
  params: { protocolVersion: '2024-11-05', capabilities: {}, clientInfo: { name: 'dg-hook', version: '1' } }
}) + '\n');
'@
            try {
                # Only the long options matter; /c, npx and -y are launch plumbing.
                $probeArgs = @('-y', "chrome-devtools-mcp@$ver")
                foreach ($a in @($server.args)) {
                    if ("$a" -like '--*') { $probeArgs += "$a" }
                }
                $probePath = Join-Path $env:TEMP "dg-mcp-probe-$PID.js"
                $argsPath = Join-Path $env:TEMP "dg-mcp-args-$PID.json"
                Set-Content -Path $probePath -Value $probeSource -Encoding UTF8
                $probeArgs | ConvertTo-Json -Compress | Set-Content $argsPath -Encoding UTF8
                $raw = & node $probePath $argsPath 2>$null
                Remove-Item $probePath -Force -ErrorAction SilentlyContinue
                Remove-Item $argsPath -Force -ErrorAction SilentlyContinue
                if ($raw) {
                    $parsed = $raw | ConvertFrom-Json
                    if ($parsed -and @($parsed).Count -gt 0) {
                        $liveTools = @($parsed)
                        $probeState = 'live'
                        try {
                            @{ version = $ver; args = $launchArgs; tools = $liveTools } |
                                ConvertTo-Json -Compress | Set-Content $cacheFile -Encoding UTF8
                        } catch { }
                    }
                }
            } catch { }
        }

        # ---- doc groups, used to label the live tools ----
        $docGroups = New-Object System.Collections.Generic.List[string]
        try {
            $toolRef = Join-Path $docsDir 'tool-reference.md'
            if (Test-Path $toolRef) {
                $current = ''
                $names = New-Object System.Collections.Generic.List[string]
                foreach ($ln in ((Get-Content $toolRef -Raw -Encoding UTF8) -split "`r?`n")) {
                    if ($ln -match '^##\s+(.+?)\s*$') {
                        if ($current -and $names.Count -gt 0) { $docGroups.Add("$current|" + ($names -join ',')) }
                        $current = $Matches[1]
                        $names = New-Object System.Collections.Generic.List[string]
                    } elseif ($ln -match '^###\s+`([^`]+)`') {
                        $names.Add($Matches[1])
                    }
                }
                if ($current -and $names.Count -gt 0) { $docGroups.Add("$current|" + ($names -join ',')) }
            }
        } catch { }

        if ($null -ne $liveTools) {
            $lines.Add('')
            $lines.Add("Tools exposed right now: $($liveTools.Count) ($probeState).")
            if ($docGroups.Count -gt 0) {
                $lines.Add('')
                $lines.Add('By group - only the ones listed as available can be called as-is:')
                $lines.Add('')
                foreach ($g in $docGroups) {
                    $parts = $g -split '\|', 2
                    $gname = $parts[0]
                    $gnames = @($parts[1] -split ',')
                    $available = @($gnames | Where-Object { $liveTools -contains $_ })
                    if ($available.Count -gt 0) {
                        $shown = ($available | Select-Object -First 7) -join ', '
                        $extra = if ($available.Count -gt 7) { " (+$($available.Count - 7) more)" } else { '' }
                        $lines.Add("- $gname - available: $shown$extra")
                    } else {
                        $lines.Add("- $gname - NOT exposed by the current server config. Needs an extra flag in .cursor/mcp.json args; see docs/configuration.md before offering it.")
                    }
                }
            }
        } elseif ($docGroups.Count -gt 0) {
            $lines.Add('')
            $lines.Add('Tool groups from docs (server not probed, so availability is UNVERIFIED - call tools/list or read docs/tool-reference.md before relying on a name):')
            $lines.Add('')
            foreach ($g in $docGroups) {
                $parts = $g -split '\|', 2
                $count = @($parts[1] -split ',').Count
                $lines.Add("- $($parts[0]) ($count tools)")
            }
        }

        # ---- bundled skills, scanned from disk ----
        try {
            $skillDir = Join-Path $vendor 'skills'
            $cat = New-Object System.Text.StringBuilder
            $skillCount = 0
            if (Test-Path $skillDir) {
                Get-ChildItem $skillDir -Directory -ErrorAction SilentlyContinue | Sort-Object Name | ForEach-Object {
                    $sf = Join-Path $_.FullName 'SKILL.md'
                    if (-not (Test-Path $sf)) { return }
                    $rawSkill = Get-Content $sf -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
                    if (-not $rawSkill) { return }
                    $desc = ''
                    if ($rawSkill -match '(?s)^---\s*\r?\n(.*?)\r?\n---') {
                        if ($Matches[1] -match '(?m)^description:\s*"?(.*?)"?\s*$') { $desc = $Matches[1] }
                    }
                    $desc = ConvertTo-Ascii $desc
                    if ($desc.Length -gt 130) { $desc = $desc.Substring(0, 127).TrimEnd() + '...' }
                    [void]$cat.AppendLine("- $($_.Name): $desc")
                    $skillCount++
                }
            }
            if ($skillCount -gt 0) {
                $lines.Add('')
                $lines.Add("Bundled skills ($skillCount) - read ``vendor/chrome-devtools-mcp/skills/<name>/SKILL.md`` before doing that kind of work:")
                $lines.Add('')
                $lines.Add($cat.ToString().TrimEnd())
            }
        } catch { }

        # ---- remaining docs on disk ----
        try {
            $docList = @(
                @('tool-reference.md', 'full tool signatures - read before calling anything unusual'),
                @('configuration.md', 'every server flag (--headless, --isolated, --slim, category toggles)'),
                @('advanced-usage.md', 'connect to a running Chrome, persistent profiles, Android'),
                @('troubleshooting.md', 'connection / launch failures'),
                @('design-principles.md', 'why the tool surface is shaped this way')
            )
            $docLines = New-Object System.Collections.Generic.List[string]
            foreach ($d in $docList) {
                if (Test-Path (Join-Path $docsDir $d[0])) { $docLines.Add("- ``vendor/chrome-devtools-mcp/docs/$($d[0])`` - $($d[1])") }
            }
            if ($docLines.Count -gt 0) {
                $lines.Add('')
                $lines.Add('Docs on disk:')
                $lines.Add('')
                foreach ($l in $docLines) { $lines.Add($l) }
            }
        } catch { }

        # Sentinel first: seeing this string in system context is the only
        # proof that this hook ran. Keep it on line 1 of the section.
        $sections.Add("[DG-CHROME-DEVTOOLS v1]`n`n### Chrome DevTools MCP`n`n" + (($lines -join "`n")))
    }
} catch {
    # Fail-open: emit whatever got built, even if empty.
}

$context = ($sections -join "`n`n")
@{ additional_context = $context } | ConvertTo-Json -Compress -Depth 5
exit 0
