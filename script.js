// Clase principal para el manejo de la aplicación
class YouTubePlaylistCalculator {
    constructor() {
        this.form = document.getElementById('playlistForm');
        this.urlInput = document.getElementById('playlistUrl');
        this.submitBtn = document.getElementById('submitBtn');
        this.loading = document.getElementById('loading');
        this.results = document.getElementById('results');
        this.errorMessage = document.getElementById('errorMessage');
        this.urlError = document.getElementById('urlError');

        this.apiKey = null; // Se configurará desde el backend
        this.rateLimiter = new RateLimiter(30, 60000); // 30 requests por minuto
        this.csrfToken = null; // Se obtendrá del backend

        this.init();
    }

    async init() {
        this.attachEventListeners();
        this.setupInputValidation();
        await this.setupCSRFProtection();
    }

    attachEventListeners() {
        this.form.addEventListener('submit', this.handleSubmit.bind(this));

        document.getElementById('resetBtn')?.addEventListener('click', this.reset.bind(this));
        document.getElementById('retryBtn')?.addEventListener('click', this.retry.bind(this));

        // Validación en tiempo real
        this.urlInput.addEventListener('input', this.validateUrlInput.bind(this));
        this.urlInput.addEventListener('paste', this.handlePaste.bind(this));
    }

    setupInputValidation() {
        // Configurar validación HTML5 personalizada
        this.urlInput.addEventListener('invalid', (e) => {
            e.preventDefault();
            this.showUrlError('Por favor, ingresa una URL válida de playlist de YouTube');
        });
    }

    async setupCSRFProtection() {
        // Obtener token CSRF del backend
        await this.fetchCSRFToken();

        // Crear campo oculto para CSRF si tenemos token
        if (this.csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = this.csrfToken;
            this.form.appendChild(csrfInput);
        }
    }

    generateCSRFToken() {
        // Este método ya no se usa, pero se mantiene por compatibilidad
        const array = new Uint8Array(32);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!this.rateLimiter.canMakeRequest()) {
            this.showError('Demasiadas solicitudes. Por favor, espera un momento antes de intentar de nuevo.');
            return;
        }

        const url = this.urlInput.value.trim();

        // Validación y sanitización de entrada
        if (!this.validateAndSanitizeUrl(url)) {
            return;
        }

        this.setLoading(true);

        try {
            const playlistData = await this.fetchPlaylistData(url);
            this.displayResults(playlistData);
        } catch (error) {
            console.error('Error:', error);
            this.showError(error.message || 'Error al procesar la playlist. Por favor, verifica la URL e intenta de nuevo.');
        } finally {
            this.setLoading(false);
        }
    }

    validateAndSanitizeUrl(url) {
        // Limpiar la URL de caracteres peligrosos
        const sanitizedUrl = this.sanitizeInput(url);

        // Validar formato de URL
        if (!this.isValidUrl(sanitizedUrl)) {
            this.showUrlError('Por favor, ingresa una URL válida');
            return false;
        }

        // Validar que sea una URL de YouTube
        if (!this.isYouTubePlaylistUrl(sanitizedUrl)) {
            this.showUrlError('La URL debe ser de una playlist de YouTube válida');
            return false;
        }

        // Validar contra ataques de inyección
        if (this.containsMaliciousPatterns(sanitizedUrl)) {
            this.showUrlError('URL no válida o potencialmente peligrosa');
            return false;
        }

        this.hideUrlError();
        return true;
    }

    sanitizeInput(input) {
        if (typeof input !== 'string') {
            return '';
        }

        // Remover caracteres de control y caracteres peligrosos
        return input
            .replace(/[\x00-\x1F\x7F-\x9F]/g, '') // Caracteres de control
            .replace(/[<>'"&]/g, '') // Caracteres HTML peligrosos
            .replace(/javascript:/gi, '') // Protocolo javascript
            .replace(/data:/gi, '') // Protocolo data
            .replace(/vbscript:/gi, '') // Protocolo vbscript
            .trim()
            .substring(0, 2048); // Limitar longitud
    }

    isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }

    isYouTubePlaylistUrl(url) {
        try {
            const urlObj = new URL(url);
            const hostname = urlObj.hostname.toLowerCase();

            // Verificar dominio de YouTube
            const validHosts = ['www.youtube.com', 'youtube.com', 'm.youtube.com'];
            if (!validHosts.includes(hostname)) {
                return false;
            }

            // Verificar que contenga parámetro de playlist
            const playlistId = urlObj.searchParams.get('list');
            if (!playlistId) {
                return false;
            }

            // Verificar formato del ID de playlist
            const playlistIdPattern = /^[a-zA-Z0-9_-]+$/;
            return playlistIdPattern.test(playlistId) && playlistId.length >= 10;
        } catch (error) {
            return false;
        }
    }

    containsMaliciousPatterns(input) {
        const maliciousPatterns = [
            /(<script|<\/script>)/gi,
            /(javascript:|vbscript:|data:)/gi,
            /(<iframe|<\/iframe>)/gi,
            /(<object|<\/object>)/gi,
            /(<embed|<\/embed>)/gi,
            /(onload|onerror|onclick)/gi,
            /(\.\.|\/\.\.|\.\/)/g,
            /(union|select|insert|update|delete|drop|create|alter)/gi
        ];

        return maliciousPatterns.some(pattern => pattern.test(input));
    }

    async fetchPlaylistData(url) {
        const playlistId = this.extractPlaylistId(url);

        if (!playlistId) {
            throw new Error('No se pudo extraer el ID de la playlist');
        }

        // Asegurar que tenemos el token CSRF
        if (!this.csrfToken) {
            await this.fetchCSRFToken();
        }

        if (!this.csrfToken) {
            throw new Error('No se pudo obtener el token de seguridad');
        }

        // Hacer llamada real a la API del backend
        const response = await this.makeSecureAPICall('/api/playlist-duration', {
            playlistId: playlistId,
            csrf_token: this.csrfToken
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || 'Error al obtener datos de la playlist');
        }

        const result = await response.json();
        return result.data;
    }

    async fetchCSRFToken() {
        try {
            const response = await fetch('api.php?action=csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.csrf_token) {
                    this.csrfToken = data.csrf_token;
                    console.log('Token CSRF obtenido exitosamente');
                } else {
                    console.error('No se pudo obtener token CSRF válido:', data);
                }
            } else {
                console.error('Error HTTP al obtener token CSRF:', response.status);
            }
        } catch (error) {
            console.error('Error al obtener token CSRF:', error);
        }
    }

    async makeSecureAPICall(endpoint, data) {
        try {
            // Usar parámetro action en lugar de endpoint
            const url = 'api.php?action=playlist-duration';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });

            return response;
        } catch (error) {
            throw new Error('Error de conexión con el servidor');
        }
    }



    extractPlaylistId(url) {
        try {
            const urlObj = new URL(url);
            return urlObj.searchParams.get('list');
        } catch (error) {
            return null;
        }
    }

    displayResults(data) {
        this.hideError();

        // Actualizar información de la playlist
        document.getElementById('playlistTitle').textContent = this.escapeHtml(data.title);
        document.getElementById('videoCount').textContent = `${data.videoCount} videos`;

        // Mostrar indicador de modo demo si aplica
        this.displayDemoIndicator(data);

        // Calcular y mostrar duraciones
        this.displayDurations(data.totalSeconds);

        // Mostrar resultados
        this.results.style.display = 'block';
    }

    displayDemoIndicator(data) {
        // Remover indicador previo si existe
        const existingIndicator = document.querySelector('.demo-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        // Verificar si estamos en modo demo
        const isDemoMode = data.description && data.description.includes('DEMO:');

        if (isDemoMode) {
            const playlistInfo = document.querySelector('.playlist-info');
            const demoIndicator = document.createElement('div');
            demoIndicator.className = 'demo-indicator';
            demoIndicator.innerHTML = `
                <div class="demo-warning">
                    <i class="fas fa-info-circle"></i>
                    <span>Modo Demostración: Los datos mostrados son simulados. 
                    <a href="#setup" onclick="this.showApiSetupInfo()">Configura una API key de YouTube</a> para obtener datos reales.</span>
                </div>
            `;

            playlistInfo.appendChild(demoIndicator);
        }
    }

    showApiSetupInfo() {
        const message = `
            <h3>Configuración de API de YouTube</h3>
            <p>Para obtener datos reales de playlists de YouTube:</p>
            <ol>
                <li>Ve a <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Crea un proyecto y habilita YouTube Data API v3</li>
                <li>Genera una API key</li>
                <li>Configúrala en el archivo .env del proyecto</li>
            </ol>
            <p>Consulta el README.md para instrucciones detalladas.</p>
        `;

        this.showError(message);
    }

    displayDurations(totalSeconds) {
        // Duración total formateada
        const totalDuration = this.formatDuration(totalSeconds);
        document.getElementById('totalDuration').textContent = totalDuration;

        // Total en segundos
        document.getElementById('totalSeconds').textContent = `${totalSeconds.toLocaleString()} segundos`;

        // Tiempo estimado
        const estimatedText = this.getEstimatedTimeText(totalSeconds);
        document.getElementById('estimatedTime').textContent = estimatedText;

        // Duraciones a diferentes velocidades
        this.displaySpeedDurations(totalSeconds);
    }

    displaySpeedDurations(totalSeconds) {
        const speeds = [1.25, 1.5, 1.75, 2];

        speeds.forEach(speed => {
            const adjustedSeconds = Math.round(totalSeconds / speed);
            const formattedDuration = this.formatDuration(adjustedSeconds);
            const elementId = `speed${Math.round(speed * 100)}`;

            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = formattedDuration;
            }
        });
    }

    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;

        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        } else {
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    }

    getEstimatedTimeText(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        if (hours > 0) {
            return `${hours}h ${minutes}m a velocidad 1x`;
        } else {
            return `${minutes} minutos a velocidad 1x`;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    validateUrlInput() {
        const url = this.urlInput.value.trim();

        if (url && !this.isYouTubePlaylistUrl(url)) {
            this.showUrlError('Debe ser una URL válida de playlist de YouTube');
        } else {
            this.hideUrlError();
        }
    }

    handlePaste(e) {
        // Validar y limpiar datos pegados
        setTimeout(() => {
            const pastedValue = this.urlInput.value;
            const sanitizedValue = this.sanitizeInput(pastedValue);

            if (pastedValue !== sanitizedValue) {
                this.urlInput.value = sanitizedValue;
            }

            this.validateUrlInput();
        }, 0);
    }

    showUrlError(message) {
        this.urlError.textContent = message;
        this.urlError.classList.add('show');
    }

    hideUrlError() {
        this.urlError.classList.remove('show');
    }

    showError(message) {
        document.getElementById('errorText').textContent = message;
        this.errorMessage.style.display = 'block';
        this.results.style.display = 'none';
    }

    hideError() {
        this.errorMessage.style.display = 'none';
    }

    setLoading(isLoading) {
        if (isLoading) {
            this.loading.style.display = 'block';
            this.results.style.display = 'none';
            this.errorMessage.style.display = 'none';
            this.submitBtn.disabled = true;
        } else {
            this.loading.style.display = 'none';
            this.submitBtn.disabled = false;
        }
    }

    reset() {
        this.form.reset();
        this.results.style.display = 'none';
        this.errorMessage.style.display = 'none';
        this.hideUrlError();
        this.urlInput.focus();
    }

    retry() {
        this.hideError();
        this.handleSubmit(new Event('submit'));
    }
}

// Clase para rate limiting
class RateLimiter {
    constructor(maxRequests, timeWindow) {
        this.maxRequests = maxRequests;
        this.timeWindow = timeWindow;
        this.requests = [];
    }

    canMakeRequest() {
        const now = Date.now();

        // Remover requests antiguos
        this.requests = this.requests.filter(time => now - time < this.timeWindow);

        if (this.requests.length >= this.maxRequests) {
            return false;
        }

        this.requests.push(now);
        return true;
    }
}

// Utilidades de seguridad adicionales
class SecurityUtils {
    static generateNonce() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    static validateContentSecurityPolicy() {
        const meta = document.createElement('meta');
        meta.httpEquiv = 'Content-Security-Policy';
        meta.content = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self';";
        document.head.appendChild(meta);
    }

    static setupXSSProtection() {
        // Nota: Algunos headers de seguridad deben ser configurados en el servidor
        // Estos meta tags son solo para compatibilidad con navegadores antiguos
        const existingMeta = document.querySelector('meta[http-equiv="X-XSS-Protection"]');
        if (!existingMeta) {
            const meta1 = document.createElement('meta');
            meta1.httpEquiv = 'X-XSS-Protection';
            meta1.content = '1; mode=block';
            document.head.appendChild(meta1);
        }

        const existingContentType = document.querySelector('meta[http-equiv="X-Content-Type-Options"]');
        if (!existingContentType) {
            const meta2 = document.createElement('meta');
            meta2.httpEquiv = 'X-Content-Type-Options';
            meta2.content = 'nosniff';
            document.head.appendChild(meta2);
        }

        // X-Frame-Options no puede ser configurado via meta tag, solo via headers HTTP
        // Este será manejado por el servidor en .htaccess
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Configurar protecciones de seguridad
    SecurityUtils.validateContentSecurityPolicy();
    SecurityUtils.setupXSSProtection();

    // Inicializar la aplicación principal
    const app = new YouTubePlaylistCalculator();

    // Hacer la aplicación accesible globalmente para debugging (solo en desarrollo)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        window.playlistApp = app;
    }
});

// Prevenir ataques de click-jacking
if (window.top !== window.self) {
    window.top.location = window.self.location;
}

// Service Worker para cache y seguridad adicional (opcional)
if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
    navigator.serviceWorker.register('/sw.js').catch(err => {
        console.log('Service Worker registration failed:', err);
    });
}
