<?php
/** @var array $ctx */
/** @var string $screen */

$labels = $ctx['labels'] ?? [];
$t = static function (string $key, string $fallback = '') use ($labels): string {
    return $labels[$key] ?? ($fallback !== '' ? $fallback : $key);
};

$permissions = $ctx['permissions'] ?? [];
$can = static function (string $resource, string $field) use ($permissions): bool {
    $wild = $permissions['*'] ?? [];
    if (($wild[$field] ?? 0) == 1) {
        return true;
    }

    return (($permissions[$resource][$field] ?? 0) == 1);
};

$user = $ctx['userEmail'] ?? 'unknown@local';
$lang = $ctx['lang'] ?? 'en';
$roles = implode(', ', $ctx['roles'] ?? []);

$ui = [
    'en' => [
        'nav.dashboard' => 'Dashboard',
        'nav.tickets' => 'Tickets',
        'nav.assets' => 'Assets',
        'nav.movements' => 'Movements',
        'nav.parts' => 'Parts',
        'nav.quality' => 'Quality',
        'nav.people' => 'People',
        'nav.settings_user' => 'Settings User',
        'nav.settings_system' => 'Settings System',
        'nav.home' => 'Home',
        'nav.move' => 'Move',
        'nav.user' => 'User',
        'search.placeholder' => 'Ticket Number / SAP Notice / Serial',
        'dashboard.open_tickets' => 'Open Tickets',
        'dashboard.sla_risk_24h' => 'SLA at Risk (24h)',
        'dashboard.active_sites' => 'Active Sites',
        'dashboard.sla_in_time' => 'SLA In Time',
        'dashboard.sla_out' => 'SLA Out',
        'dashboard.sla_risk' => 'SLA Risk',
        'dashboard.tickets_by_status' => 'Tickets by Status',
        'tickets.title' => 'Tickets List',
        'tickets.global_search' => 'Global Search',
        'field.status' => 'Status',
        'field.priority' => 'Priority',
        'field.site' => 'Site',
        'field.ticket' => 'Ticket',
        'field.serial' => 'Serial',
        'field.comment' => 'Comment',
        'field.reason' => 'Reason',
        'field.notes' => 'Notes',
        'field.type' => 'Type',
        'field.date' => 'Date',
        'field.reference' => 'Reference',
        'field.active' => 'Active',
        'field.name' => 'Name',
        'field.email' => 'Email',
        'field.phone' => 'Phone',
        'detail.not_found' => 'Ticket not found or out of scope.',
        'detail.change_status' => 'Change Status',
        'detail.new_status' => 'New Status',
        'detail.repair_solution' => 'Repair Solution',
        'detail.nonrepairable' => 'Non Repairable',
        'detail.nonrepairable_reason' => 'Non Repairable Reason',
        'detail.tech_close_ready' => 'Technical Closure Ready',
        'detail.admin_close_done' => 'Administrative Closure Done',
        'btn.apply' => 'Apply',
        'detail.transfer_site' => 'Transfer Site',
        'detail.to_site' => 'To Site',
        'btn.transfer' => 'Transfer',
        'detail.return_required' => 'Return Required',
        'detail.return_status' => 'Return Status',
        'btn.return_out' => 'Register Return Out',
        'btn.return_in' => 'Register Return In',
        'detail.create_exchange' => 'Create Exchange',
        'field.customer_plant' => 'Customer Plant',
        'field.incoming_asset' => 'Incoming Asset',
        'field.replacement_asset' => 'Replacement Asset',
        'field.approved_by' => 'Approved By',
        'field.exchange_doc' => 'Exchange Doc (path or reference)',
        'timeline.title' => 'Timeline',
        'ticket_create.title' => 'Create Ticket',
        'field.site_in' => 'Site In',
        'field.sap_notice' => 'SAP Notice',
        'field.account_plant' => 'Account/Plant',
        'field.asset' => 'Asset',
        'field.default_by_site' => 'Default by site',
        'field.reported_failure' => 'Reported Failure',
        'assets.title' => 'Assets List',
        'field.code' => 'Code',
        'field.model' => 'Model',
        'asset.not_found' => 'Asset not found.',
        'asset.title' => 'Asset 360',
        'movements.title' => 'Movements',
        'movements.create' => 'Create Movement',
        'field.from_site' => 'From Site',
        'field.to_site' => 'To Site',
        'parts.title' => 'Parts Requests',
        'field.part' => 'Part',
        'field.qty' => 'Qty',
        'field.requested_on' => 'Requested On',
        'exchange.create' => 'Create Exchange',
        'quality.nonconformities' => 'Non Conformities',
        'field.closed_on' => 'Closed On',
        'people.admin' => 'People Admin',
        'settings.user' => 'Settings User',
        'settings.language_mode' => 'Language Mode',
        'settings.preferred_language' => 'Preferred Language',
        'settings.default_site' => 'Default Site',
        'settings.system' => 'Settings System',
        'settings.visible_for' => 'Visible for Admin/Quality/Coordination.',
        'settings.sites_policies' => 'Sites + Policies',
        'settings.first_response' => 'First Response',
        'settings.repair_target' => 'Repair Target',
        'settings.closure' => 'Closure',
        'settings.translations' => 'Translations (first 100)',
        'search.title' => 'Search',
        'bool.no' => 'No',
        'bool.yes' => 'Yes',
        'common.none' => '(none)',
    ],
    'es' => [
        'nav.dashboard' => 'Tablero',
        'nav.tickets' => 'Tickets',
        'nav.assets' => 'Activos',
        'nav.movements' => 'Movimientos',
        'nav.parts' => 'Partes',
        'nav.quality' => 'Calidad',
        'nav.people' => 'Personas',
        'nav.settings_user' => 'Ajustes Usuario',
        'nav.settings_system' => 'Ajustes Sistema',
        'nav.home' => 'Inicio',
        'nav.move' => 'Mover',
        'nav.user' => 'Usuario',
        'search.placeholder' => 'Numero Ticket / Aviso SAP / Serie',
        'dashboard.open_tickets' => 'Tickets Abiertos',
        'dashboard.sla_risk_24h' => 'SLA en Riesgo (24h)',
        'dashboard.active_sites' => 'Sitios Activos',
        'dashboard.sla_in_time' => 'SLA en Tiempo',
        'dashboard.sla_out' => 'SLA Vencido',
        'dashboard.sla_risk' => 'SLA Riesgo',
        'dashboard.tickets_by_status' => 'Tickets por Estado',
        'tickets.title' => 'Listado de Tickets',
        'tickets.global_search' => 'Busqueda Global',
        'field.status' => 'Estado',
        'field.priority' => 'Prioridad',
        'field.site' => 'Sitio',
        'field.ticket' => 'Ticket',
        'field.serial' => 'Serie',
        'field.comment' => 'Comentario',
        'field.reason' => 'Motivo',
        'field.notes' => 'Notas',
        'field.type' => 'Tipo',
        'field.date' => 'Fecha',
        'field.reference' => 'Referencia',
        'field.active' => 'Activo',
        'field.name' => 'Nombre',
        'field.email' => 'Correo',
        'field.phone' => 'Telefono',
        'detail.not_found' => 'Ticket no encontrado o fuera de alcance.',
        'detail.change_status' => 'Cambiar Estado',
        'detail.new_status' => 'Nuevo Estado',
        'detail.repair_solution' => 'Solucion Reparacion',
        'detail.nonrepairable' => 'No Reparables',
        'detail.nonrepairable_reason' => 'Motivo No Reparables',
        'detail.tech_close_ready' => 'Cierre Tecnico Listo',
        'detail.admin_close_done' => 'Cierre Administrativo Hecho',
        'btn.apply' => 'Aplicar',
        'detail.transfer_site' => 'Transferir Sitio',
        'detail.to_site' => 'Al Sitio',
        'btn.transfer' => 'Transferir',
        'detail.return_required' => 'Devolucion Requerida',
        'detail.return_status' => 'Estado Devolucion',
        'btn.return_out' => 'Registrar Salida Devolucion',
        'btn.return_in' => 'Registrar Entrada Devolucion',
        'detail.create_exchange' => 'Crear Intercambio',
        'field.customer_plant' => 'Cliente/Planta',
        'field.incoming_asset' => 'Activo Entrante',
        'field.replacement_asset' => 'Activo Reemplazo',
        'field.approved_by' => 'Aprobado Por',
        'field.exchange_doc' => 'Doc Intercambio (ruta o referencia)',
        'timeline.title' => 'Linea de Tiempo',
        'ticket_create.title' => 'Crear Ticket',
        'field.site_in' => 'Sitio Ingreso',
        'field.sap_notice' => 'Aviso SAP',
        'field.account_plant' => 'Cuenta/Planta',
        'field.asset' => 'Activo',
        'field.default_by_site' => 'Por defecto por sitio',
        'field.reported_failure' => 'Falla Reportada',
        'assets.title' => 'Listado de Activos',
        'field.code' => 'Codigo',
        'field.model' => 'Modelo',
        'asset.not_found' => 'Activo no encontrado.',
        'asset.title' => 'Activo 360',
        'movements.title' => 'Movimientos',
        'movements.create' => 'Crear Movimiento',
        'field.from_site' => 'Desde Sitio',
        'field.to_site' => 'Hacia Sitio',
        'parts.title' => 'Solicitudes de Partes',
        'field.part' => 'Parte',
        'field.qty' => 'Cant.',
        'field.requested_on' => 'Solicitado el',
        'exchange.create' => 'Crear Intercambio',
        'quality.nonconformities' => 'No Conformidades',
        'field.closed_on' => 'Cerrado el',
        'people.admin' => 'Admin Personas',
        'settings.user' => 'Ajustes Usuario',
        'settings.language_mode' => 'Modo Idioma',
        'settings.preferred_language' => 'Idioma Preferido',
        'settings.default_site' => 'Sitio por Defecto',
        'settings.system' => 'Ajustes Sistema',
        'settings.visible_for' => 'Visible para Admin/Calidad/Coordinacion.',
        'settings.sites_policies' => 'Sitios + Politicas',
        'settings.first_response' => 'Primera Respuesta',
        'settings.repair_target' => 'Objetivo Reparacion',
        'settings.closure' => 'Cierre',
        'settings.translations' => 'Traducciones (primeras 100)',
        'search.title' => 'Buscar',
        'bool.no' => 'No',
        'bool.yes' => 'Si',
        'common.none' => '(ninguno)',
    ],
    'pt' => [
        'nav.dashboard' => 'Painel',
        'nav.tickets' => 'Chamados',
        'nav.assets' => 'Ativos',
        'nav.movements' => 'Movimentos',
        'nav.parts' => 'Pecas',
        'nav.quality' => 'Qualidade',
        'nav.people' => 'Pessoas',
        'nav.settings_user' => 'Config Usuario',
        'nav.settings_system' => 'Config Sistema',
        'nav.home' => 'Inicio',
        'nav.move' => 'Mover',
        'nav.user' => 'Usuario',
    ],
    'it' => [
        'nav.dashboard' => 'Dashboard',
        'nav.tickets' => 'Ticket',
        'nav.assets' => 'Asset',
        'nav.movements' => 'Movimenti',
        'nav.parts' => 'Parti',
        'nav.quality' => 'Qualita',
        'nav.people' => 'Persone',
        'nav.settings_user' => 'Impostazioni Utente',
        'nav.settings_system' => 'Impostazioni Sistema',
        'nav.home' => 'Home',
        'nav.move' => 'Muovi',
        'nav.user' => 'Utente',
    ],
];

$u = static function (string $key, string $fallback = '') use ($ui, $lang): string {
    $langSet = $ui[$lang] ?? $ui['en'];
    if (isset($langSet[$key])) {
        return $langSet[$key];
    }
    if (isset($ui['en'][$key])) {
        return $ui['en'][$key];
    }
    return $fallback !== '' ? $fallback : $key;
};

$statusLabels = [
    'Received' => ['es' => 'Recibido', 'pt' => 'Recebido', 'it' => 'Ricevuto', 'en' => 'Received'],
    'Diagnosis' => ['es' => 'Diagnostico', 'pt' => 'Diagnostico', 'it' => 'Diagnosi', 'en' => 'Diagnosis'],
    'WaitingParts' => ['es' => 'Esperando Partes', 'pt' => 'Aguardando Pecas', 'it' => 'In Attesa Parti', 'en' => 'Waiting Parts'],
    'RepairInProgress' => ['es' => 'Reparacion en Progreso', 'pt' => 'Reparo em Progresso', 'it' => 'Riparazione in Corso', 'en' => 'Repair In Progress'],
    'Testing' => ['es' => 'Pruebas', 'pt' => 'Teste', 'it' => 'Test', 'en' => 'Testing'],
    'ReadyToShip' => ['es' => 'Listo para Enviar', 'pt' => 'Pronto para Enviar', 'it' => 'Pronto per Spedire', 'en' => 'Ready To Ship'],
    'Shipped' => ['es' => 'Enviado', 'pt' => 'Enviado', 'it' => 'Spedito', 'en' => 'Shipped'],
    'Closed' => ['es' => 'Cerrado', 'pt' => 'Fechado', 'it' => 'Chiuso', 'en' => 'Closed'],
    'Cancelled' => ['es' => 'Cancelado', 'pt' => 'Cancelado', 'it' => 'Annullato', 'en' => 'Cancelled'],
];

$priorityLabels = [
    'Low' => ['es' => 'Baja', 'pt' => 'Baixa', 'it' => 'Bassa', 'en' => 'Low'],
    'High' => ['es' => 'Alta', 'pt' => 'Alta', 'it' => 'Alta', 'en' => 'High'],
    'Critical' => ['es' => 'Critica', 'pt' => 'Critica', 'it' => 'Critica', 'en' => 'Critical'],
];

$slaLabels = [
    'n/a' => ['es' => 'N/D', 'pt' => 'N/A', 'it' => 'N/D', 'en' => 'N/A'],
    'out' => ['es' => 'Fuera', 'pt' => 'Fora', 'it' => 'Fuori', 'en' => 'Out'],
    'risk' => ['es' => 'Riesgo', 'pt' => 'Risco', 'it' => 'Rischio', 'en' => 'Risk'],
    'in' => ['es' => 'En Tiempo', 'pt' => 'No Prazo', 'it' => 'In Tempo', 'en' => 'In Time'],
];

$movementTypeLabels = [
    'TransferOut' => ['es' => 'Salida Traslado', 'pt' => 'Transferencia Saida', 'it' => 'Trasferimento Uscita', 'en' => 'Transfer Out'],
    'TransferIn' => ['es' => 'Entrada Traslado', 'pt' => 'Transferencia Entrada', 'it' => 'Trasferimento Ingresso', 'en' => 'Transfer In'],
    'ReturnOut' => ['es' => 'Salida Devolucion', 'pt' => 'Devolucao Saida', 'it' => 'Reso Uscita', 'en' => 'Return Out'],
    'ReturnIn' => ['es' => 'Entrada Devolucion', 'pt' => 'Devolucao Entrada', 'it' => 'Reso Ingresso', 'en' => 'Return In'],
    'ShipToCustomer' => ['es' => 'Enviar a Cliente', 'pt' => 'Enviar ao Cliente', 'it' => 'Spedire al Cliente', 'en' => 'Ship To Customer'],
    'ReceiveFromCustomer' => ['es' => 'Recibir de Cliente', 'pt' => 'Receber do Cliente', 'it' => 'Ricevere dal Cliente', 'en' => 'Receive From Customer'],
];

$labelOf = static function (string $value, array $map, string $lang): string {
    if (! isset($map[$value])) {
        return $value;
    }

    return $map[$value][$lang] ?? $map[$value]['en'] ?? $value;
};
?>
<!doctype html>
<html lang="<?= esc($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repair Canvas App</title>
  <link rel="stylesheet" href="<?= base_url('assets/repair/app.css') ?>">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar">
    <div class="brand">Repair Canvas</div>
    <nav>
      <a href="<?= site_url('app/dashboard') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.dashboard')) ?></a>
      <a href="<?= site_url('app/tickets') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.tickets')) ?></a>
      <a href="<?= site_url('app/assets') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.assets')) ?></a>
      <a href="<?= site_url('app/movements') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.movements')) ?></a>
      <a href="<?= site_url('app/parts-requests') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.parts')) ?></a>
      <a href="<?= site_url('app/nonconformities') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.quality')) ?></a>
      <a href="<?= site_url('app/people') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.people')) ?></a>
      <a href="<?= site_url('app/settings/user') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.settings_user')) ?></a>
      <a href="<?= site_url('app/settings/system') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.settings_system')) ?></a>
    </nav>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div>
        <strong><?= esc($u('field.status')) ?></strong>
        <span class="meta"> | <?= esc($roles) ?></span>
      </div>
      <form action="<?= site_url('app/search') ?>" method="get" class="search-form">
        <input type="hidden" name="user" value="<?= esc($user) ?>">
        <input type="text" name="q" placeholder="<?= esc($u('search.placeholder')) ?>">
        <button type="submit"><?= esc($u('search.title')) ?></button>
      </form>
    </header>

    <section class="mobile-nav">
      <a href="<?= site_url('app/dashboard') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.home')) ?></a>
      <a href="<?= site_url('app/tickets') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.tickets')) ?></a>
      <a href="<?= site_url('app/assets') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.assets')) ?></a>
      <a href="<?= site_url('app/movements') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.move')) ?></a>
      <a href="<?= site_url('app/settings/user') ?>?user=<?= esc($user) ?>"><?= esc($u('nav.user')) ?></a>
    </section>

    <?php if ($screen === 'dashboard'): ?>
      <section class="grid cards-3">
        <article class="card kpi"><h3><?= esc($u('dashboard.open_tickets')) ?></h3><p><?= esc((string) $kpis['openTickets']) ?></p></article>
        <article class="card kpi"><h3><?= esc($u('dashboard.sla_risk_24h')) ?></h3><p><?= esc((string) $kpis['riskSla']) ?></p></article>
        <article class="card kpi"><h3><?= esc($u('dashboard.active_sites')) ?></h3><p><?= esc((string) $kpis['sitesWithOpenTickets']) ?></p></article>
      </section>
      <section class="grid cards-3">
        <article class="card kpi"><h3><?= esc($u('dashboard.sla_in_time')) ?></h3><p><?= esc((string) $kpis['inSla']) ?></p></article>
        <article class="card kpi"><h3><?= esc($u('dashboard.sla_out')) ?></h3><p><?= esc((string) $kpis['outSla']) ?></p></article>
        <article class="card kpi"><h3><?= esc($u('dashboard.sla_risk')) ?></h3><p><?= esc((string) $kpis['riskSla']) ?></p></article>
      </section>
      <section class="card">
        <h3><?= esc($u('dashboard.tickets_by_status')) ?></h3>
        <div class="badges">
          <?php foreach ($statusCards as $item): ?>
            <span class="badge"><?= esc($labelOf((string) $item['cr_status'], $statusLabels, $lang)) ?>: <?= esc((string) $item['total']) ?></span>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'tickets_list'): ?>
      <section class="card">
        <h3><?= esc($u('tickets.title')) ?></h3>
        <form method="get" class="filters">
          <input type="hidden" name="user" value="<?= esc($user) ?>">
          <input type="text" name="q" value="<?= esc($filters['q']) ?>" placeholder="<?= esc($u('tickets.global_search')) ?>">
          <select name="status">
            <option value=""><?= esc($u('field.status')) ?></option>
            <?php foreach (['Received','Diagnosis','WaitingParts','RepairInProgress','Testing','ReadyToShip','Shipped','Closed','Cancelled'] as $st): ?>
              <option value="<?= esc($st) ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= esc($labelOf($st, $statusLabels, $lang)) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="priority">
            <option value=""><?= esc($u('field.priority')) ?></option>
            <?php foreach (['Low','High','Critical'] as $pr): ?>
              <option value="<?= esc($pr) ?>" <?= $filters['priority'] === $pr ? 'selected' : '' ?>><?= esc($labelOf($pr, $priorityLabels, $lang)) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="site">
            <option value=""><?= esc($u('field.site')) ?></option>
            <?php foreach ($sites as $site): ?>
              <option value="<?= esc((string) $site['id']) ?>" <?= $filters['site'] === (string) $site['id'] ? 'selected' : '' ?>><?= esc($site['cr_sitecode']) ?></option>
            <?php endforeach; ?>
          </select>
          <button><?= esc($u('search.title')) ?></button>
        </form>

        <div class="table-wrap">
          <table>
            <thead><tr><th><?= esc($u('field.ticket')) ?></th><th>SAP</th><th><?= esc($u('field.serial')) ?></th><th><?= esc($u('field.status')) ?></th><th><?= esc($u('field.priority')) ?></th><th>SLA</th><th><?= esc($u('field.site')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $row): ?>
              <tr>
                <td><a href="<?= site_url('app/ticket/' . $row['id']) ?>?user=<?= esc($user) ?>"><?= esc($row['cr_ticketnumber']) ?></a></td>
                <td><?= esc($row['cr_sapnotice']) ?></td>
                <td><?= esc((string) $row['cr_serialnumber']) ?></td>
                <td><span class="badge"><?= esc($labelOf((string) $row['cr_status'], $statusLabels, $lang)) ?></span></td>
                <td><?= esc($labelOf((string) $row['cr_priority'], $priorityLabels, $lang)) ?></td>
                <td><span class="badge"><?= esc($labelOf((string) $row['cr_sla_status'], $slaLabels, $lang)) ?></span></td>
                <td><?= esc((string) $row['cr_sitecode']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'ticket_detail'): ?>
      <section class="card">
        <?php if (! $ticket): ?>
          <p><?= esc($u('detail.not_found')) ?></p>
        <?php else: ?>
          <?php if (! empty($message)): ?><p class="ok"><?= esc($message) ?></p><?php endif; ?>
          <?php if (! empty($error)): ?><p class="error"><?= esc($error) ?></p><?php endif; ?>
          <h3><?= esc($ticket['cr_ticketnumber']) ?> <span class="badge"><?= esc($labelOf((string) $ticket['cr_status'], $statusLabels, $lang)) ?></span></h3>
          <p><?= esc((string) $ticket['cr_reportedfailure']) ?></p>
          <div class="grid cards-3">
            <?php if ($can('cr_ticket', 'cr_can_change_status')): ?>
              <form method="post" action="<?= site_url('app/ticket/' . $ticket['id'] . '/change-status') ?>?user=<?= esc($user) ?>" class="card">
                <h4><?= esc($u('detail.change_status')) ?></h4>
                <label><?= esc($u('detail.new_status')) ?>
                  <select name="new_status">
                    <?php foreach ($statusOptions as $opt): ?>
                      <option value="<?= esc($opt) ?>"><?= esc($labelOf($opt, $statusLabels, $lang)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <label><?= esc($u('field.comment')) ?><textarea name="comment" rows="2"></textarea></label>
                <label><?= esc($u('detail.repair_solution')) ?><textarea name="cr_repairsolution" rows="2"><?= esc((string) $ticket['cr_repairsolution']) ?></textarea></label>
                <label><?= esc($u('detail.nonrepairable')) ?>
                  <select name="cr_nonrepairable"><option value="0"><?= esc($u('bool.no')) ?></option><option value="1" <?= (int) $ticket['cr_nonrepairable'] === 1 ? 'selected' : '' ?>><?= esc($u('bool.yes')) ?></option></select>
                </label>
                <label><?= esc($u('detail.nonrepairable_reason')) ?><input name="cr_cancelreason" value="<?= esc((string) $ticket['cr_cancelreason']) ?>"></label>
                <label><?= esc($u('detail.tech_close_ready')) ?><select name="cr_technicalclosureready"><option value="0">0</option><option value="1" <?= (int) $ticket['cr_technicalclosureready'] === 1 ? 'selected' : '' ?>>1</option></select></label>
                <label><?= esc($u('detail.admin_close_done')) ?><select name="cr_administrativeclosuredone"><option value="0">0</option><option value="1" <?= (int) $ticket['cr_administrativeclosuredone'] === 1 ? 'selected' : '' ?>>1</option></select></label>
                <button type="submit"><?= esc($u('btn.apply')) ?></button>
              </form>
            <?php endif; ?>

            <form method="post" action="<?= site_url('app/ticket/' . $ticket['id'] . '/transfer-site') ?>?user=<?= esc($user) ?>" class="card">
              <h4><?= esc($u('detail.transfer_site')) ?></h4>
              <label><?= esc($u('detail.to_site')) ?>
                <select name="to_site_id"><?php foreach ($sites as $site): ?><option value="<?= esc((string) $site['id']) ?>"><?= esc($site['cr_sitecode']) ?></option><?php endforeach; ?></select>
              </label>
              <label><?= esc($u('field.reason')) ?><textarea name="reason" rows="2"></textarea></label>
              <button type="submit"><?= esc($u('btn.transfer')) ?></button>
            </form>

            <div class="card">
              <h4><?= esc($u('detail.return_required')) ?></h4>
              <p><?= esc($u('detail.return_status')) ?>: <?= esc((string) $ticket['cr_returnstatus']) ?></p>
              <form method="post" action="<?= site_url('app/ticket/' . $ticket['id'] . '/return-out') ?>?user=<?= esc($user) ?>">
                <label><?= esc($u('field.notes')) ?><textarea name="notes" rows="2"></textarea></label>
                <button type="submit"><?= esc($u('btn.return_out')) ?></button>
              </form>
              <form method="post" action="<?= site_url('app/ticket/' . $ticket['id'] . '/return-in') ?>?user=<?= esc($user) ?>">
                <label><?= esc($u('field.notes')) ?><textarea name="notes" rows="2"></textarea></label>
                <button type="submit"><?= esc($u('btn.return_in')) ?></button>
              </form>
            </div>
          </div>

          <form method="post" action="<?= site_url('app/ticket/' . $ticket['id'] . '/exchange') ?>?user=<?= esc($user) ?>" class="card form-grid">
            <h4 class="full"><?= esc($u('detail.create_exchange')) ?></h4>
            <label><?= esc($u('field.customer_plant')) ?><select name="cr_accountplant_id"><?php foreach ($accountPlants as $ap): ?><option value="<?= esc((string) $ap['id']) ?>"><?= esc($ap['cr_name']) ?></option><?php endforeach; ?></select></label>
            <label><?= esc($u('field.incoming_asset')) ?><select name="cr_incomingasset_id"><?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?></select></label>
            <label><?= esc($u('field.replacement_asset')) ?><select name="cr_replacementasset_id"><?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?></select></label>
            <label><?= esc($u('field.approved_by')) ?><input name="cr_approvedby" value="<?= esc($user) ?>"></label>
            <label class="full"><?= esc($u('field.reason')) ?><textarea name="cr_reason" rows="2"></textarea></label>
            <label class="full"><?= esc($u('field.exchange_doc')) ?><input name="cr_document" placeholder="exchange-doc.pdf"></label>
            <button type="submit"><?= esc($u('detail.create_exchange')) ?></button>
          </form>

          <h4><?= esc($u('timeline.title')) ?></h4>
          <ul class="timeline">
            <?php foreach ($timeline as $ev): ?>
              <li><strong><?= esc((string) $ev['event_type']) ?></strong> - <?= esc((string) $ev['event_date']) ?> - <?= esc((string) $ev['event_text']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'ticket_create'): ?>
      <section class="card">
        <h3><?= esc($u('ticket_create.title')) ?></h3>
        <?php if (! empty($message)): ?><p class="ok"><?= esc($message) ?></p><?php endif; ?>
        <?php if (! empty($error)): ?><p class="error"><?= esc($error) ?></p><?php endif; ?>
        <form method="post" class="form-grid">
          <label><?= esc($u('field.site_in')) ?>
            <select name="cr_sitein_id" required>
              <?php foreach ($sites as $site): ?><option value="<?= esc((string) $site['id']) ?>"><?= esc($site['cr_sitecode']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <label><?= esc($u('field.sap_notice')) ?><input name="cr_sapnotice" required></label>
          <label><?= esc($u('field.account_plant')) ?>
            <select name="cr_accountplant_id" required>
              <?php foreach ($accountPlants as $ap): ?><option value="<?= esc((string) $ap['id']) ?>"><?= esc($ap['cr_name']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <label><?= esc($u('field.asset')) ?>
            <select name="cr_asset_id" required>
              <?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <label><?= esc($u('field.priority')) ?>
            <select name="cr_priority">
              <option value=""><?= esc($u('field.default_by_site')) ?></option>
              <option value="Low"><?= esc($labelOf('Low', $priorityLabels, $lang)) ?></option><option value="High"><?= esc($labelOf('High', $priorityLabels, $lang)) ?></option><option value="Critical"><?= esc($labelOf('Critical', $priorityLabels, $lang)) ?></option>
            </select>
          </label>
          <label class="full"><?= esc($u('field.reported_failure')) ?><textarea name="cr_reportedfailure" rows="3"></textarea></label>
          <button><?= esc($t('BTN_SAVE', 'Save')) ?></button>
        </form>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'assets_list'): ?>
      <section class="card"><h3><?= esc($u('assets.title')) ?></h3><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.code')) ?></th><th><?= esc($u('field.serial')) ?></th><th><?= esc($u('field.model')) ?></th><th><?= esc($u('field.status')) ?></th><th><?= esc($u('field.site')) ?></th></tr></thead><tbody><?php foreach ($assets as $row): ?><tr><td><a href="<?= site_url('app/asset/' . $row['id']) ?>?user=<?= esc($user) ?>"><?= esc((string) $row['cr_assetcode']) ?></a></td><td><?= esc($row['cr_serialnumber']) ?></td><td><?= esc((string) $row['cr_model']) ?></td><td><?= esc($row['cr_assetstatus']) ?></td><td><?= esc((string) $row['cr_sitecode']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'asset_360'): ?>
      <section class="card">
        <?php if (! $asset): ?><p><?= esc($u('asset.not_found')) ?></p><?php else: ?>
          <h3><?= esc($u('asset.title')) ?> - <?= esc($asset['cr_serialnumber']) ?></h3>
          <p><?= esc($u('field.model')) ?>: <?= esc((string) $asset['cr_model']) ?> | <?= esc($u('field.status')) ?>: <?= esc($asset['cr_assetstatus']) ?></p>
          <h4><?= esc($u('nav.tickets')) ?></h4>
          <ul><?php foreach ($tickets as $item): ?><li><?= esc($item['cr_ticketnumber']) ?> (<?= esc($labelOf((string) $item['cr_status'], $statusLabels, $lang)) ?>)</li><?php endforeach; ?></ul>
          <h4><?= esc($u('movements.title')) ?></h4>
          <ul><?php foreach ($movements as $item): ?><li><?= esc($labelOf((string) $item['cr_movementtype'], $movementTypeLabels, $lang)) ?> - <?= esc($item['cr_datetime']) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'movements_list'): ?>
      <section class="card"><h3><?= esc($u('movements.title')) ?></h3><a class="btn-inline" href="<?= site_url('app/movements/create') ?>?user=<?= esc($user) ?>"><?= esc($u('movements.create')) ?></a><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.type')) ?></th><th><?= esc($u('field.date')) ?></th><th><?= esc($u('field.reference')) ?></th><th><?= esc($u('field.ticket')) ?></th><th><?= esc($u('field.serial')) ?></th></tr></thead><tbody><?php foreach ($movements as $row): ?><tr><td><?= esc($labelOf((string) $row['cr_movementtype'], $movementTypeLabels, $lang)) ?></td><td><?= esc($row['cr_datetime']) ?></td><td><?= esc((string) $row['cr_reference']) ?></td><td><?= esc((string) $row['cr_ticketnumber']) ?></td><td><?= esc((string) $row['cr_serialnumber']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'movement_create'): ?>
      <section class="card"><h3><?= esc($u('movements.create')) ?></h3><?php if (! empty($message)): ?><p class="ok"><?= esc($message) ?></p><?php endif; ?><?php if (! empty($error)): ?><p class="error"><?= esc($error) ?></p><?php endif; ?><form method="post" class="form-grid"><label><?= esc($u('field.ticket')) ?><select name="cr_ticket_id"><option value=""><?= esc($u('common.none')) ?></option><?php foreach ($tickets as $ticket): ?><option value="<?= esc((string) $ticket['id']) ?>"><?= esc($ticket['cr_ticketnumber']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.asset')) ?><select name="cr_asset_id" required><?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.type')) ?><select name="cr_movementtype"><?php foreach (['TransferOut','TransferIn','ReturnOut','ReturnIn','ShipToCustomer','ReceiveFromCustomer'] as $mv): ?><option value="<?= esc($mv) ?>"><?= esc($labelOf($mv, $movementTypeLabels, $lang)) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.from_site')) ?><select name="cr_fromsite_id"><option value=""><?= esc($u('common.none')) ?></option><?php foreach ($sites as $site): ?><option value="<?= esc((string) $site['id']) ?>"><?= esc($site['cr_sitecode']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.to_site')) ?><select name="cr_tosite_id"><option value=""><?= esc($u('common.none')) ?></option><?php foreach ($sites as $site): ?><option value="<?= esc((string) $site['id']) ?>"><?= esc($site['cr_sitecode']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.reference')) ?><input name="cr_reference"></label><label class="full"><?= esc($u('field.notes')) ?><textarea name="cr_notes" rows="3"></textarea></label><button><?= esc($t('BTN_SAVE', 'Save')) ?></button></form></section>
    <?php endif; ?>

    <?php if ($screen === 'parts_requests_list'): ?>
      <section class="card"><h3><?= esc($u('parts.title')) ?></h3><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.ticket')) ?></th><th><?= esc($u('field.part')) ?></th><th><?= esc($u('field.qty')) ?></th><th><?= esc($u('field.status')) ?></th><th><?= esc($u('field.requested_on')) ?></th></tr></thead><tbody><?php foreach ($parts as $row): ?><tr><td><?= esc((string) $row['cr_ticketnumber']) ?></td><td><?= esc($row['cr_partname']) ?></td><td><?= esc((string) $row['cr_quantity']) ?></td><td><?= esc($row['cr_status']) ?></td><td><?= esc((string) $row['cr_requestedon']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'exchange_create'): ?>
      <section class="card"><h3><?= esc($u('exchange.create')) ?></h3><?php if (! empty($message)): ?><p class="ok"><?= esc($message) ?></p><?php endif; ?><?php if (! empty($error)): ?><p class="error"><?= esc($error) ?></p><?php endif; ?><form method="post" class="form-grid"><label><?= esc($u('field.ticket')) ?><select name="cr_ticket_id"><?php foreach ($tickets as $ticket): ?><option value="<?= esc((string) $ticket['id']) ?>"><?= esc($ticket['cr_ticketnumber']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.account_plant')) ?><select name="cr_accountplant_id"><?php foreach ($accountPlants as $ap): ?><option value="<?= esc((string) $ap['id']) ?>"><?= esc($ap['cr_name']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.incoming_asset')) ?><select name="cr_incomingasset_id"><?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?></select></label><label><?= esc($u('field.replacement_asset')) ?><select name="cr_replacementasset_id"><?php foreach ($assets as $asset): ?><option value="<?= esc((string) $asset['id']) ?>"><?= esc($asset['cr_serialnumber']) ?></option><?php endforeach; ?></select></label><label class="full"><?= esc($u('field.reason')) ?><textarea name="cr_reason" rows="3"></textarea></label><button><?= esc($t('BTN_SAVE', 'Save')) ?></button></form></section>
    <?php endif; ?>

    <?php if ($screen === 'nonconformities_list'): ?>
      <section class="card"><h3><?= esc($u('quality.nonconformities')) ?></h3><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.ticket')) ?></th><th><?= esc($u('field.type')) ?></th><th><?= esc($u('field.status')) ?></th><th><?= esc($u('field.closed_on')) ?></th></tr></thead><tbody><?php foreach ($items as $row): ?><tr><td><?= esc((string) $row['cr_ticketnumber']) ?></td><td><?= esc($row['cr_type']) ?></td><td><?= esc($row['cr_status']) ?></td><td><?= esc((string) $row['cr_closedon']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'people_admin'): ?>
      <section class="card"><h3><?= esc($u('people.admin')) ?></h3><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.name')) ?></th><th><?= esc($u('field.email')) ?></th><th><?= esc($u('field.phone')) ?></th><th><?= esc($u('field.active')) ?></th></tr></thead><tbody><?php foreach ($people as $row): ?><tr><td><?= esc($row['cr_fullname']) ?></td><td><?= esc($row['cr_email']) ?></td><td><?= esc((string) $row['cr_phone']) ?></td><td><?= esc((string) $row['cr_isactive']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'settings_user'): ?>
      <section class="card">
        <h3><?= esc($u('settings.user')) ?></h3>
        <?php if (! empty($message)): ?><p class="ok"><?= esc($message) ?></p><?php endif; ?>
        <form method="post" class="form-grid">
          <label><?= esc($u('settings.language_mode')) ?>
            <select name="cr_languagemode">
              <option value="Auto" <?= ($ctx['settings']['cr_languagemode'] ?? '') === 'Auto' ? 'selected' : '' ?>>Auto</option>
              <option value="Manual" <?= ($ctx['settings']['cr_languagemode'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
            </select>
          </label>
          <label><?= esc($u('settings.preferred_language')) ?>
            <select name="cr_preferredlanguage">
              <?php foreach (['es', 'pt', 'it', 'en'] as $langCode): ?>
                <option value="<?= esc($langCode) ?>" <?= ($ctx['settings']['cr_preferredlanguage'] ?? '') === $langCode ? 'selected' : '' ?>><?= esc($langCode) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label><?= esc($u('settings.default_site')) ?>
            <select name="cr_defaultsite_id">
              <option value=""><?= esc($u('common.none')) ?></option>
              <?php foreach ($sites as $site): ?><option value="<?= esc((string) $site['id']) ?>" <?= (string) ($ctx['settings']['cr_defaultsite_id'] ?? '') === (string) $site['id'] ? 'selected' : '' ?>><?= esc($site['cr_sitecode']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <button><?= esc($t('BTN_SAVE', 'Save')) ?></button>
        </form>
      </section>
    <?php endif; ?>

    <?php if ($screen === 'settings_system'): ?>
      <section class="card"><h3><?= esc($u('settings.system')) ?></h3><p><?= esc($u('settings.visible_for')) ?></p><h4><?= esc($u('settings.sites_policies')) ?></h4><ul><?php foreach ($sites as $site): ?><li><?= esc($site['cr_sitecode']) ?> - <?= esc($site['cr_name']) ?></li><?php endforeach; ?></ul><h4>SLA</h4><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.priority')) ?></th><th><?= esc($u('settings.first_response')) ?></th><th><?= esc($u('settings.repair_target')) ?></th><th><?= esc($u('settings.closure')) ?></th></tr></thead><tbody><?php foreach ($sla as $row): ?><tr><td><?= esc($labelOf((string) $row['cr_priority'], $priorityLabels, $lang)) ?></td><td><?= esc((string) $row['cr_firstresponse_hours']) ?></td><td><?= esc((string) $row['cr_repairtarget_hours']) ?></td><td><?= esc((string) $row['cr_closuretarget_hours']) ?></td></tr><?php endforeach; ?></tbody></table></div><h4><?= esc($u('settings.translations')) ?></h4><div class="table-wrap"><table><thead><tr><th>Key</th><th>ES</th><th>PT</th><th>IT</th><th>EN</th></tr></thead><tbody><?php foreach ($translations as $row): ?><tr><td><?= esc($row['cr_key']) ?></td><td><?= esc($row['cr_text_es']) ?></td><td><?= esc($row['cr_text_pt']) ?></td><td><?= esc($row['cr_text_it']) ?></td><td><?= esc($row['cr_text_en']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>

    <?php if ($screen === 'search'): ?>
      <section class="card"><h3><?= esc($u('search.title')) ?>: <?= esc($q) ?></h3><div class="table-wrap"><table><thead><tr><th><?= esc($u('field.ticket')) ?></th><th>SAP</th><th><?= esc($u('field.serial')) ?></th><th><?= esc($u('field.status')) ?></th></tr></thead><tbody><?php foreach ($results as $row): ?><tr><td><a href="<?= site_url('app/ticket/' . $row['id']) ?>?user=<?= esc($user) ?>"><?= esc($row['cr_ticketnumber']) ?></a></td><td><?= esc($row['cr_sapnotice']) ?></td><td><?= esc((string) $row['cr_serialnumber']) ?></td><td><?= esc($labelOf((string) $row['cr_status'], $statusLabels, $lang)) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <?php endif; ?>
  </main>
</div>
<script src="<?= base_url('assets/repair/app.js') ?>"></script>
</body>
</html>
