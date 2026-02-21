# Guia i18n (Internacionalizacion)

## Objetivo
Garantizar consistencia multilenguaje (es/pt/it/en) en UI y textos operativos.

## Modelo de datos
- Tabla: `cr_localizationstring`
- Clave funcional: `cr_key`
- Columnas de idioma: `cr_text_es`, `cr_text_pt`, `cr_text_it`, `cr_text_en`
- Activacion: `cr_isactive`

## Determinacion de idioma
Implementada en `RepairApp::bootContext()`:
1. Lee `cr_usersettings` del usuario.
2. Si `cr_languagemode = Manual`, usa `cr_preferredlanguage`.
3. Si `Auto`, usa locale del request (fallback `en`).
4. Carga etiquetas activas y aplica fallback a ingles/clave.

## Politica de cambios
- Rol recomendado para cambios funcionales de traduccion: `cr_Calidad`.
- Rol recomendado para soporte tecnico/global: `cr_AdminSistema`.
- Cualquier cambio de clave debe incluir:
  - idioma base en los 4 idiomas
  - modulo (`cr_module`)
  - validacion en pantalla impactada

## Buenas practicas
- No reutilizar claves con semantica distinta.
- Evitar textos hardcoded en vistas nuevas.
- Validar longitud y legibilidad en movil.
- Mantener versionado de cambios en despliegues.

## Checklist de liberacion i18n
1. Clave creada/actualizada en `cr_localizationstring`.
2. Traducciones completas en 4 idiomas.
3. Validacion visual en `app/Views/repair/layout.php`.
4. Verificacion por usuario en `Auto` y `Manual`.
