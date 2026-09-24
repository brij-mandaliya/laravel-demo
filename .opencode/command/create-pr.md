# Create Pull Request

Open a pull request against `main` with a concise (≤ 30 lines) but genuinely descriptive body.

## Steps

1. Inspect the current state:

   ```bash
   git status --short
   git branch --show-current
   git remote -v
   ```

2. Ensure a remote exists. If none:

   ```bash
   gh repo create <name> --public --source=. --remote=origin --push
   ```

   (Omit `--push` until step 4 if the branch is not committed yet.)

3. Make sure the branch is committed. If there are uncommitted changes, run `/commit` first.

4. Push the feature branch (it must not be `main` — if on `main`, create one first):

   ```bash
   git checkout -b <branch-slug>
   git push -u origin <branch-slug>
   ```

5. Derive the PR title from the conventional commit subject (`git log -1 --pretty=%s`),
   promoted to a sentence-case title. Reuse it verbatim if it reads well as a title.

6. Build the PR body — **at most 30 lines, target ~15–25**. Required sections:

   ```markdown
   ## What
   What this change does, in plain language (2–4 lines).

   ## Why
   The problem/motivation, in one or two sentences.

   ## Changes
   Bullet list of the meaningful pieces:
   - models/migrations (schema)
   - controllers / routes
   - views / components
   - policies / validation
   - tests

   ## How to verify
   Concrete commands and manual steps:
   docker/`sail` commands, test command, and a quick manual walkthrough.

   ## Notes
   Anything worth flagging (deploy concerns, follow-ups). Optional — omit if empty.
   ```

7. Create the PR with a body file (avoids shell-quoting issues):

   ```bash
   cat > /tmp/pr-body.md <<'EOF'
   ...body...
   EOF

   gh pr create --title "<title>" --body-file /tmp/pr-body.md --base main
   ```

8. Report the PR URL when done.

## Rules

- Body **never exceeds 30 lines** (include the blank separator lines in that count).
- Description must name the real pieces of the current code — do not invent details.
- No AI attribution in title or body.
- If a PR already exists for this branch, update it with `gh pr edit` instead of creating a duplicate.

## Acceptance

- `gh pr create`/`gh pr edit` returns a PR URL.
- Body ≤ 30 lines, title set, base `main`.