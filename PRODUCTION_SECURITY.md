# 🔐 Guía de Restricciones del API Key para Producción

Una vez que confirmes que todo funciona correctamente, es **muy importante** reactivar las restricciones del API key de YouTube para mayor seguridad.

## 📋 Pasos para Configurar Restricciones Seguras

### 1. Accede a Google Cloud Console
- Ve a: https://console.cloud.google.com/
- Selecciona tu proyecto
- Ve a **APIs & Services** > **Credentials**

### 2. Edita tu API Key
- Encuentra tu API key de YouTube
- Haz clic en el ícono de **editar** (lápiz)

### 3. Configura Restricciones de Aplicación

**Opción A: Restricción por HTTP Referrers (Recomendado para desarrollo local)**
```
http://localhost/*
https://localhost/*
http://127.0.0.1/*
```

**Opción B: Restricción por HTTP Referrers (Para producción)**
```
https://tudominio.com/*
https://www.tudominio.com/*
```

**Opción C: Restricción por IP (Para servidores específicos)**
- Agrega la IP pública de tu servidor

### 4. Configura Restricciones de API

**Seleccionar SOLO esta API:**
- ✅ **YouTube Data API v3**

**NO seleccionar otras APIs como:**
- ❌ YouTube Analytics API
- ❌ YouTube Reporting API
- ❌ Otras APIs de Google

### 5. Guarda los Cambios
- Haz clic en **"Save"**
- Espera unos minutos para que los cambios se propaguen

## 🧪 Verificar que Sigue Funcionando

Después de aplicar las restricciones:

1. Espera **5-10 minutos** para que se propaguen los cambios
2. Prueba tu aplicación nuevamente:
   ```
   http://localhost/youtube_playlist_duration_calculator/index.html
   ```
3. Si aparece error "API_KEY_HTTP_REFERRER_BLOCKED":
   - Verifica que agregaste `http://localhost/*` en las restricciones
   - Espera un poco más y vuelve a intentar

## 🚨 Importante para Producción

Cuando subas la aplicación a un servidor en vivo:

1. **Actualiza las restricciones** para incluir tu dominio:
   ```
   https://tudominio.com/*
   https://www.tudominio.com/*
   ```

2. **Remueve localhost** de las restricciones:
   ```
   ❌ http://localhost/*  (remover en producción)
   ```

3. **Configura HTTPS** obligatorio en tu servidor

## 🔍 Monitoreo

- Revisa regularmente el uso de tu API key en Google Cloud Console
- Configura alertas de cuota si es necesario
- Mantén tu API key segura y nunca la subas a repositorios públicos

---

## 📞 Troubleshooting

Si encuentras problemas después de aplicar restricciones:

1. **Error 403**: Verifica las restricciones de referrer
2. **Error 400**: Verifica que solo tienes YouTube Data API v3 habilitada
3. **Error 429**: Has superado la cuota, espera o solicita aumento

¡Tu aplicación ahora está lista y segura para producción! 🎉
