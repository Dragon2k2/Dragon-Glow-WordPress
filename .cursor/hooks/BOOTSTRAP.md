# Session Bootstrap - Dragon Glow

> Canonical bootstrap text. The `sessionStart` hook injects this on every new
> conversation. If the agent reads this manually, the hook did not fire - read
> it once and proceed normally.

## Superpowers

Process skills live at `.cursor/skills/<name>/SKILL.md`.

**Invoke the relevant skill BEFORE any response or action** - before clarifying
questions, before reading code, before grep. If there is even a 1% chance a
skill applies, read it. References written as `superpowers:<name>` mean
`.cursor/skills/<name>/SKILL.md`.

Announce one line ("Dung skill <name> de <purpose>"), then follow the skill
exactly. If it has a checklist, create a todo per item.

Priority: process skills first, then implementation skills.

- "Build feature X" -> brainstorming, then implementation skills.
- "Fix this bug" -> systematic-debugging, then domain skills.

Workflow: brainstorming -> writing-plans -> executing-plans or
subagent-driven-development -> verification-before-completion ->
finishing-a-development-branch.

### Project adaptation (overrides the skills where they conflict)

- This repo has NO test suite. There is no `npm test`. Never invent or run one.
- TDD RED to GREEN maps to observable verification: `php -l` for PHP syntax,
  Playwright for render/responsive at 640/768/1024px, WooCommerce on/off,
  `prefers-reduced-motion`. Confirm the wrong behavior before the fix, the
  right behavior after.
- Bundled bash scripts run via Git Bash:
  `bash .cursor/skills/<skill>/scripts/<name>`.
- Project constraints win: no React, no build step, PHP templates + vanilla JS,
  token-first CSS, BEM `dg-`.
- Only dispatch subagents when the user explicitly asks.

## Knowledge graph

This repo has an AST knowledge graph at `graphify-out/graph.json` covering the
theme code (the inc PHP folder, assets/js, template-parts data files). It
answers cross-file questions that grep cannot: who calls what, dependency
paths, architectural hubs.

**Before exploring code for any architecture question, query the graph first:**

- `query "<question>"` - scoped subgraph around matching nodes (BFS)
- `path "<A>" "<B>"` - shortest dependency path between two symbols
- `explain "<concept>"` - every node related to a concept
- `god-nodes --top 20` - architectural hubs

Use Read/Grep directly only when the graph has already oriented you and you
need exact lines, or when `graphify-out/graph.json` is missing. CSS is not
parsed by graphify, so styling questions still need grep.

The graphify CLI is usually NOT on PATH. Invoke it by full path:
`& "$env:USERPROFILE\.local\bin\graphify.exe" query "<question>"`
(or `uv tool run graphify ...`).

After changing PHP/JS, refresh with the `update` subcommand (AST-only, no API
cost).

Scope note: `.graphifyignore` excludes dot-folders (.claude, .cursor, .agents),
node_modules, graphify-out, scripts, and stitch_dragon_glow_* folders. Plain
PHP templates with no function declarations yield no symbols, so files like
404.php and footer.php are absent by design.

## Chrome DevTools MCP

Sentinel: `[DG-CHROME-DEVTOOLS v1]`. It leads the chrome-devtools section of
the system context and is emitted by its own `sessionStart` entry in
`.cursor/hooks.json`. Variant `[DG-CHROME-DEVTOOLS v1 | NOT VENDORED]` still
means the hook ran but `vendor/chrome-devtools-mcp` is missing.

Seeing the sentinel proves the HOOK ran. It does NOT prove the MCP server is
alive - check the next lines for `MCP server: registered ...` and
`Tools exposed right now: <n>`. Grep `tools/list` yourself before claiming a
tool exists.

Facts that are easy to get wrong:

- The server exposes 32 tools by default. The current `.cursor/mcp.json` turns
  on six flags (memoryDebugging, categoryExtensions,
  categoryExperimentalThirdParty, categoryExperimentalWebmcp, categoryPwa,
  experimentalVision) for 59 tools, which is everything npm `1.12.0` has.
- GitHub `main` documents 61 tools (`set_logpoints`, `configure_webauthn`),
  but npm `latest` is still `1.12.0`. Docs run ahead of releases: verify with
  `npm view chrome-devtools-mcp dist-tags` instead of trusting a doc count.
- On Windows the launch MUST be wrapped as `command: cmd`,
  `args: ["/c","npx",...]`. Cursor spawns stdio servers without a shell, so a
  bare `npx` sits at "disconnected" forever with no error in the UI.
- Read `vendor/chrome-devtools-mcp/docs/configuration.md` before adding a flag,
  and `docs/tool-reference.md` before calling an unfamiliar tool. `--slim`
  reduces the surface to 3 tools if that is ever what you want.
- This only inspects a browser. It does not relax any constraint above.

## Docs are already loaded

`CLAUDE.md` and `.cursor/rules/*.mdc` are auto-loaded by Cursor. Do not
re-read them unless you need to edit them. Trust them over this summary when
they conflict, EXCEPT where the hook reports drift.
