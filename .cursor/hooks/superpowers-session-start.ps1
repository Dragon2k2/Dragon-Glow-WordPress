# =========================================
# DRAGON GLOW - SESSION BOOTSTRAP
# Cursor sessionStart hook.
#
# Injects navigation aids into the conversation's initial system context so
# the agent does not have to rediscover them every session.
#
# Canonical text lives in BOOTSTRAP.md next to this script. Both this hook and
# the agent's manual fallback read that one file, so there is a single source
# of truth and the injected text is pure ASCII (no PowerShell escaping bugs).
#
# What this hook adds on top of BOOTSTRAP.md:
#   - a sentinel marker, so the agent can tell the hook ran
#   - a live catalog of .cursor/skills (recomputed each run)
#   - live graph stats + staleness warning
#   - a drift detector comparing doc claims with the repo, reporting only
#     discrepancies (silent when everything matches)
#
# IMPORTANT: CLAUDE.md and .cursor/rules/*.mdc are ALREADY auto-loaded by
# Cursor (~14,000 tokens). Never re-inject their contents here.
#
# Cursor pipes hook JSON in on stdin and reads JSON on stdout.
# Output field: additional_context (string).
# Fail-open: any error still produces valid JSON with whatever sections
# succeeded. Never blocks the session.
# =========================================

$ErrorActionPreference = 'Stop'

# Cursor runs project hooks from the project root, so PWD is the repo.
# Fall back to the known path if the cwd ever diverges.
$root = $PWD.Path
if (-not (Test-Path (Join-Path $root '.cursor\skills'))) {
    $root = 'c:\du-an-html\Dragon-Glow-WordPress-main'
}

# Drain stdin - Cursor writes the hook payload there.
try { $null = [Console]::In.ReadToEnd() } catch { }

$sections = New-Object System.Collections.Generic.List[string]

# Sentinel: this exact string is what the always-on rule checks for. If the
# agent does not see it in context, the hook did not run and the agent must
# fall back to reading BOOTSTRAP.md.
$sections.Add('[DG-SESSION-BOOTSTRAP v3]')

# ---------------------------------------------------------------
# Section 1 - canonical bootstrap text
# ---------------------------------------------------------------
$bootstrapPath = Join-Path $PSScriptRoot 'BOOTSTRAP.md'
if (Test-Path $bootstrapPath) {
    $core = Get-Content $bootstrapPath -Raw -Encoding UTF8
    if ($core) { $sections.Add($core.TrimEnd()) }
} else {
    $sections.Add(@'
You have superpowers - process skills at `.cursor/skills/<name>/SKILL.md`.
Invoke the relevant skill BEFORE any response or action. Priority: process
skills first (brainstorming, systematic-debugging), then implementation skills.
This repo has NO test suite - never run `npm test`. Verify with `php -l` and
Playwright instead.
'@)
}

# ---------------------------------------------------------------
# Section 2 - live skill catalog from .cursor/skills
# ---------------------------------------------------------------
try {
    $catalog = New-Object System.Text.StringBuilder
    $count = 0
    $skillDir = Join-Path $root '.cursor\skills'
    if (Test-Path $skillDir) {
        Get-ChildItem $skillDir -Directory | Sort-Object Name | ForEach-Object {
            $skillFile = Join-Path $_.FullName 'SKILL.md'
            if (-not (Test-Path $skillFile)) { return }
            $raw = Get-Content $skillFile -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
            if (-not $raw) { return }
            $desc = ''
            if ($raw -match '(?s)^---\s*\r?\n(.*?)\r?\n---') {
                $fm = $Matches[1]
                if ($fm -match '(?m)^description:\s*"?(.*?)"?\s*$') { $desc = $Matches[1] }
            }
            # Everything emitted must stay ASCII: PowerShell 5.1 reads BOM-less
            # .ps1 files as ANSI and Cursor reads stdout in the host codepage.
            $desc = $desc -replace [char]0x2014, '-' -replace [char]0x2013, '-' `
                          -replace [char]0x2018, "'" -replace [char]0x2019, "'" `
                          -replace [char]0x201C, '"' -replace [char]0x201D, '"' `
                          -replace [char]0x2026, '...'
            $desc = ($desc.ToCharArray() | ForEach-Object { if ([int]$_ -ge 32 -and [int]$_ -le 126) { $_ } else { ' ' } }) -join ''
            if ($desc.Length -gt 95) { $desc = $desc.Substring(0, 92).TrimEnd() + '...' }
            $desc = ($desc -replace '\s+', ' ').Trim()
            [void]$catalog.AppendLine("- $($_.Name): $desc")
            $count++
        }
    }
    if ($count -gt 0) {
        $sections.Add("### Skill catalog ($count skills in .cursor/skills - read the matching SKILL.md before acting)`n`n" + $catalog.ToString().TrimEnd())
    }
} catch {
    # Fail-open: skip the catalog, keep the rest.
}

# ---------------------------------------------------------------
# Section 3 - live graph stats + staleness
# ---------------------------------------------------------------
try {
    $graphPath = Join-Path $root 'graphify-out\graph.json'
    if (Test-Path $graphPath) {
        $stats = $null

        # Prefer node (fast, ~100ms); fall back to PowerShell JSON parsing.
        $nodeCmd = Get-Command node -ErrorAction SilentlyContinue
        if ($nodeCmd) {
            $js = "const g=require(process.argv[1]);const n=g.nodes||[],l=g.links||[];" +
                  "console.log(JSON.stringify({n:n.length,e:l.length,c:new Set(n.map(x=>x.community)).size,commit:(g.built_at_commit||'').slice(0,7)}))"
            $out = & $nodeCmd.Source -e $js $graphPath 2>$null
            if ($LASTEXITCODE -eq 0 -and $out) {
                $stats = $out | ConvertFrom-Json
            }
        }
        if (-not $stats) {
            $g = Get-Content $graphPath -Raw | ConvertFrom-Json
            $comm = $g.nodes | ForEach-Object { $_.community } | Sort-Object -Unique
            $stats = [pscustomobject]@{
                n      = @($g.nodes).Count
                e      = @($g.links).Count
                c      = @($comm).Count
                commit = "$($g.built_at_commit)".Substring(0, [Math]::Min(7, "$($g.built_at_commit)".Length))
            }
        }

        # Compare with current HEAD to detect staleness.
        $head = ''
        $gitCmd = Get-Command git -ErrorAction SilentlyContinue
        if ($gitCmd) {
            Push-Location $root
            try { $head = (& $gitCmd.Source rev-parse --short HEAD 2>$null) } catch { }
            finally { Pop-Location }
        }
        $fresh = ($head -and $head.Trim() -eq "$($stats.commit)")
        $freshLabel = if ($fresh) { 'fresh' } elseif ($head) { "STALE - HEAD is $($head.Trim()) - run the update subcommand" } else { 'freshness unknown' }

        $sections.Add("### Graph status - $($stats.n) nodes, $($stats.e) edges, $($stats.c) communities (built from commit $($stats.commit), $freshLabel)")
    }
} catch {
    # Fail-open: skip the graph stats, keep the rest.
}

# ---------------------------------------------------------------
# Section 4 - drift detector
#
# CLAUDE.md and .cursor/rules/*.mdc are already auto-loaded, so we do NOT
# repeat them. But they contain hard-coded snapshots that rot: node counts,
# skill lists, DG_VERSION, file paths. Verify those against the live repo and
# report ONLY the ones that no longer hold. Silent when everything matches.
# ---------------------------------------------------------------
try {
    $drift = New-Object System.Collections.Generic.List[string]

    # --- Live graph numbers, reused by several checks. ---
    $liveNodes = $null; $liveEdges = $null; $liveComm = $null
    $graphFile = Join-Path $root 'graphify-out\graph.json'
    if (Test-Path $graphFile) {
        try {
            $nodeCmd2 = Get-Command node -ErrorAction SilentlyContinue
            if ($nodeCmd2) {
                $js2 = "const g=require(process.argv[1]);const n=g.nodes||[],l=g.links||[];" +
                       "console.log(JSON.stringify({n:n.length,e:l.length,c:new Set(n.map(x=>x.community)).size}))"
                $o2 = & $nodeCmd2.Source -e $js2 $graphFile 2>$null
                if ($LASTEXITCODE -eq 0 -and $o2) {
                    $s2 = $o2 | ConvertFrom-Json
                    $liveNodes = $s2.n; $liveEdges = $s2.e; $liveComm = $s2.c
                }
            }
        } catch { }
    }

    # --- Check 1: graph stats claimed in CLAUDE.md vs live graph ---
    $claudePath = Join-Path $root 'CLAUDE.md'
    if ((Test-Path $claudePath) -and $null -ne $liveNodes) {
        $claudeText = Get-Content $claudePath -Raw -Encoding UTF8
        if ($claudeText -match '(\d+)\s*nodes\s*[^0-9]+\s*(\d+)\s*edges') {
            $claimN = [int]$Matches[1]
            $claimE = [int]$Matches[2]
            if ($claimN -ne $liveNodes -or $claimE -ne $liveEdges) {
                $drift.Add("- CLAUDE.md claims $claimN nodes / $claimE edges, but graphify-out/graph.json has $liveNodes / $liveEdges. Update the stats block in CLAUDE.md section C.1.")
            }
        }
        if ($claudeText -match '(\d+)\s*communities') {
            $claimC = [int]$Matches[1]
            if ($null -ne $liveComm -and $claimC -ne $liveComm) {
                $drift.Add("- CLAUDE.md claims $claimC communities, but the graph has $liveComm.")
            }
        }
    }

    # --- Check 2: skills listed in CLAUDE.md section C.2 vs disk ---
    # CLAUDE.md lists the 15 superpowers skills in a table while .cursor/skills
    # also holds ~19 WordPress skills, so counting directories would be a false
    # positive. Instead verify every skill the doc NAMES actually exists, and
    # that the claimed count matches the number named.
    $claudeForSkills = Join-Path $root 'CLAUDE.md'
    $skillDir2 = Join-Path $root '.cursor\skills'
    if ((Test-Path $claudeForSkills) -and (Test-Path $skillDir2)) {
        $ct = Get-Content $claudeForSkills -Raw -Encoding UTF8
        if ($ct -match '(?s)###\s*(\d+)\s*skills[^\r\n]*\r?\n(.*?)\r?\n###') {
            $claimedCount = [int]$Matches[1]
            $tableBody = $Matches[2]
            $named = [regex]::Matches($tableBody, '`([a-z0-9][a-z0-9\-]*)`') |
                     ForEach-Object { $_.Groups[1].Value } |
                     Sort-Object -Unique
            $missing = @($named | Where-Object { -not (Test-Path (Join-Path $skillDir2 "$_\SKILL.md")) })
            if ($named.Count -gt 0 -and $named.Count -ne $claimedCount) {
                $drift.Add("- CLAUDE.md heading says $claimedCount skills but the table lists $($named.Count).")
            }
            foreach ($m in $missing) {
                $drift.Add("- CLAUDE.md lists skill '$m' but .cursor/skills/$m/SKILL.md does not exist.")
            }
        }
    }

    # --- Check 2b: superpowers rule's claimed count vs CLAUDE.md's list ---
    $spRule = Join-Path $root '.cursor\rules\superpowers.mdc'
    if ((Test-Path $spRule) -and (Test-Path $claudeForSkills)) {
        $rt = Get-Content $spRule -Raw -Encoding UTF8
        if ($rt -match '(\d+)\s*process skills') {
            $claimSkills = [int]$Matches[1]
            $ct2 = Get-Content $claudeForSkills -Raw -Encoding UTF8
            if ($ct2 -match '(?s)###\s*\d+\s*skills[^\r\n]*\r?\n(.*?)\r?\n###') {
                $named2 = [regex]::Matches($Matches[1], '`([a-z0-9][a-z0-9\-]*)`') |
                          ForEach-Object { $_.Groups[1].Value } | Sort-Object -Unique
                if ($named2.Count -gt 0 -and $claimSkills -ne $named2.Count) {
                    $drift.Add("- .cursor/rules/superpowers.mdc says $claimSkills process skills, but CLAUDE.md names $($named2.Count).")
                }
            }
        }
    }

    # --- Check 3: DG_VERSION claimed anywhere vs functions.php ---
    $fnPhp = Join-Path $root 'wp-content\themes\dragon-glow\functions.php'
    if (Test-Path $fnPhp) {
        $fnText = Get-Content $fnPhp -Raw -Encoding UTF8
        if ($fnText -match "DG_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'") {
            $liveVersion = $Matches[1]
            foreach ($doc in @($claudePath, $spRule)) {
                if (-not (Test-Path $doc)) { continue }
                $t = Get-Content $doc -Raw -Encoding UTF8
                if ($t -match 'DG_VERSION[^\r\n]{0,40}?([0-9]+\.[0-9]+\.[0-9]+)') {
                    $claimed = $Matches[1]
                    if ($claimed -ne $liveVersion) {
                        $drift.Add("- $([IO.Path]::GetFileName($doc)) mentions DG_VERSION $claimed, but functions.php defines $liveVersion.")
                    }
                }
            }
        }
    }

    # --- Check 4: file paths referenced in docs that no longer exist ---
    foreach ($doc in @($claudePath, $spRule, (Join-Path $root '.cursor\rules\graphify.mdc'))) {
        if (-not (Test-Path $doc)) { continue }
        $docText = Get-Content $doc -Raw -Encoding UTF8
        foreach ($m in [regex]::Matches($docText, 'wp-content[\\/]themes[\\/]dragon-glow[\\/][A-Za-z0-9_\-\\/\.]+\.(php|css|js)')) {
            $rel = $m.Value -replace '/', '\'
            if (-not (Test-Path (Join-Path $root $rel))) {
                $drift.Add("- $([IO.Path]::GetFileName($doc)) references $rel, which does not exist.")
            }
        }
    }

    if ($drift.Count -gt 0) {
        $unique = $drift | Sort-Object -Unique
        $sections.Add(@"
### DOCS DRIFT - CLAUDE.md / rules disagree with the repo

CLAUDE.md and the always-on rules are already loaded in your context. These specific claims in them are now FALSE. Trust the repo, not the doc, and fix the doc when you touch that area:

$($unique -join "`n")
"@)
    }
} catch {
    # Fail-open: no drift section if anything goes wrong.
}

# ---------------------------------------------------------------
# Emit
# ---------------------------------------------------------------
$context = ($sections -join "`n`n")
@{ additional_context = $context } | ConvertTo-Json -Compress -Depth 5
exit 0
