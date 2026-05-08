# WSL Development Notes

## What is slowing this app down right now

The app is being executed inside WSL, but the project itself lives on the mounted Windows filesystem:

- Workspace path in Windows: `D:\project\CodeingClass\student-platform`
- Workspace path in WSL: `/mnt/d/project/CodeingClass/student-platform`

That means Laravel, Livewire, Filament, Vite, SQLite, file cache, and Blade view compilation are all reading and writing through the `/mnt/d` mount. That is usually much slower than keeping the project inside native WSL storage.

A second local slowdown was session storage. In local development the app was using `SESSION_DRIVER=database`, which writes to SQLite frequently. That has now been changed to `SESSION_DRIVER=file` for local development.

## Best local setup before Docker

Use native WSL storage for active development, for example:

- `/home/<your-user>/projects/student-platform`

Recommended flow:

1. Copy or clone the repo into WSL storage.
2. Run Composer, npm, and Artisan inside WSL from that Linux path.
3. Keep SQLite for now if you want a lightweight dev database.
4. Move to Docker once the application flow is settled.

## Suggested migration into WSL storage

From inside WSL:

```bash
mkdir -p ~/projects
cd ~/projects
cp -r /mnt/d/project/CodeingClass/student-platform ./student-platform
cd student-platform
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Why this helps

Native WSL storage improves:

- SQLite file access
- Blade compiled view writes
- Livewire and Filament request cycles
- Vite and npm filesystem scans
- Laravel cache/session file reads and writes

## Notes for later Docker work

This setup already keeps the app DB-agnostic enough for a later move to MySQL or Postgres in Docker. The main thing to avoid during local dev is benchmarking performance from `/mnt/d`, because it can make the app feel slower than it really is.
