# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-09-10

### Dependencies

- **deps:** Bump orhun/git-cliff-action from 4.8.0 to 4.9.0

## [0.1.1] - 2026-09-08

### Bug Fixes

- **deps:** Update guzzlehttp/guzzle to patch security advisories
- **ci:** Publish release as draft until PHAR asset is attached

### CI/CD

- Pin actions to commit SHA, add dependabot cooldown/composer, trim dist archive
- **release:** Generate CHANGELOG.md and release notes with git-cliff

### Dependencies

- **deps:** Bump actions/checkout from 6.1.0 to 7.0.1
- **deps:** Bump actions/cache from 5.1.0 to 6.1.0
- **deps:** Bump shivammathur/setup-php

### Documentation

- Add Buy Me a Coffee sponsor link
- Standardize README section structure

### Miscellaneous Tasks

- Bump guzzlehttp/guzzle and guzzlehttp/psr7 for security advisories
- Add GitHub Sponsors to FUNDING.yml

## [0.1.0] - 2026-07-15

### Other

- Initial commit: plump-cli

CLI for the Plumb (plumbphp.dev) public JSON API — show package quality
scores, trigger scans, and browse scan history from the terminal.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
- Add portfolio banner

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
- Switch CI to the release-driven publish flow used by other CLIs

release.yml required a manual workflow_dispatch with the version typed
in by hand. The rest of the fleet (bb-cli, jira-cli, etc.) instead lets
creating a GitHub Release do the work: publish-phar.yml builds and
attaches the PHAR, update-changelog.yml updates CHANGELOG.md and bumps
version.txt to the release tag, and build.yml (also listening for that
workflow) rebuilds builds/plump with the new version baked in.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
- Replace CI publish chain with filakit-cli's self-contained release.yml

The build.yml + publish-phar.yml + update-changelog.yml cascade only
works when the release is created by a human outside CI: a release
created from within a workflow using GITHUB_TOKEN never fires the
'release: created' event (GitHub's anti-recursion rule), so nothing
downstream would run. filakit-cli's release.yml sidesteps this
entirely — one workflow_dispatch job resolves the next version (or
auto-bumps the patch), builds the PHAR, commits it to main, tags that
exact commit, and uploads the asset, all in the same run.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>


