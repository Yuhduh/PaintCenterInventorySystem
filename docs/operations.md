# Operations runbook

## Automated database backups

The Laravel scheduler creates a verified database backup every day at 01:30, cleans expired backups at 01:00, and checks backup health at 10:00.

Run the scheduler continuously in production:

```powershell
php artisan schedule:work
```

Confirm the schedule and available archives:

```powershell
php artisan schedule:list
php artisan backup:list
php artisan backup:monitor
```

Set `BACKUP_ARCHIVE_PASSWORD` in the production `.env`. Never store that password in the repository.

## Quarterly restore drill

Never restore a drill into the live database. Create an empty temporary database with a restricted test account, then:

1. Copy the newest backup archive to an isolated machine.
2. Open the archive using `BACKUP_ARCHIVE_PASSWORD` and extract the SQL dump.
3. Import the dump into the empty temporary database.
4. point a temporary application instance at that database with `APP_ENV=testing` and `APP_DEBUG=false`.
5. Run `php artisan migrate:status`, sign in, and verify products, inventory totals, sales history, and audit logs.
6. Record the backup date, restore duration, row-count checks, tester, and outcome.
7. Delete the temporary database and extracted dump after the drill.

A backup is not considered recoverable until this drill succeeds.
