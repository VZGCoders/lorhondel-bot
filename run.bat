chcp 65001
@echo off
cls
set watch=Lorhondel-Dev
title %watch% Watchdog
:watchdog
echo (%time%) %watch% started.
php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=100M "run.php"
echo (%time%) %watch% closed or crashed, restarting.
goto watchdog