@echo off
setlocal EnableDelayedExpansion
title Subir DIV, SimForest y portal_citas a GitHub
cd /d "%~dp0"

set "BRANCH=main"
set "FOLDERS=DIV portal_citas SimForest DimForest"
set "ENLACE_NOMBRE=enlace proyectos-clase.txt"
set "URL_FALLBACK=https://github.com/librosinformatica12-sudo/proyectos-clase"
set "EXITCODE=0"

echo ==================================================
echo   Subir DIV, SimForest y portal_citas a GitHub
echo ==================================================
echo.

call :comprobar_git
if errorlevel 1 goto :error

call :obtener_url "%~1"
if errorlevel 1 goto :error

call :comprobar_carpetas
if errorlevel 1 goto :error

set "MSG=%~2"
if not defined MSG (
    set /p "MSG=Mensaje del commit (Enter = automatico): "
)
if not defined MSG set "MSG=Actualizacion %DATE% %TIME%"

set "TMPROOT=%TEMP%\subir-repo-%RANDOM%%RANDOM%"
set "CLONE=%TMPROOT%\repo"
mkdir "%TMPROOT%" >nul 2>&1
if not exist "%TMPROOT%\" (
    echo [ERROR] No se pudo crear la carpeta temporal.
    goto :error
)

echo Repositorio destino: !URL!
echo Carpetas: !FOLDERLIST!
echo.

echo [1/4] Descargando repositorio...
git clone --quiet "!URL!" "%CLONE%" > "%TMPROOT%\clone.log" 2>&1
if errorlevel 1 (
    echo.
    echo [ERROR] No se pudo descargar el repositorio.
    type "%TMPROOT%\clone.log"
    call :ayuda_clone
    goto :error
)

cd /d "%CLONE%"
git rev-parse --verify --quiet refs/remotes/origin/main >nul 2>&1
if not errorlevel 1 (
    git checkout -B %BRANCH% origin/main >nul 2>&1
) else (
    git checkout -B %BRANCH% >nul 2>&1
)

echo [2/4] Copiando carpetas...
for %%D in (!FOLDERLIST!) do (
    if exist "%%D\" rmdir /s /q "%%D"
    xcopy "%~dp0%%D" "%CLONE%\%%D\" /E /I /H /Y /Q >nul 2>&1
    if errorlevel 1 (
        echo [ERROR] No se pudo copiar la carpeta %%D.
        goto :error
    )
)

copy /y "%~f0" "%CLONE%\%~nx0" >nul 2>&1
if errorlevel 1 (
    echo [ERROR] No se pudo copiar el propio .bat al repositorio.
    goto :error
)

echo [3/4] Preparando el commit...
git add -A
if errorlevel 1 (
    echo [ERROR] Fallo el comando "git add".
    goto :error
)

git diff --cached --quiet
if not errorlevel 1 (
    echo.
    echo [OK] No hay cambios en las carpetas. Nada que subir.
    goto :limpiar
)

set "SENSIBLES="
for /f "delims=" %%F in ('git ls-files --cached') do (
    set "FN=%%~nxF"
    if /i "!FN!"=="AccesoDatos.php" set "SENSIBLES=!SENSIBLES!%%F "
    if /i not "!FN:.env=!"=="!FN!" set "SENSIBLES=!SENSIBLES!%%F "
    if /i "!FN:~-4!"==".key" set "SENSIBLES=!SENSIBLES!%%F "
    if /i "!FN:~-4!"==".pem" set "SENSIBLES=!SENSIBLES!%%F "
    if /i "!FN:~-4!"==".p12" set "SENSIBLES=!SENSIBLES!%%F "
    if /i "!FN:~-4!"==".pfx" set "SENSIBLES=!SENSIBLES!%%F "
)
if defined SENSIBLES (
    echo.
    echo [ERROR] Se han encontrado archivos sensibles en las carpetas:
    echo !SENSIBLES!
    echo.
    echo Eliminalos o renombralos antes de volver a subir.
    goto :error
)

set "GITID="
git config user.name >nul 2>&1
if errorlevel 1 set "GITID=!GITID! -c user.name=Subida-automatica"
git config user.email >nul 2>&1
if errorlevel 1 set "GITID=!GITID! -c user.email=subida@local"

git!GITID! commit -m "%MSG%" > "%TMPROOT%\commit.log" 2>&1
if errorlevel 1 (
    echo [ERROR] No se pudo crear el commit.
    type "%TMPROOT%\commit.log"
    goto :error
)

echo [4/4] Subiendo cambios a GitHub...
git push origin %BRANCH% > "%TMPROOT%\push.log" 2>&1
if errorlevel 1 (
    echo.
    echo [ERROR] Fallo el push.
    type "%TMPROOT%\push.log"
    call :ayuda_push
    goto :error
)

echo.
echo [OK] Cambios subidos correctamente.
echo      Rama: %BRANCH%
echo      Repositorio: !URL!
goto :limpiar


:comprobar_git
where git >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Git no esta instalado o no esta en el PATH.
    echo         Instala Git desde https://git-scm.com/download/win
    exit /b 1
)
exit /b 0

:obtener_url
set "URL="
set "TXTFILE="
set "ARG=%~1"
if defined ARG (
    if /i "!ARG:~0,4!"=="http" (
        set "URL=!ARG!"
        goto :url_lista
    )
    if /i "!ARG:~0,7!"=="file://" (
        set "URL=!ARG!"
        goto :url_lista
    )
    if /i "!ARG:~0,4!"=="git@" (
        set "URL=!ARG!"
        goto :url_lista
    )
    if exist "!ARG!" (
        set "TXTFILE=!ARG!"
        goto :leer_txt
    )
)

call :buscar_txt
if not defined TXTFILE goto :url_fallback

:leer_txt
for /f "usebackq delims=" %%u in ("!TXTFILE!") do (
    if not defined URL set "URL=%%u"
)
if defined URL set "URL=!URL: =!"
if defined URL goto :url_lista
goto :url_fallback

:url_fallback
if defined URL_FALLBACK (
    set "URL=!URL_FALLBACK!"
    echo [AVISO] No se encontro "%ENLACE_NOMBRE%". Se usara el repositorio por defecto:
    echo         !URL!
    goto :url_lista
)
echo [ERROR] No se encontro el archivo "%ENLACE_NOMBRE%".
echo         Coloca el archivo en Descargas o arrastra el .txt sobre este .bat.
exit /b 1

:url_lista
if not defined URL exit /b 1
exit /b 0

:buscar_txt
set "TXTFILE="
for %%P in (
    "%USERPROFILE%\Downloads\%ENLACE_NOMBRE%"
    "%USERPROFILE%\Descargas\%ENLACE_NOMBRE%"
    "%USERPROFILE%\OneDrive\Downloads\%ENLACE_NOMBRE%"
    "%USERPROFILE%\OneDrive\Descargas\%ENLACE_NOMBRE%"
    "%~dp0%ENLACE_NOMBRE%"
    "%CD%\%ENLACE_NOMBRE%"
) do (
    if not defined TXTFILE if exist "%%~P" set "TXTFILE=%%~P"
)
exit /b 0

:comprobar_carpetas
set "FOLDERLIST="
for %%D in (%FOLDERS%) do (
    if exist "%~dp0%%D\" set "FOLDERLIST=!FOLDERLIST!%%D "
)
if not defined FOLDERLIST (
    echo [ERROR] No se encontro ninguna de las carpetas DIV, SimForest o portal_citas
    echo         junto a este archivo .bat.
    exit /b 1
)
exit /b 0

:ayuda_clone
findstr /i /c:"Authentication failed" /c:"could not read Username" /c:"Permission denied" /c:"Invalid username or password" "%TMPROOT%\clone.log" >nul 2>&1
if not errorlevel 1 (
    echo [AYUDA] Credenciales incorrectas o sesion caducada en GitHub.
    echo         Configura Git Credential Manager o revisa tu token de acceso.
)
findstr /i /c:"Could not resolve host" /c:"unable to access" /c:"Connection timed out" /c:"not found" /c:"does not appear to be a git repository" "%TMPROOT%\clone.log" >nul 2>&1
if not errorlevel 1 (
    echo [AYUDA] Revisa tu conexion y que el enlace del .txt sea correcto.
)
exit /b 0

:ayuda_push
findstr /i /c:"Authentication failed" /c:"could not read Username" /c:"Permission denied" /c:"Invalid username or password" "%TMPROOT%\push.log" >nul 2>&1
if not errorlevel 1 (
    echo [AYUDA] Credenciales incorrectas o sesion caducada en GitHub.
    echo         Configura Git Credential Manager o revisa tu token de acceso.
)
findstr /i /c:"rejected" /c:"non-fast-forward" /c:"fetch first" "%TMPROOT%\push.log" >nul 2>&1
if not errorlevel 1 (
    echo [AYUDA] El repositorio remoto tiene cambios nuevos.
    echo         Vuelve a ejecutar este .bat para actualizarte y subir de nuevo.
)
findstr /i /c:"Could not resolve host" /c:"unable to access" /c:"Connection timed out" "%TMPROOT%\push.log" >nul 2>&1
if not errorlevel 1 (
    echo [AYUDA] Revisa tu conexion y el enlace del repositorio.
)
exit /b 0

:limpiar
cd /d "%~dp0"
if defined TMPROOT (
    if exist "%TMPROOT%\" rmdir /s /q "%TMPROOT%" >nul 2>&1
)
echo.
pause
exit /b %EXITCODE%

:error
set "EXITCODE=1"
goto :limpiar
