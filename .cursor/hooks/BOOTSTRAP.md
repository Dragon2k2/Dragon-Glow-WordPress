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

The reporting hook was **deleted** — there is no session-start injection and no
sentinel. The MCP server itself is still registered in `.cursor/mcp.json`, so
the tools work; just call one (e.g. `list_pages`) to check it is alive.

Setup, flags and the Windows `cmd /c` trap live in `CLAUDE.md` section C.1b.

## Docs are already loaded

`CLAUDE.md` and `.cursor/rules/*.mdc` are auto-loaded by Cursor. Do not
re-read them unless you need to edit them. Trust them over this summary when
they conflict, EXCEPT where the hook reports drift.
