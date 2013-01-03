Use it in Windows Scheduler like this:

Set working directory as directory where your heartbeat.php file is located (in webpage root directory by default).

Copy this line in Windows Scheduler
wscript.exe "ds-addons/Scheduler/tools/invisible.vbs" "ds-addons/Scheduler/tools/heartbeat.cmd"
