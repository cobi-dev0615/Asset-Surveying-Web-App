# Guía de Despliegue — SER Inventarios

Servidor: HostGator Mexico CPanel
Dominio: `app.seretail.com.mx`
PHP: 8.2+
MySQL: 8.0+

---

## 1. Preparar CPanel

### 1.1 Crear subdominio
- CPanel → Subdominios → Crear `app.seretail.com.mx`
- Document root: `/home/CPANEL_USER/app.seretail.com.mx/public`

### 1.2 Configurar PHP
- CPanel → PHP Selector (o MultiPHP Manager)
- Seleccionar PHP 8.2 para `app.seretail.com.mx`
- Extensiones requeridas: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `gd`

### 1.3 Crear base de datos
- CPanel → MySQL Databases
- Crear database: `CPANEL_USER_seretail`
- Crear usuario: `CPANEL_USER_seruser`
- Asignar usuario a database con **TODOS los privilegios**
- Anotar: nombre de DB, usuario y contraseña

---

## 2. Subir archivos

### Opción A: SSH (recomendado)
```bash
# Desde tu máquina local
rsync -avz --exclude=node_modules --exclude=vendor --exclude=.env \
  web-platform/ CPANEL_USER@seretail.com.mx:~/app.seretail.com.mx/
```

### Opción B: SFTP
- Subir todo el contenido de `web-platform/` a `/home/CPANEL_USER/app.seretail.com.mx/`
- Excluir: `node_modules/`, `vendor/`, `.env`

### Opción C: Git
```bash
cd ~/app.seretail.com.mx
git clone REPO_URL .
```

---

## 3. Desplegar

```bash
cd ~/app.seretail.com.mx

# Ejecutar script de despliegue
chmod +x deploy.sh
bash deploy.sh
```

En la primera ejecución:
1. El script creará `.env` desde `.env.production`
2. Se detendrá pidiendo que edites las credenciales de BD
3. Editar `.env`:
   ```
   DB_DATABASE=CPANEL_USER_seretail
   DB_USERNAME=CPANEL_USER_seruser
   DB_PASSWORD=tu_contraseña_aqui
   ```
4. Ejecutar `bash deploy.sh` de nuevo

---

## 4. Importar datos de producción

```bash
# Importar el dump SQL de producción
mysql -u CPANEL_USER_seruser -p CPANEL_USER_seretail < dump_produccion.sql
```

O desde CPanel → phpMyAdmin → Importar.

---

## 5. Verificar

1. Abrir `https://app.seretail.com.mx`
2. Login: `avillegas` / `admin123`
3. Verificar dashboard, menú lateral, datos
4. Probar API: `curl https://app.seretail.com.mx/api/login -d '{"usuario":"...", "password":"...", "device_name":"test"}'`

---

## 6. Configurar .htaccess (si es necesario)

El archivo `public/.htaccess` de Laravel debería funcionar. Si hay problemas de URL, verificar que `mod_rewrite` está activo en CPanel.

---

## 7. SSL

HostGator Mexico incluye Let's Encrypt. Verificar en CPanel → SSL/TLS que el certificado esté activo para `app.seretail.com.mx`.

---

## Mantenimiento

```bash
# Limpiar todos los cachés
php artisan optimize:clear

# Re-cachear después de cambios
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Modo mantenimiento
php artisan down
php artisan up

# Ver logs
tail -f storage/logs/laravel.log
```

---

## Estructura de directorios en CPanel

```
/home/CPANEL_USER/
└── app.seretail.com.mx/          ← Raíz del proyecto Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/                    ← Document root del subdominio
    │   ├── index.php
    │   ├── .htaccess
    │   └── storage -> ../storage/app/public
    ├── resources/
    ├── routes/
    ├── storage/
    │   └── app/public/fotos/      ← Imágenes de activos
    ├── vendor/
    ├── .env
    ├── composer.json
    └── deploy.sh
```
