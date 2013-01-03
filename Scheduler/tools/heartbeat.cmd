@ECHO OFF
ECHO %DATE% %TIME% >> heartbeat.log
php -f heartbeat.php >> heartbeat.log
