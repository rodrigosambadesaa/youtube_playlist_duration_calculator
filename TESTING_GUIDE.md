# 🧪 Guía Completa de Testing y Configuración

## 🔐 Configuración Segura de API Key

### Paso 1: Obtener API Key de YouTube

1. **Ir a Google Cloud Console**
   - Ve a [Google Cloud Console](https://console.developers.google.com/)
   - Inicia sesión con tu cuenta de Google

2. **Crear o seleccionar proyecto**
   ```
   - Haz clic en "Seleccionar proyecto" → "Nuevo proyecto"
   - Nombre: "YouTube Playlist Calculator"
   - Haz clic en "Crear"
   ```

3. **Habilitar YouTube Data API v3**
   ```
   - Ve a "APIs y servicios" → "Biblioteca"
   - Busca "YouTube Data API v3"
   - Haz clic en "Habilitar"
   ```

4. **Crear credenciales**
   ```
   - Ve a "APIs y servicios" → "Credenciales"
   - Haz clic en "Crear credenciales" → "Clave de API"
   - Copia la API key generada (ejemplo: AIzaSyDxxxxxxxxxxxxxxxxxxxxxxxxxxx)
   ```

5. **Configurar restricciones (MUY IMPORTANTE)**
   ```
   - Haz clic en la API key creada
   - En "Restricciones de aplicación":
     * Selecciona "Referentes HTTP (sitios web)"
     * Agrega: localhost/* y 127.0.0.1/*
   - En "Restricciones de API":
     * Selecciona "Restringir clave"
     * Marca solo "YouTube Data API v3"
   - Haz clic en "Guardar"
   ```

### Paso 2: Configuración Local Segura

#### Opción A: Archivo .env (RECOMENDADO)

1. **Crear archivo de configuración**
   ```bash
   # En el directorio del proyecto, copia el archivo ejemplo:
   cp .env.example .env
   ```

2. **Editar el archivo .env**
   ```bash
   # Abre .env con tu editor preferido y configura:
   YOUTUBE_API_KEY=AIzaSyDxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

3. **Verificar que .env está en .gitignore**
   ```bash
   # El archivo .env ya está incluido en .gitignore
   # NUNCA subas este archivo a GitHub
   ```

#### Opción B: Variable de entorno del sistema

**Windows (PowerShell):**
```powershell
$env:YOUTUBE_API_KEY = "AIzaSyDxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

**Windows (CMD):**
```cmd
set YOUTUBE_API_KEY=AIzaSyDxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Linux/Mac:**
```bash
export YOUTUBE_API_KEY="AIzaSyDxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

## 🧪 Proceso de Testing Completo

### Fase 1: Verificación de Configuración

1. **Abrir página de debug**
   ```
   http://localhost/youtube_playlist_duration_calculator/debug.php
   ```
   
   **Verificar:**
   - ✅ PHP versión 7.4+
   - ✅ Módulos: json, session, filter, curl
   - ✅ Apache modules: mod_rewrite, mod_headers

2. **Test básico de API**
   ```
   http://localhost/youtube_playlist_duration_calculator/api-test.html
   ```
   
   **Hacer clic en "Verificar Estado de API":**
   - ✅ Backend PHP funcionando
   - ✅ Token CSRF generado
   - ✅ API endpoints accesibles
   - ✅ Estado de API key (configurada/demo)

### Fase 2: Testing de Funcionalidad

1. **Test con datos de demostración**
   ```
   http://localhost/youtube_playlist_duration_calculator/
   ```
   
   **Probar URLs de ejemplo:**
   ```
   # Playlist de demo 1:
   https://www.youtube.com/playlist?list=PLbAFXJC0J5Gz-liBodX5Lmt0wfbMgm-vA
   
   # Playlist de demo 2:
   https://www.youtube.com/playlist?list=PLX8eNTkvxMmcM9-Y-w2hBAVuvW4N3q36n
   ```

2. **Test con playlist real (si tienes API key)**
   
   **Playlists públicas para probar:**
   ```
   # Ejemplo de playlist pública real:
   https://www.youtube.com/playlist?list=PLWKjhJtqVAbleDe3_ZA8h3AO2rXar-q2V
   
   # O cualquier playlist pública que conozcas
   ```

### Fase 3: Tests Avanzados

1. **Test de seguridad**
   ```
   http://localhost/youtube_playlist_duration_calculator/test.html
   ```
   
   **Ejecutar todos los tests:**
   - ✅ Configuración del servidor
   - ✅ Token CSRF
   - ✅ Validación de URL
   - ✅ Rate limiting
   - ✅ Inputs maliciosos
   - ✅ API real de YouTube

2. **Test de rendimiento**
   
   **Probar con playlist grande:**
   ```
   # Buscar en YouTube una playlist con 50+ videos
   # Verificar que se procese en tiempo razonable (< 10 segundos)
   ```

## 🔍 Resolución de Problemas

### Problema: "API key de YouTube no configurada"

**Verificaciones:**

1. **Comprobar archivo .env**
   ```bash
   # Verificar que existe y tiene contenido
   cat .env
   # Debe mostrar: YOUTUBE_API_KEY=tu_api_key_aquí
   ```

2. **Comprobar permisos de archivo**
   ```bash
   # El archivo debe ser legible por el servidor web
   ls -la .env
   ```

3. **Verificar en API test**
   ```
   # Ir a api-test.html y hacer clic en "Verificar Estado de API"
   # Debe mostrar si la API key está detectada
   ```

### Problema: "Playlist no encontrada"

**Posibles causas:**
- Playlist es privada o no listada
- URL incorrecta
- API key sin permisos
- Playlist fue eliminada

**Solución:**
1. Verificar que la playlist es pública
2. Probar con las playlists de ejemplo conocidas
3. Revisar logs en `logs/app.log`

### Problema: "Error de cuota de API"

**Causa:** Límite diario de YouTube API alcanzado

**Soluciones:**
1. **Verificar cuota en Google Cloud Console:**
   ```
   - Ve a "APIs y servicios" → "Cuotas"
   - Buscar "YouTube Data API v3"
   - Verificar uso actual vs límite
   ```

2. **Solicitar aumento de cuota** (si es necesario)
3. **Usar modo demo** temporalmente

### Problema: Rate limiting muy agresivo

**Ajustar en config.php:**
```php
define('MAX_REQUESTS_PER_MINUTE', 20); // Aumentar de 10 a 20
```

## 📊 Interpretación de Resultados

### Datos Reales vs Demo

**Indicadores de datos reales:**
- ✅ Título exacto de la playlist
- ✅ Nombre real del canal
- ✅ Número exacto de videos
- ✅ Duraciones precisas al segundo

**Indicadores de modo demo:**
- ⚠️ Mensaje "Modo Demostración"
- ⚠️ Descripción contiene "DEMO:"
- ⚠️ Datos genéricos o aproximados

### Validación de Resultados

**Para verificar manualmente:**
1. Abrir la playlist en YouTube
2. Usar extensión de navegador para sumar duraciones
3. Comparar con resultado de la aplicación

**Tolerancia aceptable:**
- Diferencia máxima: ±5 segundos por playlist
- Diferencias mayores indican videos privados/eliminados

## 🚀 Optimización de Rendimiento

### Para playlists grandes (100+ videos):

1. **Aumentar timeout PHP:**
   ```php
   ini_set('max_execution_time', 60);
   ```

2. **Verificar cache:**
   ```
   # Los resultados se cachean por 15 minutos
   # Segunda consulta debe ser instantánea
   ```

3. **Monitorear logs:**
   ```bash
   tail -f logs/app.log
   ```

## 🔒 Seguridad en Producción

### Variables de entorno del servidor

**Apache (.htaccess):**
```apache
SetEnv YOUTUBE_API_KEY "tu_api_key_aquí"
```

**Nginx:**
```nginx
fastcgi_param YOUTUBE_API_KEY "tu_api_key_aquí";
```

**Panel de hosting:**
```
# Buscar sección "Variables de entorno"
# Agregar: YOUTUBE_API_KEY = tu_api_key_aquí
```

### Restricciones adicionales

1. **Configurar referentes en Google Cloud:**
   ```
   # Agregar solo tu dominio de producción
   tudominio.com/*
   www.tudominio.com/*
   ```

2. **Monitoreo de uso:**
   ```
   # Configurar alertas en Google Cloud Console
   # Para detectar uso anómalo de la API
   ```

---

## ✅ Checklist Final

- [ ] API key obtenida de Google Cloud Console
- [ ] Archivo .env creado y configurado
- [ ] .env incluido en .gitignore
- [ ] Test básico funcionando (debug.php)
- [ ] Test de API funcionando (api-test.html)
- [ ] Aplicación principal funcionando
- [ ] Playlists de ejemplo procesadas correctamente
- [ ] Playlists reales funcionando (con API key)
- [ ] Rate limiting verificado
- [ ] Seguridad validada
- [ ] Rendimiento aceptable

**¡Listo para usar en producción!** 🎉
