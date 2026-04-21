# CLI Mode

The same controllers work from the command line — no changes needed:

```bash
# Calls welcome::index()
php public/index.php welcome

# Calls users::show('42')
php public/index.php users/show/42
```

This makes it easy to build CLI tools, cron jobs, or admin scripts that share logic with your web app.
