# RepairManagement MVP (CodeIgniter 4 + MySQL)

## 1) Especificacion tecnica
- Prefijo de tablas: `rm_`
- Tablas maestras: `rm_site`, `rm_customer`, `rm_customerplant`, `rm_productmodel`, `rm_material`, `rm_user`
- Tablas operativas: `rm_asset`, `rm_repairticket`, `rm_ticketstatushistory`, `rm_ticketmaterial`, `rm_siteconfiguration`, `rm_employeeprofile`, `rm_assetmovement`, `rm_assetownership`
- Claves alternas implementadas con `UNIQUE`:
- `rm_site.rm_sitecode`
- `rm_customer.rm_customercode`
- `rm_customerplant.rm_plantcode`
- `rm_productmodel.rm_modelcode`
- `rm_material.rm_partnumber`
- `rm_asset.rm_serialnumber`
- `rm_repairticket (rm_intakesite_id, rm_sapnoticenumber)`
- `rm_siteconfiguration.rm_site_id`
- `rm_employeeprofile.rm_user_id`

Choices (valores almacenados como texto):
- Ticket status: `Received`, `Diagnosis`, `WaitingParts`, `RepairInProgress`, `Testing`, `ReadyToShip`, `Shipped`, `Closed`, `Cancelled`
- Priority: `Low`, `Medium`, `High`, `Critical`
- Asset status: `InCustomer`, `InTransit`, `InRepair`, `InStock`, `Scrap`
- Movement type: `InterSiteTransfer`, `InterSiteReturn`, `ShipmentToCustomer`, `ReceiptFromCustomer`

## 2) Model-driven app (simulada en UI)
Sitemap (barra lateral):
- Tickets
- Assets
- Customers
- Master Data
- Reports (Dashboard)
- Admin

Vistas:
- Tickets (filtros por status/sede)
- Assets
- Customers + Plants
- Master Data (sites, models, materials)
- Admin Site Configuration

Formulario Ticket con tabs:
- Intake
- Diagnosis
- Repair (subgrid Materials)
- Logistics (subgrid Movements)
- History (subgrid Status History)

Command bar simulada:
- Transfer to Site
- Add Material
- Mark Shipped
- Close Ticket

## 3) Flows (implementados en backend CI)
Flow 1 - Log Ticket Status Changes:
- Trigger: POST `tickets/{id}/status`
- Crea registro en `rm_ticketstatushistory` con from/to/changedby/changedat/comment.

Flow 2 - Apply Site Defaults on Ticket Create:
- Trigger: POST `tickets`
- Si intake site vacio: usa `rm_employeeprofile.rm_homesite`
- Lee `rm_siteconfiguration` y aplica defaults de status/priority si vienen vacios.
- Si current processing vacio: usa intake site.

Flow 3 - Create Asset Movement on Transfer:
- Trigger: POST `tickets/{id}/transfer`
- Actualiza `rm_currentprocessingsite_id`
- Inserta `rm_assetmovement`
- Actualiza `rm_asset.rm_currentlocationsite_id`
- Inserta historial de estado.

Reglas de negocio backend:
- `replacementgiven = true` exige `replacementasset`
- Al cerrar ticket, si site config exige solucion, `repairsln` es obligatorio.

## 4) Despliegue
1. Levantar contenedores:
- `docker compose up -d --build`

2. Ejecutar migraciones y seed:
- `docker compose exec php php spark migrate`
- `docker compose exec php php spark db:seed RepairManagementSeeder`

3. Entrar en la app:
- `http://localhost:8006`

4. phpMyAdmin (opcional):
- `http://localhost:8080`

## 5) Datos de ejemplo
CSVs en `docs/seeds/`:
- `sites.csv`
- `customers.csv`
- `customerplants.csv`
- `models.csv`
- `materials.csv`
- `assets.csv`
- `tickets.csv`

## Notas Power Platform CLI (referencia futura)
- `pac auth create ...`
- `pac solution init --publisher-name RepairManagement --publisher-prefix rm`
- `pac solution add-reference ...`
- `pac solution export ...`
- `pac solution import ...`
