# Conventional Commit

Create a single-line commit with a conventional commit message. No AI attribution.

## Steps

1. Inspect the work:

   ```bash
   git status --short
   git diff --stat && git diff
   ```

2. Stage only files intended for this commit. NEVER stage:
   - secrets (`*.env`, `.env.*`, `.pem`, `id_rsa*`, `.env.production`)
   - build artifacts (`node_modules/`, `vendor/`, `public/build/`, `storage/logs/*`)
   - large binaries or anything unrelated to the change

   ```bash
   git add <file1> <file2> ...   # list files explicitly; avoid `git add -A` unless every dirty file belongs
   ```

3. Write ONE commit message line — subject only, no body, no hashtags.

   Format: `type(scope): subject`

   - `type`: `feat` | `fix` | `refactor` | `docs` | `test` | `chore` | `style`
   - `scope` (optional): short area, e.g. `posts`, `comments`, `db`, `views`, `deploy`
   - `subject`: imperative mood, lowercase, ≤ 50 chars, no trailing period
     - good: `feat(posts): add slug-based post routes`
     - bad: `Added new feature stuff and fixed things!!`

4. Commit the staged changes:

   ```bash
   git commit -m "<message>"
   ```

## Rules

- **Exactly one line.** Never add a body (no `-m` with a second string, no heredoc).
- **No AI attribution.** Do not add "Generated with opencode", "Co-authored-by: ...", "🤖",
  "via opencode", or any other generator/author tags. Plain human message only.
- If the repo has hooks that reject the static analysis output, run the project's lint
  (`./vendor/bin/sail pint`) and typecheck/test checks, fix, then commit again — a NEW
  commit, never an amend of a pushed commit.

## Acceptance

- `git log -1 --pretty=%s` shows `type(scope): subject` at ≤ 50 chars.
- `git show --stat --format= HEAD` lists only the intended files.
- Message contains no AI attribution.