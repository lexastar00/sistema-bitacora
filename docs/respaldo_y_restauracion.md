# Respaldo y restauración

Realiza respaldos periódicos y conserva una copia fuera de la computadora donde se ejecuta la aplicación. No guardes copias en `public/`.

## SQLite (configuración local actual)

La base de datos configurada en `.env` es `database/database.sqlite`; los adjuntos y DOCX privados están en `storage/app/private/`.

1. Detén `php artisan serve` para evitar copiar una base SQLite mientras recibe escrituras.
2. Copia `database/database.sqlite` y toda la carpeta `storage/app/private/` a una ubicación segura con fecha.
3. Reinicia el servidor.

Para restaurar, detén el servidor, conserva una copia de los archivos actuales, reemplaza la base SQLite y la carpeta de archivos privados por el respaldo correspondiente y reinicia. Mantén juntos los respaldos de base y archivos: sus rutas están relacionadas.

## MySQL (servidor compartido o producción)

Exporta la base usando herramientas de MySQL (`mysqldump` o la utilidad administrada por el proveedor), incluyendo estructura y datos. Respalda también `storage/app/private/`. Protege los archivos resultantes con acceso restringido y cifrado.

Para restaurar, importa el dump en la base configurada en `.env`, restaura el almacenamiento privado en la misma ubicación y comprueba `php artisan migrate:status`. No ejecutes migraciones destructivas sobre una restauración sin validar primero el respaldo.

## Comprobación

- Prueba periódicamente la restauración en una instancia separada.
- Verifica que se puedan descargar evidencias y documentos archivados.
- No copies `.env` a los respaldos de acceso general: contiene claves y credenciales.
- Antes de producción, configura retención, cifrado, permisos del sistema operativo y un proceso automático de respaldo.
