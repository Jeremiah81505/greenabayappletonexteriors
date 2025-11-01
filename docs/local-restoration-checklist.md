# Local Restoration Checklist

This checklist summarizes the steps needed to finish wiring the restored WordPress site after syncing the legacy content.

1. **Update the Site URL for Local Browsing**
    - Decide the exact URL you will use locally (for example `http://localhost/exterior-solutions`).
    - Run the helper script from the repository root:

       ```powershell
       # Change the URL to match your local domain
       .\scripts\set-site-url.ps1 -Url "http://localhost/exterior-solutions"
       ```

    - The script updates both `home` and `siteurl` and flushes caches while skipping plugins/themes to avoid fatal errors.

2. **Verify Premium Plugin Payloads**
    - Elementor Pro, Essential Addons Pro, and Gravity Forms have been rehydrated. Confirm they remain licensed after importing the database.
    - If you need to refresh them, download the latest ZIPs, extract into `html\wp-content\plugins`, then run:

       ```powershell
       ./scripts/wp-cli.ps1 plugin activate elementor elementor-pro gravityforms essential-addons-elementor
       ```

    - If a plugin throws a fatal error, retry the activation with `--skip-plugins` to bypass the offender and investigate the PHP error log.

3. **Reactivate Remaining Plugins**
    - Enable only the plugins you plan to keep; use the helper script for activation:

       ```powershell
       ./scripts/wp-cli.ps1 plugin activate wordpress-seo
       ```

    - Leave SiteGround-specific utilities (`sg-cachepress`, `sg-security`) and caching plugins (LiteSpeed, WP Fastest Cache) inactive unless the destination host supports them.

4. **Flush Permalinks and Elementor Assets**
   - Log into the WordPress dashboard, navigate to *Settings → Permalinks*, and click **Save Changes**.
   - In Elementor, open *Tools → General* and click **Regenerate CSS & Data** and **Sync Library**.

5. **Sanity Checks**
   - Visit the front end and confirm the header, hero, and Gravity Forms load without fatal errors.
   - Run `./scripts/wp-cli.ps1 db check` to confirm the database is clean.
   - Capture screenshots of critical pages for comparison with production.

6. **Deployment Prep**
   - Commit the new assets (uploads, plugins, scripts) to Git.
   - Update production credentials in `wp-config.php` before pushing to SiteGround, or keep them in host-specific includes.
   - When ready, run your SiteGround deployment workflow or upload via SFTP.

Keep this file updated as you discover additional tasks during QA.
