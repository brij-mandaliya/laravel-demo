---
name: opencode-commands
description: >
  Lists the slash commands available in the current project. Use whenever the user asks
  "what commands are available", "which commands exist", "what can I run", "/help",
  "list the commands", or similar questions about opencode commands, skills, or the
  .opencode directory in the current repo.
---

# OpenCode Commands

Show the user every available slash command in the current project with a one-line
description for each. Always read the list live — do not rely on memory.

## Steps

1. Scan the command directory:

   ```bash
   ls .opencode/command/*.md
   ```

2. For each file except `opencode-commands.md`, extract the command name and description:

   ```bash
   for f in .opencode/command/*.md; do
       [ "$(basename "$f")" = "opencode-commands.md" ] && continue
       name=$(basename "$f" .md)
       desc=$(awk 'NR > 1 && NF { print; exit }' "$f")
       printf '/%-22s %s\n' "$name" "$desc"
   done
   ```

   - `name` = the file basename without `.md` (this is the slash command the user types, e.g. `commit.md` → `/commit`).
   - `desc` = the first non-empty paragraph line after the H1 (the one-line description).

3. If the scan of the description fails for a file, fall back to reading the first
   non-heading line of that file and summarize it in one line.

4. Present the result as a code block so it reads cleanly in the terminal.

## Output shape

```
/commit                 Create a single-line commit with a conventional commit message.
/create-pr              Open a PR against main with a concise body.
/laravel-setup          Scaffold a fresh Laravel Sail project.
```

If `.opencode/command/` does not exist in the current project, tell the user they have
no custom commands here and point them to `~/.config/opencode` if a global set exists.