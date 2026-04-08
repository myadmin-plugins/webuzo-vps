# Caliber Learnings

Accumulated patterns and anti-patterns from development sessions.
Auto-managed by [caliber](https://github.com/caliber-ai-org/ai-setup) — do not edit manually.

- **[gotcha]** `vendor/bin/phpunit` does not exist within this sub-package directory since it's installed as a vendored dependency under the parent project. Use the globally installed `phpunit` command or run from the parent project root instead.
- **[pattern]** When referencing paths in CLAUDE.md or skill files, always verify they exist with `ls` or `Glob` first. Template placeholders like `{function_name}` and version constraint strings like `^5.0/^6.0/^7.0` get flagged as invalid file path references by config scoring systems — use actual filenames from the project instead.
