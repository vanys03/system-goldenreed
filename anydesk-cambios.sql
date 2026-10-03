-- =====================================================================
-- Cambios al modulo AnyDesk  -  2026-10-02
-- 1. Borra el registro de prueba
-- 2. Quita la columna nombre (se reemplaza por imagenes)
-- 3. Crea la tabla de imagenes
-- 4. Crea los permisos del modulo (no existian en esta base)
-- =====================================================================

START TRANSACTION;

-- 1. Registro de prueba (VENTURA GONZAGA MENESES, codigo 78, torre 97)
DELETE FROM anydesks WHERE codigo = '78' AND torre = '97';

-- 2. La columna nombre ya no se usa: el acceso se identifica por torre + codigo + imagenes
ALTER TABLE anydesks DROP COLUMN nombre;

-- 3. Varias imagenes por acceso AnyDesk
CREATE TABLE `anydesk_imagenes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `anydesk_id` bigint(20) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anydesk_imagenes_anydesk_id_foreign` (`anydesk_id`),
  CONSTRAINT `anydesk_imagenes_anydesk_id_foreign`
    FOREIGN KEY (`anydesk_id`) REFERENCES `anydesks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Permisos del modulo. Solo necesitan existir para salir en la matriz de Roles;
--    Superadmin pasa por Gate::before de todos modos.
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('Ver anydesks',      'web', NOW(), NOW()),
  ('Crear anydesks',    'web', NOW(), NOW()),
  ('Editar anydesks',   'web', NOW(), NOW()),
  ('Eliminar anydesks', 'web', NOW(), NOW());

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE p.name IN ('Ver anydesks', 'Crear anydesks', 'Editar anydesks', 'Eliminar anydesks')
  AND r.name = 'Superadmin';

COMMIT;
