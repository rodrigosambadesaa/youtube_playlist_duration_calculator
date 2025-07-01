# 🔒 Configuración Segura de API Key de YouTube

## Restricciones Recomendadas para Google Cloud Console

### 📍 **Restricciones de Aplicaciones**

#### **Opción Recomendada: "Sitios web"**

**Para desarrollo local:**
```
http://localhost/*
http://127.0.0.1/*
```

**Para producción:**
```
https://tudominio.com/*
https://www.tudominio.com/*
```

**Configuración completa (desarrollo + producción):**
```
http://localhost/*
http://127.0.0.1/*
https://tudominio.com/*
https://www.tudominio.com/*
```

### 🎯 **Restricciones de API**

#### **Seleccionar: "Restringir clave"**

**APIs permitidas (marcar SOLO esta):**
- ✅ **YouTube Data API v3**

**NO permitir:**
- ❌ Todas las demás APIs

## 🚨 **IMPORTANTE: ¿Qué API Seleccionar?**

**Si no ves "YouTube Data API v3" en la lista de APIs disponibles:**

1. **Primero debes habilitarla:**
   - Ve a **"APIs y servicios" → "Biblioteca"**
   - Busca **"YouTube Data API v3"**
   - Haz clic en **"HABILITAR"**
   - Espera a que se habilite (puede tomar unos minutos)

2. **Luego configurar restricciones:**
   - Ve a **"APIs y servicios" → "Credenciales"**
   - Haz clic en tu API key
   - En **"Restricciones de API"**, selecciona **"Restringir clave"**
   - Ahora SÍ deberías ver **"YouTube Data API v3"** en la lista

**De la lista que proporcionaste (BigQuery, Cloud Storage, etc.):**
- ❌ **NO selecciones NINGUNA de esas APIs**
- ❌ Esas son APIs diferentes para otros servicios de Google Cloud
- ✅ **Tu aplicación necesita específicamente "YouTube Data API v3"**

**Si sigues sin ver "YouTube Data API v3":**
- Verifica que estás en el proyecto correcto
- Asegúrate de que la API esté habilitada
- Prueba refrescar la página del navegador

## 🚨 **PROBLEMA ACTUAL DETECTADO**

**Tu API Key está bloqueada por restricciones de referente demasiado estrictas:**

```
Error 403: "Requests from referer <empty> are blocked"
Reason: "API_KEY_HTTP_REFERRER_BLOCKED"
```

### 🔧 **SOLUCIÓN URGENTE:**

**Opción A: Quitar Restricciones Temporalmente (PARA TESTING)**
1. Ve a Google Cloud Console → Credenciales
2. Edita tu API Key
3. En **"Restricciones de aplicaciones"** selecciona **"Ninguna"**
4. Guarda los cambios
5. Prueba tu aplicación

**Opción B: Configurar Restricciones Correctamente**
1. Ve a Google Cloud Console → Credenciales  
2. Edita tu API Key
3. En **"Restricciones de aplicaciones"** selecciona **"Sitios web"**
4. En **"Restricciones de referentes de sitios web"** agrega:
   ```
   http://localhost/*
   http://127.0.0.1/*
   *
   ```
   ⚠️ **NOTA:** La línea `*` permite cualquier referente (menos seguro pero funcional)

5. Guarda los cambios

**Opción C: Sin Restricciones de Aplicación (MÁS SEGURO)**
1. Ve a Google Cloud Console → Credenciales
2. Edita tu API Key  
3. En **"Restricciones de aplicaciones"** selecciona **"Ninguna"**
4. En **"Restricciones de API"** mantén solo **"YouTube Data API v3"**
5. Guarda los cambios

### ⚡ **RECOMENDACIÓN INMEDIATA:**
Usa la **Opción A** o **Opción C** temporalmente para que funcione, luego ajusta las restricciones según necesites.

### 🛡️ **Configuración Paso a Paso**

#### **Paso 1: Restricciones de Aplicaciones**
1. Selecciona **"Sitios web"**
2. En "Restricciones de referentes de sitios web", agrega:

**Para desarrollo local:**
```
http://localhost/*
http://127.0.0.1/*
```

**Para desarrollo con XAMPP (puerto específico):**
```
http://localhost:80/*
http://localhost:8080/*
http://127.0.0.1:80/*
http://127.0.0.1:8080/*
```

**Para producción (cuando tengas dominio):**
```
https://tudominio.com/*
https://www.tudominio.com/*
```

#### **Paso 2: Restricciones de API**
1. Selecciona **"Restringir clave"**
2. En la lista desplegable, busca y selecciona:
   - **YouTube Data API v3**
3. **NO selecciones ninguna otra API**

### ⚙️ **Configuraciones Específicas por Entorno**

#### **🏠 Desarrollo Local (XAMPP)**
```
Restricciones de aplicaciones: Sitios web
Referentes permitidos:
  http://localhost/*
  http://127.0.0.1/*
  
Restricciones de API: Restringir clave
APIs permitidas:
  YouTube Data API v3
```

#### **🌐 Producción**
```
Restricciones de aplicaciones: Sitios web
Referentes permitidos:
  https://tudominio.com/*
  https://www.tudominio.com/*
  
Restricciones de API: Restringir clave
APIs permitidas:
  YouTube Data API v3
```

#### **🔧 Desarrollo + Producción**
```
Restricciones de aplicaciones: Sitios web
Referentes permitidos:
  http://localhost/*
  http://127.0.0.1/*
  https://tudominio.com/*
  https://www.tudominio.com/*
  
Restricciones de API: Restringir clave
APIs permitidas:
  YouTube Data API v3
```

### 🚨 **Patrones de Referentes Comunes**

#### **✅ Permitidos (ejemplos):**
```
http://localhost/*                    (desarrollo local)
http://127.0.0.1/*                   (desarrollo local IP)
https://miapp.com/*                  (producción)
https://www.miapp.com/*              (producción con www)
https://miapp.herokuapp.com/*        (Heroku)
https://miapp.vercel.app/*           (Vercel)
https://miapp.netlify.app/*          (Netlify)
```

#### **❌ NO Recomendados:**
```
*                                    (cualquier dominio - MUY INSEGURO)
http://*                            (cualquier HTTP - INSEGURO)
https://*                           (cualquier HTTPS - INSEGURO)
```

### 📊 **Niveles de Seguridad**

#### **🔴 Nivel Básico (Mínimo Requerido):**
- Restricción de API: Solo YouTube Data API v3
- Sin restricción de referentes (NO recomendado)

#### **🟡 Nivel Intermedio:**
- Restricción de API: Solo YouTube Data API v3
- Restricción de sitios web: Tu dominio principal

#### **🟢 Nivel Avanzado (Recomendado):**
- Restricción de API: Solo YouTube Data API v3
- Restricción de sitios web: Dominios específicos (dev + prod)
- Monitoreo de uso activado

### 🔧 **Configuración Detallada en Console**

#### **En la sección "Restricciones de aplicaciones":**
1. Haz clic en **"Sitios web"**
2. En el campo "Restricciones de referentes de sitios web", ingresa **una línea por dominio:**
   ```
   http://localhost/*
   http://127.0.0.1/*
   ```
3. Si planeas usar en producción, agrega también:
   ```
   https://tudominio.com/*
   https://www.tudominio.com/*
   ```

#### **En la sección "Restricciones de API":**
1. Selecciona **"Restringir clave"**
2. En el dropdown que aparece, busca **"YouTube Data API v3"**
3. Selecciónala (debe aparecer una marca de verificación)
4. **NO selecciones ninguna otra API**

### ⏱️ **Tiempo de Aplicación**
- Las restricciones pueden tardar **hasta 5 minutos** en aplicarse
- Después de guardar, espera antes de probar
- Si hay errores, verifica la configuración y espera un poco más

### 🧪 **Testing de Restricciones**

#### **Test 1: Verificar que funciona desde tu dominio**
```bash
# Abre la aplicación desde tu dominio permitido
http://localhost/youtube_playlist_duration_calculator/

# Debe funcionar normalmente
```

#### **Test 2: Verificar que NO funciona desde dominios no autorizados**
```bash
# Intenta acceder desde un proxy o dominio diferente
# Debe dar error de API key
```

### 🚨 **Errores Comunes y Soluciones**

#### **Error: "API key not valid. Please pass a valid API key."**
**Causa:** Referente no autorizado
**Solución:** Verificar que el dominio esté en la lista de referentes permitidos

#### **Error: "The request is missing a valid API key."**
**Causa:** API key no configurada o incorrecta
**Solución:** Verificar que la API key esté correctamente configurada

#### **Error: "Access Not Configured"**
**Causa:** YouTube Data API v3 no habilitada o no en restricciones
**Solución:** Habilitar la API y agregarla a restricciones

### 📋 **Checklist de Configuración**

- [ ] API key creada en Google Cloud Console
- [ ] YouTube Data API v3 habilitada en el proyecto
- [ ] Restricción de aplicación configurada (Sitios web)
- [ ] Referentes específicos agregados (localhost + producción)
- [ ] Restricción de API configurada (Solo YouTube Data API v3)
- [ ] Configuración guardada y esperado 5 minutos
- [ ] API key copiada al archivo .env
- [ ] Testing realizado desde dominio autorizado

### 🎯 **Configuración Final Recomendada**

```
Nombre: YouTube Playlist Calculator API Key

Restricciones de aplicaciones: Sitios web
Referentes permitidos:
  http://localhost/*
  http://127.0.0.1/*
  [tu dominio de producción si aplica]

Restricciones de API: Restringir clave
APIs permitidas:
  YouTube Data API v3

Estado: Activa ✅
```

Esta configuración garantiza que tu API key solo funcione desde tus dominios autorizados y únicamente con la API de YouTube, maximizando la seguridad.
