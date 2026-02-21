# Manual de Uso - Repair Management (CodeIgniter 4 + MySQL)

## 1. Objetivo
Aplicacion para gestionar tickets de reparacion electronica multi-sede, con flujo tipo Power Apps (vistas, formularios por secciones, subgrids e indicadores).

## 2. Acceso
- App: `http://localhost:8006`
- phpMyAdmin: `http://localhost:8080`

## 3. Credenciales de base de datos
Para phpMyAdmin:
- Servidor: `mysql`
- Usuario: `mysql`
- Password: `mysql`

Opcional admin:
- Usuario: `root`
- Password: `root`

Base principal:
- `development`

## 4. Estructura de navegacion (Sitemap)
En la barra lateral de la app:
- `Tickets`
- `Assets`
- `Customers`
- `Master Data`
- `Reports` (Dashboard)
- `Admin`

## 5. Flujo principal de uso (Tickets)

### 5.1 Crear ticket
1. Ir a `Tickets`.
2. Pulsar `New Ticket`.
3. Completar obligatorios:
- `Intake Site`
- `SAP Notice Number`
- `Customer Plant`
- `Asset`
- `Reported Failure`
4. Pulsar `Create Ticket`.

Reglas aplicadas:
- No permite duplicado por combinacion (`Intake Site` + `SAP Notice Number`).
- Si no se informa `Intake Site` y hay perfil de empleado, toma `HomeSite`.
- Aplica defaults por sede (estado/prioridad) desde `rm_siteconfiguration`.

### 5.2 Revisar ticket por pestañas
Dentro de un ticket:
- `Intake`: datos base del aviso.
- `Diagnosis`: cambio de estado y diagnostico.
- `Repair`: alta de material usado + subgrid de materiales.
- `Logistics`: transferencia entre sedes + subgrid de movimientos.
- `History`: subgrid de historial de estados.

### 5.3 Cambiar estado (historial automatico)
1. En pestaña `Diagnosis`, seleccionar nuevo estado.
2. Completar comentario (opcional) y pulsar `Apply Status`.
3. El sistema inserta automaticamente un registro en `Status History`.

Regla de cierre:
- Si en configuracion de sede `close_requires_solution = 1`, no permite cerrar sin `Repair Solution`.

### 5.4 Transferir ticket de sede
1. En pestaña `Logistics`, elegir `To Site`.
2. Pulsar `Transfer`.
3. El sistema:
- Actualiza `Current Processing Site` del ticket.
- Inserta movimiento en `rm_assetmovement`.
- Actualiza ubicacion actual del asset.
- Registra evento en historial de estados.

### 5.5 Registrar material usado
1. En pestaña `Repair`, seleccionar material y cantidad.
2. Pulsar `Add Material`.
3. Se agrega en subgrid `Materials`.

## 6. Modulos de consulta

### 6.1 Reports
- KPIs: total tickets, abiertos, assets en reparacion, materiales usados.
- Tabla tickets por estado.
- Tabla tickets por sede.

### 6.2 Assets
- Lista serial, modelo, estado, sede actual y tipo de propietario.

### 6.3 Customers
- Clientes y plantas asociadas.

### 6.4 Master Data
- Sedes, modelos y materiales cargados.

### 6.5 Admin
- Configuracion por sede (estado/prioridad por defecto, reglas de cierre, checklist).

## 7. Datos de ejemplo incluidos
Se cargan automaticamente con el seeder:
- 4 sedes (`ES-CAST`, `IT-MOD`, `PT-XXX`, `FR-XXX`)
- 2 clientes
- 3 plantas
- 3 modelos
- 5 materiales
- 2 assets
- 2 tickets

## 8. Comandos utiles de operacion

### Levantar entorno
```bash
docker compose up -d --build
```

### Ejecutar migraciones
```bash
docker compose exec php php spark migrate
```

### Cargar datos semilla
```bash
docker compose exec php php spark db:seed RepairManagementSeeder
```

### Reiniciar base (desarrollo)
```bash
yes y | docker compose exec -T php php spark migrate:refresh -all
docker compose exec php php spark db:seed RepairManagementSeeder
```

## 9. Problemas frecuentes

### Error `Boot.php` no encontrado
Faltan dependencias de Composer:
```bash
docker compose exec php composer update --no-interaction --no-progress
```

### MySQL no healthy
Verificar logs:
```bash
docker compose logs --tail=200 mysql
```

### App no responde en `:8006`
Comprobar contenedores:
```bash
docker compose ps
```

## 10. Alcance actual (MVP)
- Implementado end-to-end para operacion interna.
- Seguridad por roles detallada y autenticacion real: pendiente para fase siguiente.
- Integracion real con Power Platform/Dataverse: no aplica en este MVP (se usa MySQL).
