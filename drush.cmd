@echo off
setlocal
set DRUSH_NO_MIN_PHP=1
php "%~dp0vendor\bin\drush.php" --root="%~dp0web" %*
endlocal
