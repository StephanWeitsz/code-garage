# Hosting Deployment (No Console Access)

## Why admin or lecturer pages may be blank on hosting

Common causes in this project:

- Stale local cache files copied to hosting (`bootstrap/cache/*.php` or compiled view/session/cache artifacts).
- Missing frontend build assets (`public/build/manifest.json`).
- Missing storage symlink (`public/storage` -> `storage/app/public`).
- Filament assets not refreshed after package updates.

## Recommended deployment flow

1. On your local machine, run:
```bash
npm run build
composer install --no-dev --optimize-autoloader
./scripts/package-hosting-release.sh
```
2. Upload the generated zip from `.release/dist/`.
3. Extract on hosting into your app root.
4. In `.env` on hosting set:
```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DEPLOYMENT_TOOLS_ENABLED=true
```
5. Log in as admin and open `/deployment-tools`.
6. Run these actions in order:
- `optimize:clear`
- `storage:link`
- `filament:upgrade` (especially if admin/lecturer pages are blank)

After deployment is stable, set `DEPLOYMENT_TOOLS_ENABLED=false` for security.

