/** Monitoring Hub — constants → state → api → ui → events → init.
 * @file public/assets/js/page/monitoring/main_page.js
 */
const MonLog = {
  on: !!(window.PageData && window.PageData.debug === true),
  debug(...a) { if (MonLog.on && window.console && console.debug) console.debug('[Monitoring]', ...a); },
  error(...a) { if (window.console && console.error) console.error('[Monitoring]', ...a); }
};
window.MonLog = MonLog;

const Monitoring = {
  constants: {
    ENDPOINTS: {
      STATS: site_url + '/monitoring/ajax-stats',
      LIST: site_url + '/monitoring/ajax-list',
      TREND: site_url + '/monitoring/ajax-trend',
      EXPORT: site_url + '/monitoring/export'
    },
    TIMEOUT: 15000,
    COLORS: ['#1E40AF', '#3B82F6', '#D97706', '#DC2626', '#059669', '#7C3AED', '#0891B2', '#BE123C', '#4D7C0F', '#0F766E']
  },

  state: { stats: {}, start: null, end: null, charts: {}, poll: { on: true, ms: 60000, timer: null } },
  _sessionTable: null,
  _activityTable: null,

  api: {
    refreshStats(start, end) {
      return $.ajax({ url: Monitoring.constants.ENDPOINTS.STATS, method: 'GET', data: { start_date: start, end_date: end }, timeout: Monitoring.constants.TIMEOUT });
    },
    loadTable(table, start, end, take) {
      return $.ajax({ url: Monitoring.constants.ENDPOINTS.LIST, method: 'GET', data: { table: table, start_date: start, end_date: end, take: take || 20 }, timeout: Monitoring.constants.TIMEOUT });
    },
    loadTrend(table, days, end) {
      return $.ajax({ url: Monitoring.constants.ENDPOINTS.TREND, method: 'GET', data: { table: table, days: days || 14, end_date: end }, timeout: Monitoring.constants.TIMEOUT });
    },
    loadDetail(table, id) {
      return $.ajax({ url: site_url + '/monitoring/ajax-detail', method: 'GET', data: { table: table, id: id }, timeout: Monitoring.constants.TIMEOUT });
    },
    loadAlerts() {
      return $.ajax({ url: site_url + '/monitoring/ajax-alerts', method: 'GET', timeout: Monitoring.constants.TIMEOUT });
    }
  },

  ui: {
    destroyChart(key) {
      if (Monitoring.state.charts[key]) { Monitoring.state.charts[key].destroy(); delete Monitoring.state.charts[key]; }
    },
    renderDomainChart(stats) {
      const el = document.getElementById('monStatusChart');
      if (!el || typeof Chart === 'undefined') return;
      const labels = ['Sesi', 'Assignment', 'FPKT', 'Ninebox', 'Ijin', 'Resign', 'Panel/SS', 'Rekrutmen', 'SK', 'Surat'];
      const keys = ['sessions', 'assignment', 'fpkt', 'ninebox', 'ijin', 'resign', 'panel_ss', 'rekrutmen', 'sk', 'surat'];
      const data = keys.map((k) => Object.values(stats[k] || {}).reduce((a, b) => a + (parseInt(b, 10) || 0), 0));
      Monitoring.ui.destroyChart('domain');
      Monitoring.state.charts.domain = new Chart(el, {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: data, backgroundColor: Monitoring.constants.COLORS }] },
        options: { responsive: true, plugins: { legend: { position: 'right' } } }
      });
    },
    renderDeltas(stats) {
      const prev = stats.previous || {};
      const keys = ['sessions', 'assignment', 'fpkt', 'ninebox', 'ijin', 'resign', 'panel_ss', 'rekrutmen', 'sk', 'surat'];
      keys.forEach((k) => {
        const el = document.getElementById('delta-' + k);
        if (!el) return;
        const cur = Object.values(stats[k] || {}).reduce((a, b) => a + (parseInt(b, 10) || 0), 0);
        const p = parseInt(prev[k], 10) || 0;
        if (!p) { el.textContent = ''; return; }
        const pct = Math.round(((cur - p) / p) * 100);
        el.textContent = (pct >= 0 ? '▲ +' : '▼ ') + pct + '% vs periode lalu';
        el.style.color = pct >= 0 ? '#059669' : '#DC2626';
        el.setAttribute('aria-label', 'Perubahan ' + pct + ' persen dibanding periode lalu');
      });
    },
    renderApprovalTracking(byStatus, lastDoc) {
      const box = document.getElementById('approval-tracking');
      if (!box) return;
      box.textContent = '';
      const labels = { assignment_approve: 'Assignment', pengajuan_ijin_approve: 'Ijin', pengajuan_resign_approve: 'Resign', w_fpk_approve: 'FPK', w_ppmj_approve: 'PPMJ' };
      const palette = ['#059669', '#D97706', '#3B82F6', '#7C3AED', '#DC2626'];
      let idx = 0;
      Object.keys(labels).forEach((k) => {
        const counts = byStatus && byStatus[k];
        const doc = lastDoc && lastDoc[k];
        const hasCounts = counts && Object.keys(counts).length > 0;
        if (!hasCounts && !doc) return;
        const card = document.createElement('div');
        card.className = 'appr-card stagger-in';
        card.style.animationDelay = (idx * 0.04) + 's';
        const h = document.createElement('h4'); h.textContent = labels[k];
        card.appendChild(h);
        if (hasCounts) {
          const total = Object.values(counts).reduce((a, b) => a + (parseInt(b, 10) || 0), 0);
          const bar = document.createElement('div'); bar.className = 'appr-bar';
          const legend = document.createElement('div'); legend.className = 'appr-legend';
          let ci = 0;
          Object.entries(counts).forEach(([st, n]) => {
            const pct = total ? Math.round((parseInt(n, 10) / total) * 100) : 0;
            const seg = document.createElement('div');
            seg.className = 'appr-seg'; seg.style.width = pct + '%'; seg.style.background = palette[ci % palette.length];
            seg.title = 'Status ' + st + ': ' + n + ' (' + pct + '%)';
            bar.appendChild(seg);
            const lg = document.createElement('span');
            lg.textContent = 'Status ' + st + ': ' + n + ' (' + pct + '%)'; lg.style.color = palette[ci % palette.length];
            legend.appendChild(lg);
            ci++;
          });
          card.appendChild(bar); card.appendChild(legend);
        }
        if (doc && doc.id) {
          const btn = document.createElement('button');
          btn.className = 'appr-last';
          btn.dataset.table = doc.table; btn.dataset.pk = doc.id;
          const ago = Monitoring.ui.relTime(doc.date);
          btn.textContent = 'Ref ' + (doc.ref || '-') + ' • ' + (doc.date ? doc.date.slice(0, 16) : '-') + (ago ? ' • ' + ago : '') + ' • Status ' + (doc.status || '-');
          btn.addEventListener('click', () => Monitoring.showDetail(doc.table, doc.id));
          btn.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); Monitoring.showDetail(doc.table, doc.id); } });
          card.appendChild(btn);
        } else {
          const p = document.createElement('p'); p.className = 'text-muted'; p.style.fontSize = '12px'; p.textContent = 'Belum ada dokumen.';
          card.appendChild(p);
        }
        box.appendChild(card);
        idx++;
      });
      if (!box.children.length) box.innerHTML = '<p class="text-muted" style="font-size:12px">Belum ada data.</p>';
    },
    renderMainTrend(items) {
      const el = document.getElementById('monTrendChart');
      const alt = document.getElementById('monTrendAlt');
      if (!el || typeof Chart === 'undefined') return;
      const rows = items || [];
      const labels = rows.map((r) => String(r.date || '').slice(5));
      Monitoring.ui.destroyChart('mainTrend');
      Monitoring.state.charts.mainTrend = new Chart(el, {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Sesi per hari', data: rows.map((r) => r.count || 0), borderColor: '#1E40AF', fill: false, tension: 0.3 }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
      });
      if (alt) {
        const total = rows.reduce((a, r) => a + (parseInt(r.count, 10) || 0), 0);
        alt.textContent = rows.length ? ('Total ' + total + ' sesi dalam ' + rows.length + ' hari.') : 'Belum ada data tren pada periode ini.';
      }
    },
    renderSessionTrend(items) {
      const el = document.getElementById('sessTrendChart');
      if (!el || typeof Chart === 'undefined') return;
      const perDay = {};
      (items || []).forEach((r) => { const d = String(r.start || '').slice(0, 10); if (d) perDay[d] = (perDay[d] || 0) + 1; });
      const labels = Object.keys(perDay).sort().slice(-14);
      Monitoring.ui.destroyChart('sessTrend');
      Monitoring.state.charts.sessTrend = new Chart(el, {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Sesi', data: labels.map((d) => perDay[d]), borderColor: '#1E40AF', fill: false }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
      });
    },
    renderSessionApps(items) {
      const el = document.getElementById('sessAppsChart');
      if (!el || typeof Chart === 'undefined') return;
      const perApp = {};
      (items || []).forEach((r) => { String(r.apps || '').split(',').forEach((a) => { a = a.trim(); if (a) perApp[a] = (perApp[a] || 0) + 1; }); });
      const labels = Object.keys(perApp);
      Monitoring.ui.destroyChart('sessApps');
      Monitoring.state.charts.sessApps = new Chart(el, {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: labels.map((a) => perApp[a]), backgroundColor: Monitoring.constants.COLORS }] },
        options: { responsive: true, plugins: { legend: { position: 'right' } } }
      });
    },
    relTime(iso) {
      if (!iso) return '';
      const t = new Date(String(iso).replace(' ', 'T')).getTime();
      if (isNaN(t)) return String(iso).slice(0, 16);
      const s = Math.max(0, Math.floor((Date.now() - t) / 1000));
      if (s < 60) return 'baru saja';
      if (s < 3600) return Math.floor(s / 60) + ' mnt lalu';
      if (s < 86400) return Math.floor(s / 3600) + ' jam lalu';
      return Math.floor(s / 86400) + ' hari lalu';
    },
    renderActivity(items) {
      const data = items || [];
      const $table = $('#grid-activity');
      if (Monitoring._activityTable) {
        Monitoring._activityTable.clear();
        if (data.length) Monitoring._activityTable.rows.add(data).draw();
        else Monitoring._activityTable.draw();
        return;
      }
      Monitoring._activityTable = $table.DataTable({
        data: data,
        pageLength: 10,
        lengthChange: false,
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({ header: function () { return 'Detail Aktivitas'; } }),
            renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
          }
        },
        columns: [
          {
            data: 'date',
            title: 'Waktu',
            render: function (d) {
              if (!d) return '-';
              var rel = Monitoring.ui.relTime(d);
              return '<div>' + String(d).slice(0, 16) + '</div><small class="text-muted">' + rel + '</small>';
            }
          },
          {
            data: 'table_label',
            title: 'Jenis',
            render: function (d, type, row) { return d || row.table || '-'; }
          },
          {
            data: 'user',
            title: 'User',
            render: function (d) { return d && d !== '-' ? '<span class="fw-medium">' + $('<div>').text(d).html() + '</span>' : '<span class="text-muted">-</span>'; }
          },
          {
            data: 'title',
            title: 'Keterangan',
            render: function (d, type, row) {
              var title = d ? '<div style="font-weight:600">' + $('<div>').text(d).html() + '</div>' : '';
              var detail = row.detail ? '<small class="text-muted">' + $('<div>').text(row.detail).html() + '</small>' : '';
              return title + detail || row.table || '-';
            }
          }
        ],
        order: [[0, 'desc']],
        language: {
          emptyTable: '<div class="sap-empty" style="padding:32px 10px"><i class="fas fa-history"></i><h4>Belum ada aktivitas</h4><p>Tidak ada aktivitas pada periode ini.</p></div>',
          search: 'Cari:',
          info: 'Baris _START_–_END_ dari _TOTAL_',
          infoEmpty: 'Tidak ada data',
          paginate: { previous: '‹ Prev', next: 'Next ›' }
        },
        dom: '<"row mb-2"<"col-sm-6"f><"col-sm-6 text-end"l>>rt<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
        createdRow: function (row, data) {
          $(row).addClass('row-clickable').attr('data-table', data.table || '').attr('data-pk', data.id || '');
        }
      });
      // Delegated click for detail modal
      $table.off('click', 'tbody tr[data-table]').on('click', 'tbody tr[data-table]', function () {
        var d = Monitoring._activityTable.row(this).data();
        if (d && d.table && d.id) Monitoring.showDetail(d.table, d.id);
      });
    },
    renderSession(items, withCharts) {
      const data = items || [];
      // Update KPIs & charts only on first page load
      if (withCharts) {
        const users = {};
        let today = 0;
        const todayStr = new Date().toISOString().slice(0, 10);
        data.forEach((r) => {
          users[r.userid] = true;
          if (String(r.start || '').slice(0, 10) === todayStr) today++;
        });
        const tEl = document.getElementById('sess-today');
        const uEl = document.getElementById('sess-users');
        if (tEl) tEl.textContent = String(today);
        if (uEl) uEl.textContent = String(Object.keys(users).length);
        Monitoring.ui.renderSessionTrend(data);
        Monitoring.ui.renderSessionApps(data);
      }
      const $table = $('#grid-session');
      if (Monitoring._sessionTable) {
        Monitoring._sessionTable.clear();
        if (data.length) Monitoring._sessionTable.rows.add(data).draw();
        else Monitoring._sessionTable.draw();
        return;
      }
      Monitoring._sessionTable = $table.DataTable({
        data: data,
        pageLength: 10,
        lengthChange: false,
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({ header: function () { return 'Detail Sesi'; } }),
            renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
          }
        },
        columns: [
          { data: 'userid', title: 'UserID', render: function (d) { return d ? '<span class="fw-medium">' + $('<div>').text(d).html() + '</span>' : '-'; } },
          { data: 'ip', title: 'IP', defaultContent: '-', render: function (d) { return d ? $('<div>').text(d).html() : '<span class="text-muted">-</span>'; } },
          {
            data: 'apps',
            title: 'Apps',
            render: function (d) {
              if (!d) return '<span class="text-muted">-</span>';
              var badges = String(d).split(',').map(function (a) { return a.trim(); }).filter(Boolean).map(function (a) { return '<span class="sap-badge me-1">' + $('<div>').text(a).html() + '</span>'; }).join('');
              return badges || '-';
            }
          },
          {
            data: 'start',
            title: 'Start',
            render: function (d) {
              if (!d) return '-';
              var rel = Monitoring.ui.relTime(d);
              return '<div>' + $('<div>').text(String(d).slice(0, 16)).html() + '</div><small class="text-muted">' + rel + '</small>';
            }
          }
        ],
        order: [[3, 'desc']],
        language: {
          emptyTable: '<div class="sap-empty" style="padding:32px 10px"><i class="fas fa-sign-in-alt"></i><h4>Belum ada sesi</h4><p>Tidak ada sesi pada periode ini.</p></div>',
          search: 'Cari:',
          info: 'Baris _START_–_END_ dari _TOTAL_',
          infoEmpty: 'Tidak ada data',
          paginate: { previous: '‹ Prev', next: 'Next ›' }
        },
        dom: '<"row mb-2"<"col-sm-6"f><"col-sm-6 text-end"l>>rt<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>'
      });
    },
  },

  events: {
    bind() {
      $(document).on('click', '#btn-filter', () => {
        location.href = site_url + '/monitoring?start_date=' + $('#filter-start').val() + '&end_date=' + $('#filter-end').val();
      });
      $(document).on('click', '#refreshToggle', () => { Monitoring.poll.toggle(); });
      const exportTable = () => document.getElementById('export-table')?.value || 'session';
      const exportUrl = (fmt) => Monitoring.constants.ENDPOINTS.EXPORT + '?table=' + exportTable()
        + '&start_date=' + $('#filter-start').val() + '&end_date=' + $('#filter-end').val() + (fmt ? '&format=' + fmt : '');
      $(document).on('click', '#btn-export', () => { location.href = exportUrl(''); });
      $(document).on('click', '#btn-export-xlsx', () => { location.href = exportUrl('xlsx'); });
      $(document).on('click', '#monAlertBell', () => {
        document.getElementById('section-activity')?.scrollIntoView({ behavior: 'smooth' });
      });
      $(document).on('change', '#activity-domain', () => { Monitoring.queue.add(() => Monitoring.loadActivity()); });
      // DataTables built-in pagination handles paging; no manual pager buttons
    }
  },


  showDetail(table, pk) {
    if (!pk || !table) return;
    const tb = document.querySelector('#mon-detail-table tbody');
    if (tb) tb.innerHTML = '<tr><td class="text-center text-muted">Memuat…</td></tr>';
    const modalEl = document.getElementById('monDetailModal');
    if (modalEl && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    this.api.loadDetail(table, pk)
      .done((res) => {
        if (!tb) return;
        tb.textContent = '';
        const row = res.row || {};
        const keys = Object.keys(row);
        if (!keys.length) {
          tb.innerHTML = '<tr><td class="text-center text-muted">Data tidak ditemukan.</td></tr>';
          return;
        }
        keys.forEach((k) => {
          const tr = document.createElement('tr');
          const th = document.createElement('th'); th.textContent = k; th.style.width = '35%';
          const td = document.createElement('td');
          const v = row[k];
          td.textContent = v == null ? '' : String(v);
          tr.appendChild(th); tr.appendChild(td); tb.appendChild(tr);
        });
      })
      .fail(() => {
        if (tb) tb.innerHTML = '<tr><td class="text-center text-muted">Gagal memuat detail.</td></tr>';
        if (window.toastr_error) window.toastr_error('Gagal memuat detail');
      });
  },

  loadSession() {
    // Fetch 100 last sessions once; DataTables paginates 10/page client-side
    return Monitoring.api.loadTable('session', Monitoring.state.start, Monitoring.state.end, 100, 0)
      .done((res) => {
        const items = res.items || [];
        MonLog.debug('session loaded', items.length);
        Monitoring.ui.renderSession(items, true);
      })
      .fail((xhr) => { MonLog.error('session load fail', xhr.status); });
  },

  loadActivity() {
    return $.ajax({ url: site_url + '/monitoring/ajax-activity', method: 'GET', data: { limit: 100, domain: document.getElementById('activity-domain')?.value || '', start_date: Monitoring.state.start, end_date: Monitoring.state.end }, timeout: Monitoring.constants.TIMEOUT })
      .done((res) => { MonLog.debug('activity loaded', (res.items || []).length); Monitoring.ui.renderActivity(res.items || []); })
      .fail((xhr) => { MonLog.error('activity load fail', xhr.status); });
  },

  init(pageData) {
    const pd = pageData || {};
    this.state.stats = pd.stats || {};
    this.state.start = pd.startDate || null;
    this.state.end = pd.endDate || null;
    MonLog.debug('init', { start: this.state.start, end: this.state.end, debug: MonLog.on });
    this.ui.renderDomainChart(this.state.stats);
    this.ui.renderDeltas(this.state.stats);
    this.ui.renderApprovalTracking(this.state.stats.by_status || {}, this.state.stats.last_doc || {});
    this.events.bind();
    Monitoring.queue.add(() => Monitoring.loadSession());
    Monitoring.queue.add(() => Monitoring.api.loadTrend('session', 14, Monitoring.state.end)
      .done((res) => { MonLog.debug('main trend loaded', (res.items || []).length); Monitoring.ui.renderMainTrend(res.items || []); })
      .fail((xhr) => { MonLog.error('main trend fail', xhr.status); }));
    Monitoring.queue.add(() => Monitoring.loadActivity());
    this.poll.start();
    this.alerts.start();
    if (this.presets) this.presets.init();
  }
};

$(() => Monitoring.init(window.PageData || { stats: {}, startDate: null, endDate: null }));
window.Monitoring = Monitoring;
