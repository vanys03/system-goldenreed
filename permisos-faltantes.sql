-- =====================================================================
-- Permisos faltantes  -  2026-10-02
-- El codigo ya verifica estos permisos pero no existen en la base:
-- cualquier usuario que no sea Superadmin tiene esos modulos bloqueados.
-- Se completan las 4 acciones (Ver / Crear / Editar / Eliminar) por modulo.
-- =====================================================================

-- INSERT IGNORE + la llave unica de spatie evitan duplicados si se corre dos veces.
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
  -- clientes rentas  (el codigo usa las 4, ninguna existia)
  ('Ver clientes rentas',      'web', NOW(), NOW()),
  ('Crear clientes rentas',    'web', NOW(), NOW()),
  ('Editar clientes rentas',   'web', NOW(), NOW()),
  ('Eliminar clientes rentas', 'web', NOW(), NOW()),

  -- cuentas  (el codigo usa las 4, ninguna existia)
  ('Ver cuentas',      'web', NOW(), NOW()),
  ('Crear cuentas',    'web', NOW(), NOW()),
  ('Editar cuentas',   'web', NOW(), NOW()),
  ('Eliminar cuentas', 'web', NOW(), NOW()),

  -- perfiles  (el codigo usa las 4, ninguna existia)
  ('Ver perfiles',      'web', NOW(), NOW()),
  ('Crear perfiles',    'web', NOW(), NOW()),
  ('Editar perfiles',   'web', NOW(), NOW()),
  ('Eliminar perfiles', 'web', NOW(), NOW()),

  -- plataformas  (el codigo usa las 4, ninguna existia)
  ('Ver plataformas',      'web', NOW(), NOW()),
  ('Crear plataformas',    'web', NOW(), NOW()),
  ('Editar plataformas',   'web', NOW(), NOW()),
  ('Eliminar plataformas', 'web', NOW(), NOW()),

  -- rentas  (el codigo usa las 4, ninguna existia)
  ('Ver rentas',      'web', NOW(), NOW()),
  ('Crear rentas',    'web', NOW(), NOW()),
  ('Editar rentas',   'web', NOW(), NOW()),
  ('Eliminar rentas', 'web', NOW(), NOW()),

  -- telefonos  (el codigo usa Ver y Crear; Editar y Eliminar se agregan para completar)
  ('Ver telefonos',      'web', NOW(), NOW()),
  ('Crear telefonos',    'web', NOW(), NOW()),
  ('Editar telefonos',   'web', NOW(), NOW()),
  ('Eliminar telefonos', 'web', NOW(), NOW());

-- Superadmin pasa por Gate::before de todos modos, pero se le asignan
-- para que la matriz de Roles lo muestre con todo marcado.
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Superadmin';

-- Limpia el cache de permisos (equivale a permission:cache-reset,
-- porque CACHE_STORE=database). Se reconstruye en la siguiente peticion.
DELETE FROM cache WHERE `key` LIKE '%spatie.permission.cache%';
