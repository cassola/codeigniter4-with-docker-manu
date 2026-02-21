# Manual de Rol: Recepcion

## Objetivo del rol
Registrar el ingreso del equipo, abrir ticket y asegurar evidencia inicial.

## Pantallas usadas
- `app/ticket-create`
- `app/tickets`
- `app/ticket/{id}`
- `app/search`

## Flujo operativo
1. Crear ticket en `Create Ticket`.
2. Capturar: sede, aviso SAP, planta, activo, prioridad, falla reportada.
3. Validar que el ticket quede en estado inicial (`Received` por politica de sede).
4. Adjuntar evidencias iniciales cuando aplique (ejemplo: dano por transporte).
5. Cambiar estado a `Diagnosis` cuando se entrega al equipo tecnico.

## Evidencias minimas
- Registro del ticket (`cr_ticket`).
- Historia de cambio de estado (`cr_tickethistory`).
- Documentos de recepcion (`cr_ticketdocument`) si hay dano de transporte.

## Cambios permitidos
- Crear ticket.
- Actualizacion basica de datos de ticket en su sede.
- Cambio de estado inicial segun matriz de seguridad.

## Controles y restricciones
- No puede borrar tickets.
- No puede cerrar administrativamente.
- Solo ve tickets de su alcance de sede (excepto roles globales).
