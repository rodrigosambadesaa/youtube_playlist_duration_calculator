@echo off
echo ============================================
echo  YouTube Playlist Calculator - Setup
echo ============================================
echo.

:: Verificar si estamos en el directorio correcto
if not exist "index.html" (
    echo ERROR: Este script debe ejecutarse desde el directorio del proyecto
    echo que contiene index.html
    pause
    exit /b 1
)

echo [1/4] Verificando archivos del proyecto...
set FILES_OK=1
if not exist "api.php" set FILES_OK=0
if not exist "config.php" set FILES_OK=0
if not exist "script.js" set FILES_OK=0
if not exist "styles.css" set FILES_OK=0

if %FILES_OK%==0 (
    echo ERROR: Faltan archivos del proyecto
    pause
    exit /b 1
)
echo ✓ Archivos del proyecto encontrados

echo.
echo [2/4] Verificando configuracion de seguridad...
if not exist ".gitignore" (
    echo ADVERTENCIA: Archivo .gitignore no encontrado
    echo Tu API key podria subirse accidentalmente a Git
)

if not exist ".env.example" (
    echo ERROR: Archivo .env.example no encontrado
    pause
    exit /b 1
)
echo ✓ Archivos de configuracion listos

echo.
echo [3/4] Configurando archivo de entorno...
if exist ".env" (
    echo INFO: El archivo .env ya existe
    echo ¿Quieres sobrescribirlo? (s/N)
    set /p OVERWRITE=
    if /i "%OVERWRITE%"=="s" (
        copy ".env.example" ".env"
        echo ✓ Archivo .env recreado
    ) else (
        echo - Archivo .env conservado
    )
) else (
    copy ".env.example" ".env"
    echo ✓ Archivo .env creado desde plantilla
)

echo.
echo [4/4] Configurando API Key...
echo.
echo IMPORTANTE: Necesitas configurar tu API Key de YouTube
echo.
echo Pasos para obtener una API Key:
echo 1. Ve a: https://console.developers.google.com/
echo 2. Crea un proyecto y habilita "YouTube Data API v3"
echo 3. Crea una "Clave de API" en Credenciales
echo 4. Copia la API key
echo.
echo ¿Tienes una API Key lista para configurar? (s/N)
set /p HAS_KEY=

if /i "%HAS_KEY%"=="s" (
    echo.
    echo Ingresa tu API Key de YouTube:
    set /p API_KEY=
    
    if not "%API_KEY%"=="" (
        echo YOUTUBE_API_KEY=%API_KEY%> .env
        echo ✓ API Key configurada en .env
    ) else (
        echo - Sin API Key configurada, la aplicacion funcionara en modo demo
    )
) else (
    echo - Sin API Key configurada, la aplicacion funcionara en modo demo
    echo   Puedes configurarla despues editando el archivo .env
)

echo.
echo ============================================
echo  Setup Completado
echo ============================================
echo.
echo Proximos pasos:
echo.
echo 1. Asegurate de que XAMPP este ejecutandose
echo 2. Ve a: http://localhost/youtube_playlist_duration_calculator/setup-check.html
echo 3. Ejecuta la verificacion automatica
echo 4. Si todo esta OK, ve a: http://localhost/youtube_playlist_duration_calculator/
echo.
echo Archivos importantes:
echo - .env : Configuracion de API key (NO subir a Git)
echo - TESTING_GUIDE.md : Guia completa de testing
echo - README.md : Documentacion principal
echo.
echo ¿Quieres abrir la verificacion automatica ahora? (s/N)
set /p OPEN_CHECK=

if /i "%OPEN_CHECK%"=="s" (
    start http://localhost/youtube_playlist_duration_calculator/setup-check.html
    echo ✓ Abriendo verificacion automatica...
)

echo.
echo Setup completado. ¡Presiona cualquier tecla para salir!
pause >nul
