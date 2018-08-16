@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../browscap/browscap-php/bin/browscap-php
php "%BIN_TARGET%" %*
