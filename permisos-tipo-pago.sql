-- =====================================================================
-- Permisos de tipo de pago en ventas  -  2026-10-02
-- Separan quien puede cobrar en efectivo de quien puede cobrar por
-- transferencia. Se asignan a TODOS los roles para no cambiar el
-- comportamiento actual; a partir de aqui se quitan desde Roles.
-- Idempotente: INSERT IGNORE sobre llaves unicas, se puede correr 2 veces.
-- =====================================================================

INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('Crear pagos efectivo',      'web', NOW(), NOW()),
  ('Crear pagos transferencia', 'web', NOW(), NOW());

-- A todos los roles existentes, para que nadie pierda lo que hoy puede hacer.
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE p.name IN ('Crear pagos efectivo', 'Crear pagos transferencia');

-- Comprobacion
SELECT p.name AS permiso, COUNT(rp.role_id) AS roles_con_el_permiso
FROM permissions p
LEFT JOIN role_has_permissions rp ON rp.permission_id = p.id
WHERE p.name IN ('Crear pagos efectivo', 'Crear pagos transferencia')
GROUP BY p.name;

-- =====================================================================
-- Limpia el cache de permisos. Equivale a "php artisan permission:cache-reset"
-- porque CACHE_STORE=database: el cache de spatie es una fila de esta tabla.
-- El LIKE cubre cualquier prefijo (aqui es goldenred_cache_, en otro
-- ambiente puede ser distinto segun APP_NAME / CACHE_PREFIX).
-- Borrarla no pierde nada: se reconstruye sola en la siguiente peticion.
-- =====================================================================
DELETE FROM cache WHERE `key` LIKE '%spatie.permission.cache%';
  