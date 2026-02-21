# Fase 3 - Arquitectura App Responsive (CodeIgniter 4)

## Estructura implementada
- Controlador principal: `app/Controllers/RepairApp.php`
- Vista base reutilizable: `app/Views/repair/layout.php`
- Estilos y comportamiento UI:
  - `public/assets/repair/app.css`
  - `public/assets/repair/app.js`
- Rutas: `app/Config/Routes.php` bajo prefijo `/app/*`

## OnStart equivalente (servidor)
El método `bootContext()` en `RepairApp` funciona como `App OnStart`:
- Carga usuario actual por email (`?user=`).
- Lee `cr_usersettings` (LanguageMode, PreferredLanguage, DefaultSite).
- Determina `varLang`:
  - Manual -> `cr_preferredlanguage`
  - Auto -> mapeo por locale (`es/pt/it/en`, default `en`)
- Carga i18n en memoria desde `cr_localizationstring` y crea diccionario `labels`.
- Carga roles (`cr_security_user_role`) y permisos por recurso (`cr_security_table_permission`).
- Calcula alcance global para Coordinación/Calidad/Admin.

## Componentes reutilizables
- Layout compartido con:
  - Header + buscador global
  - Navegación lateral (desktop)
  - Navegación compacta (móvil)
- Patrones reutilizados:
  - tarjetas KPI
  - badges de estado
  - tablas responsive
  - formularios con validación básica

## i18n aplicado
- Patrón en vista: `labels[cr_key]` con fallback.
- Idioma activo por usuario y modo Auto/Manual.
- Integración con tabla `cr_localizationstring`.

## Multi-sede y seguridad UI
- Scope de lectura tickets:
  - Roles globales ven todo.
  - Resto filtrado por `defaultSite` sobre `cr_currentsite_id` / `cr_sitein_id`.
- Control de acciones por permisos (`cr_security_table_permission`):
  - Mostrar/ocultar acciones de estado/cierres en Ticket Detail.

## Timeline unificado
- `buildTimeline()` combina:
  - `cr_tickethistory`
  - `cr_worklog`
  - `cr_movement`
- Orden cronológico descendente para vista de trazabilidad.
