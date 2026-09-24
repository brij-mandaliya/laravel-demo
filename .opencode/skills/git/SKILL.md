---
name: git
description: >
  Git and GitHub workflow for this repo: everyday commands, safe commit rules, and how to
  authenticate the gh CLI with GitHub. Use when the user asks about git commands, how to
  commit/push/branch/stash, how to authenticate gh, GitHub auth/login/logout, or any
  git/gh troubleshooting in this project.
---

# Git & GitHub Workflow

Repository-safe git practices plus the exact gh auth commands for this machine.

## Non-negotiables (this project)

- NEVER force-push: no `--force`, `--force-with-lease`, `--force-if-includes`.
- NEVER amend a commit that has already been pushed; use a new commit.
- Never skip hooks (`--no-verify`) unless a pre-existing infra failure is proven; say why.
- Never stage secrets: `.env`, `.env.*`, `*.pem`, `id_rsa*`.

## gh CLI authentication

Check current status:

```bash
gh auth status
```

Login interactively (browser):

```bash
gh auth login
```

Login with a token (CI or headless):

```bash
gh auth login --with-token <<< "ghp_..."
```

Set/refresh the token from an env var:

```bash
gh auth login --with-token <<< "$GITHUB_TOKEN"
```

Logout:

```bash
gh auth logout
```

Transfer a login to another host / protocol:

```bash
gh auth login --hostname github.com --git-protocol ssh
```

Inspect the stored credential:

```bash
gh auth token
```

Test the connection:

```bash
gh api user --jq .login
```

## Everyday git

Inspect:

```bash
git status --short        # working tree
git diff                  # unstaged changes
git diff --cached         # staged changes
git diff --stat           # change summary
git log --oneline -10     # recent commits
git log --oneline --graph --all   # branch topology
git remote -v             # remotes
```

Branching:

```bash
git branch                # local branches
git branch -a             # incl. remote
git checkout -b feat/x    # create + switch
git switch -              # back to previous
git switch main && git pull --ff-only
```

Undoing (local only):

```bash
git restore <file>            # discard unstaged changes to a file
git restore --staged <file>   # unstage
git reset --soft HEAD~1       # undo last commit, keep changes staged
git stash push -m "msg"       # park WIP
git stash pop                 # restore it
git stash list
```

Staging + committing (use `/commit` for the conventional one-liner):

```bash
git add <path>            # stage specific files
git add -p                # stage hunks interactively
git commit -m "feat(x): do thing"
git commit --amend        # ONLY for un-pushed local commits
```

Sharing:

```bash
git push -u origin <branch>   # first push of a branch
git push                      # subsequent
gh pr create --base main      # open a PR (see /create-pr)
```

## gh everyday

```bash
gh pr list                # open PRs
gh pr view <n>            # details
gh pr checkout <n>        # checkout a PR locally
gh repo view --web        # open repo in browser
gh issue list
gh api 'repos/:owner/:repo/commits?per_page=5'
```

## Verify auth is healthy

```bash
gh auth status && gh api user --jq .login && git remote -v
```

If the remote uses SSH (`git@github.com:...`) and pushes fail, confirm the SSH key is
loaded (`ssh -T git@github.com`) or re-login with `gh auth login --git-protocol ssh`.