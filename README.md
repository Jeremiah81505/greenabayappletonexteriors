# Green Bay & Appleton Exterior Solutions

WordPress content and configuration extracted from the legacy GoDaddy/SiteGround stack. This repository is the working tree for rebuilding the site on neutral infrastructure (local Docker, generic VPS, or managed WordPress host).

## Stack Snapshot

- **WordPress 6.8.3** with core files under `html/`.
- **Theme & Builder:** Elementor 3.32.5 + Elementor Pro 3.32.3, Essential Addons Pro 6.7.0, Hello Biz customizations.
- **Forms & Payments:** Gravity Forms 2.9.21 (bundled Stripe SDK under `gravityforms/includes/payments/stripe/`).
- **Caching:** Default WordPress object cache. The GoDaddy Object Cache Pro drop-in and `gd-config.php` have been removed so the site no longer expects Redis or GoDaddy APIs.

## Local Prerequisites

| Tool | Recommended Version | Notes |
| --- | --- | --- |
| PHP | 8.1+ | Required for WordPress core, CLI tools, and linting. A portable PHP binary ships in `tools/PHP/8.3/` (outside the repo); set `WP_LOCAL_PHP` to its path or install your own. |
| MySQL/MariaDB | 10.5+ / 8.x | Needed to run WordPress locally. Import the sanitized dump provided with the backups before logging in. |
| WP-CLI | Latest | Use `scripts/wp-cli.ps1` to wrap WP-CLI with the correct PHP binary and project path. |
| Node.js (optional) | 18+ | Only necessary if you modify asset pipelines for plugins/themes that use npm builds. |

## Getting Started

1. **Clone or sync the repo.** Serve the `html/` directory from your local web server (IIS Express, Laragon, LocalWP, Docker/NGINX, etc.).
2. **Import the database.** Create a database that matches the credentials in `wp-config.php` or update those constants to match your environment. Load the latest dump (`fkqc46017421287.sql`) or a sanitized export from production.
3. **Update URLs.** Point a host entry (for example `exteriors.local`) to your local server, then run `./scripts/set-site-url.ps1 -Url "http://exteriors.local"` to swap the `home` and `siteurl` options.
4. **Regenerate permalinks & assets.** After logging in, visit *Settings ▸ Permalinks* and click **Save**, then open *Elementor ▸ Tools* and click **Regenerate CSS & Data**.
5. **Optional caching plugins.** Leave LiteSpeed Cache, WP Fastest Cache, and SiteGround Optimizer deactivated until you configure equivalent services on the new host.

## WP-CLI Wrapper

Run WordPress CLI commands through the helper script; it attaches the repository path automatically and resolves PHP/WP-CLI binaries:

```powershell
# From the repo root
./scripts/wp-cli.ps1 plugin status
```

Fallback order used by the script:

1. `WP_LOCAL_PHP` / `WP_LOCAL_WPCLI` environment variables.
2. `tools/PHP/8.3/php.exe` and `tools/wp-cli/wp-cli.phar` inside the workspace (if present).
3. `c:\tools\PHP\8.3\php.exe` and `c:\tools\wp-cli\wp-cli.phar`.

If no candidate binaries are found the script aborts with a descriptive error. Override the environment variables to suit your machine.

## Validation Workflow

With PHP and the database running locally, execute baseline checks:

```powershell
cd "c:/path/to/Exterior Solutions"
php -l html\wp-config.php
./scripts/wp-cli.ps1 core verify-checksums
./scripts/wp-cli.ps1 plugin status
```

Manual QA:

- Load the home page, services detail pages, and Elementor-driven contact form to confirm widgets resolve.
- Submit the Gravity Forms “Request an Estimate” form with test data (use Stripe test keys if available).
- Search Elementor’s Site Settings for lingering `*.myftpupload.com` URLs and replace them with the target domain.

## Removed Host-Specific Components

- `gd-config.php` and the GoDaddy system plugin loader were removed to prevent calls into GoDaddy APIs.
- `mu-plugins/object-cache.php` (Object Cache Pro) has been deleted because Redis is not bundled locally. Reintroduce it only if the destination host provides Object Cache Pro.
- SiteGround and GoDaddy helper mu-plugins remain on disk for reference but are not loaded; delete them if you want a completely clean tree.

## Logging & Debugging

- Temporarily enable debugging by setting `define( 'WP_DEBUG', true );` and `define( 'WP_DEBUG_LOG', true );` in `wp-config.php`. Logs will appear in `html/wp-content/debug.log`.
- Historical logs and legacy host assets live under `html_old_20251030/` for reference.

## Deployment Notes

- Package the active `html/` directory (omit `html_old_*` backups) when pushing to the destination host.
- Remove transient artifacts (`php_errorlog`, `.maintenance`, cache folders) before final sync.
- Keep premium plugin ZIPs in `Updated Plugin Zips/` for quick reinstalls but exclude them from production deployments.
- Every push to `main` now runs the **Deploy to SiteGround** workflow, which syncs the git tree and re-imports `fkqc46017421287.sql` into the production database. Any manual content edits made directly in WordPress will be overwritten on the next push—capture them in the repo or the SQL dump first.
- A manual **Restore production database** workflow is also available under the GitHub Actions tab if you ever need to re-import without publishing new code.

## Maintenance Checklist

- [ ] Run the validation workflow before committing.
- [ ] Document plugin/theme upgrades in this README or the `docs/` folder.
- [ ] Confirm Elementor, Gravity Forms, and premium add-ons remain licensed after migration.
- [ ] Update this README with any host-specific setup notes discovered during QA.

## Support & Contacts

- Website owner: Exterior Solutions (Jeremiah81505).
- Deployment: Internal team; choose hosting provider per project.
- Development contact: Internal web team / contractors maintaining this repository.
