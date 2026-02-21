# Manual de Rol: AdminSistema

## Objetivo del rol
Mantener configuracion global, seguridad, catalogos y continuidad operativa.

## Pantallas usadas
- `app/settings/system`
- `app/people`
- `app/settings/user`
- Vistas operativas para soporte transversal

## Responsabilidades
1. Gestion de roles y permisos (`cr_security_*`).
2. Mantenimiento de catalogos (`cr_choice_set`, `cr_choice_option`).
3. Mantenimiento i18n (`cr_localizationstring`).
4. Politicas SLA (`cr_prioritysla`).
5. Soporte a auditoria y evidencia de gobierno.

## Controles tecnicos
- Variables SQL de contexto por request (`@app_useremail`, `@app_role_name`, `@app_is_admin`).
- Triggers de seguridad y auditoria activos.
- Administracion de respaldo y recuperacion en infraestructura Docker/MySQL.

## Restricciones y excepciones
- Unico rol con borrado administrado en escenarios permitidos por trigger.
- Cualquier cambio estructural requiere control de cambio y registro.
