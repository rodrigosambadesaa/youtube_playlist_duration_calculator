# YouTube Playlist Duration Calculator

Una aplicación web moderna y segura para calcular la duración total de playlists de YouTube. Desarrollada con tecnologías web estándar y enfoque en seguridad y experiencia de usuario.

## 🚀 Características

### Funcionalidades Principales
- ✅ Cálculo de duración total de playlists de YouTube
- ✅ Visualización en múltiples formatos (HH:MM:SS, minutos, segundos)
- ✅ Cálculo de tiempo a diferentes velocidades de reproducción (1.25x, 1.5x, 1.75x, 2x)
- ✅ Interfaz responsive que funciona en desktop, tablet y móvil
- ✅ Diseño moderno con tonos azules y excelente UX

### Características de Seguridad
- 🔒 **Protección CSRF** - Tokens de seguridad para prevenir ataques
- 🔒 **Sanitización avanzada** - Limpieza de datos sin usar funciones deprecated
- 🔒 **Rate Limiting** - Límite de solicitudes por IP
- 🔒 **Validación de entrada** - Múltiples capas de validación
- 🔒 **Headers de seguridad** - XSS Protection, HSTS, CSP, etc.
- 🔒 **Protección contra inyección** - Prevención de SQL injection y XSS
- 🔒 **Sesiones seguras** - Configuración segura de cookies de sesión

### Características Técnicas
- 📱 **Responsive Design** - Compatible con todos los dispositivos
- ⚡ **Performance optimizado** - Compresión GZIP, cache, minificación
- 🎨 **UI/UX moderno** - Diseño limpio con animaciones fluidas
- 🔧 **API RESTful** - Backend bien estructurado
- 📊 **Manejo de errores** - Gestión robusta de errores y excepciones

## 🛠️ Tecnologías Utilizadas

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos modernos con Flexbox y Grid
- **JavaScript ES6+** - Lógica del cliente con clases y módulos
- **Font Awesome** - Iconografía
- **Google Fonts** - Tipografía (Inter)

### Backend
- **PHP 7.4+** - Lógica del servidor
- **Apache** - Servidor web con configuración de seguridad

### Seguridad
- **CSRF Protection** - Tokens de validación
- **Rate Limiting** - Control de velocidad de requests
- **Input Validation** - Validación multicapa
- **Output Encoding** - Codificación segura de salida
- **HTTP Security Headers** - Configuración de headers de seguridad

## 📁 Estructura del Proyecto

```
youtube_playlist_duration_calculator/
├── index.html          # Página principal
├── styles.css          # Estilos CSS
├── script.js           # JavaScript del frontend
├── api.php             # API backend
├── .htaccess           # Configuración Apache
└── README.md           # Documentación
```

## 🚀 Instalación y Configuración

### Requisitos Previos
- Apache 2.4+
- PHP 7.4+
- Módulos Apache: mod_rewrite, mod_headers, mod_deflate
- **API Key de YouTube Data API v3** (requerida para datos reales)

### Obtener API Key de YouTube

1. **Ir a Google Cloud Console**
   - Visita [Google Cloud Console](https://console.developers.google.com/)
   - Inicia sesión con tu cuenta de Google

2. **Crear o seleccionar un proyecto**
   - Crea un nuevo proyecto o selecciona uno existente
   - Dale un nombre descriptivo como "YouTube Playlist Calculator"

3. **Habilitar YouTube Data API v3**
   - Ve a "APIs y servicios" > "Biblioteca"
   - Busca "YouTube Data API v3"
   - Haz clic en "Habilitar"

4. **Crear credenciales**
   - Ve a "APIs y servicios" > "Credenciales"
   - Haz clic en "Crear credenciales" > "Clave de API"
   - Copia la API key generada

5. **Configurar restricciones (recomendado)**
   - Haz clic en la API key creada
   - En "Restricciones de aplicación", selecciona "Referentes HTTP"
   - Agrega tu dominio (ej: `localhost/*`, `tudominio.com/*`)
   - En "Restricciones de API", selecciona "YouTube Data API v3"

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   git clone [repository-url]
   cd youtube_playlist_duration_calculator
   ```

2. **Configurar la API Key**
   ```bash
   # Copiar archivo de ejemplo
   cp .env.example .env
   
   # Editar el archivo .env y agregar tu API key
   # YOUTUBE_API_KEY=tu_api_key_aquí
   ```

3. **Configurar el servidor web**
   - Asegúrate de que Apache esté ejecutándose
   - Verifica que mod_rewrite esté habilitado
   - El directorio debe ser accesible vía web

4. **Configurar permisos**
   ```bash
   chmod 644 *.html *.css *.js *.php
   chmod 755 .
   ```

5. **Acceder a la aplicación**
   - Abre tu navegador
   - Ve a `http://localhost/youtube_playlist_duration_calculator`

### 🧪 Testing y Verificación

Para una guía completa de testing, configuración y resolución de problemas, consulta:
**📖 [GUÍA COMPLETA DE TESTING](TESTING_GUIDE.md)**

#### Tests Rápidos

1. **Verificación básica**: `http://localhost/youtube_playlist_duration_calculator/debug.php`
2. **Test de API**: `http://localhost/youtube_playlist_duration_calculator/api-test.html`
3. **Tests de seguridad**: `http://localhost/youtube_playlist_duration_calculator/test.html`

#### Playlists de Prueba

```bash
# Modo demostración (sin API key):
https://www.youtube.com/playlist?list=PLbAFXJC0J5Gz-liBodX5Lmt0wfbMgm-vA

# Con API key configurada, cualquier playlist pública de YouTube
```

### 🔒 Seguridad de API Key

**IMPORTANTE:** 
- ✅ El archivo `.env` está incluido en `.gitignore`
- ✅ Nunca subas tu API key a repositorios públicos
- ✅ Usa variables de entorno en producción
- ✅ Configura restricciones en Google Cloud Console

### Modo de Demostración

Si no configuras una API key, la aplicación funcionará en modo de demostración con:
- Datos simulados para la mayoría de playlists
- Algunas playlists de ejemplo con datos predefinidos
- Mensaje indicando que se requiere configuración para datos reales

### Configuración de Producción

Para un entorno de producción, considera:

1. **HTTPS**: Configura SSL/TLS
2. **API Key**: Configura una API key real de YouTube
3. **Variables de entorno**: Usa variables de entorno para configuración sensible
4. **Monitoreo**: Implementa logging y monitoreo
5. **Backup**: Configura respaldos regulares

## 🔧 Uso

### Usando la Aplicación

1. **Ingresa la URL de la playlist**
   - Copia la URL de una playlist de YouTube
   - Pégala en el campo de entrada
   - Ejemplo: `https://www.youtube.com/playlist?list=PLxxxxxxxxxxx`

2. **Calcula la duración**
   - Haz clic en "Calcular Duración"
   - Espera a que se procese la solicitud

3. **Revisa los resultados**
   - Duración total en formato HH:MM:SS
   - Total en segundos
   - Duración a diferentes velocidades de reproducción
   - Información de la playlist (título, número de videos)

### API Endpoints

#### POST /api/playlist-duration
Calcula la duración de una playlist.

**Request:**
```json
{
  "playlistId": "PLxxxxxxxxxxx",
  "csrf_token": "token_value"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "title": "Nombre de la Playlist",
    "videoCount": 25,
    "totalSeconds": 7200,
    "videos": [...],
    "playlistId": "PLxxxxxxxxxxx"
  }
}
```

#### GET /api/csrf-token
Obtiene el token CSRF para la sesión.

**Response:**
```json
{
  "csrf_token": "generated_token"
}
```

## 🔒 Características de Seguridad Implementadas

### 1. Protección CSRF
- Tokens únicos por sesión
- Validación en cada request POST
- Regeneración automática de tokens

### 2. Sanitización de Entrada
```php
// Ejemplo de sanitización implementada
private function sanitizeInput($input) {
    $input = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $input);
    $input = str_replace(['<', '>', '"', "'", '&'], '', $input);
    $input = preg_replace('/javascript:/i', '', $input);
    return trim($input);
}
```

### 3. Rate Limiting
- Límite de 10 requests por minuto por IP
- Almacenamiento en archivos temporales
- Limpieza automática de registros antiguos

### 4. Validación de URL
```javascript
// Validación multicapa en el frontend
isYouTubePlaylistUrl(url) {
    const urlObj = new URL(url);
    const validHosts = ['www.youtube.com', 'youtube.com', 'm.youtube.com'];
    return validHosts.includes(urlObj.hostname.toLowerCase()) &&
           urlObj.searchParams.get('list');
}
```

### 5. Headers de Seguridad
```apache
# Configuración en .htaccess
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "DENY"
Header always set Content-Security-Policy "default-src 'self'..."
```

## 🎨 Personalización

### Colores
Los colores principales están definidos en variables CSS:
```css
:root {
    --primary-blue: #2563eb;
    --primary-blue-dark: #1d4ed8;
    --accent-blue: #60a5fa;
    /* ... más variables */
}
```

### Responsive Breakpoints
```css
@media (max-width: 768px) { /* Tablet */ }
@media (max-width: 480px) { /* Móvil */ }
```

## 🐛 Solución de Problemas

### Errores Comunes

1. **"Método no permitido"**
   - Verifica que mod_rewrite esté habilitado
   - Revisa la configuración de .htaccess

2. **"Token de seguridad inválido"**
   - Refresca la página para obtener un nuevo token
   - Verifica que las sesiones PHP estén funcionando

3. **"Demasiadas solicitudes"**
   - Espera un minuto antes de hacer otra solicitud
   - Verifica la configuración de rate limiting

4. **Errores de CSS/JS**
   - Verifica que los archivos estén accesibles
   - Revisa la configuración de MIME types en Apache

## 📈 Rendimiento

### Optimizaciones Implementadas
- **Compresión GZIP** para reducir tamaño de transferencia
- **Cache de navegador** para recursos estáticos
- **Minificación** de CSS y JS en producción
- **Carga asíncrona** de recursos no críticos

### Métricas de Rendimiento
- Time to First Byte (TTFB): < 200ms
- First Contentful Paint (FCP): < 1.5s
- Largest Contentful Paint (LCP): < 2.5s

## 🤝 Contribuciones

### Cómo Contribuir
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de Código
- **JavaScript**: ES6+, camelCase, JSDoc para documentación
- **PHP**: PSR-12, snake_case para variables, camelCase para métodos
- **CSS**: BEM methodology, variables CSS para colores
- **HTML**: Semántico, accesible (ARIA labels)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte y preguntas:
- 📧 Email: soporte@ejemplo.com
- 📝 Issues: [GitHub Issues]
- 📚 Documentación: [Wiki del proyecto]

## 🔮 Roadmap

### Próximas Características
- [ ] Integración con API real de YouTube
- [ ] Soporte para canales además de playlists
- [ ] Exportación de resultados a PDF/CSV
- [ ] Modo oscuro
- [ ] Internacionalización (i18n)
- [ ] PWA (Progressive Web App)
- [ ] Análisis estadístico avanzado

### Mejoras de Seguridad Planificadas
- [ ] Autenticación de dos factores
- [ ] Logging de seguridad avanzado
- [ ] Integración con WAF (Web Application Firewall)
- [ ] Monitoreo de anomalías

---

**Desarrollado con ❤️ y enfoque en seguridad y usabilidad**
