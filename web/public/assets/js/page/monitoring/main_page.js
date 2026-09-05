/** Monitoring Hub — constants → state → api → ui → events → init.
 * @file public/assets/js/page/monitoring/main_page.js
 */
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

  state: { stats: {}, start: null, end: null, charts: {}, activeTable: 'assignment', poll: { on: true, ms: 60000, timer: null } },

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
    renderSession(items) {
      const tb = document.querySelector('#grid-session tbody');
      if (!tb) return;
      tb.textContent = '';
      if (!items || !items.length) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 4; td.className = 'text-center text-muted'; td.textContent = 'Belum ada sesi pada periode ini.';
        tr.appendChild(td); tb.appendChild(tr); return;
      }
      const users = {};
      let today = 0;
      const todayStr = new Date().toISOString().slice(0, 10);
      items.forEach((r) => {
        users[r.userid] = true;
        if (String(r.start || '').slice(0, 10) === todayStr) today++;
        const tr = document.createElement('tr');
        [r.userid, r.ip, r.apps, r.start].forEach((v) => {
          const td = document.createElement('td'); td.textContent = v == null ? '' : String(v); tr.appendChild(td);
        });
        tb.appendChild(tr);
      });
      const tEl = document.getElementById('sess-today');
      const uEl = document.getElementById('sess-users');
      if (tEl) tEl.textContent = String(today);
      if (uEl) uEl.textContent = String(Object.keys(users).length);
      Monitoring.ui.renderSessionTrend(items);
      Monitoring.ui.renderSessionApps(items);
    },
    renderDomainTable(items) {
      const head = document.getElementById('grid-domain-head');
      const body = document.getElementById('grid-domain-body');
      if (!head || !body) return;
      head.textContent = ''; body.textContent = '';
      if (!items || !items.length) {
        const th = document.createElement('th'); th.textContent = 'Data'; head.appendChild(th);
        const tr = document.createElement('tr');
        const td = document.createElement('td'); td.className = 'text-center text-muted'; td.textContent = 'Belum ada data pada periode ini.';
        tr.appendChild(td); body.appendChild(tr); return;
      }
      const cols = Object.keys(items[0]).slice(0, 6);
      cols.forEach((c) => { const th = document.createElement('th'); th.textContent = c; head.appendChild(th); });
      items.forEach((r) => {
        const tr = document.createElement('tr');
        tr.className = 'row-clickable';
        const pk = r.id ?? r.Id ?? r.ID ?? '';
        tr.dataset.pk = String(pk);
        cols.forEach((c) => {
          const td = document.createElement('td');
          let v = r[c];
          if (typeof v === 'string' && v.length > 60) v = v.slice(0, 60) + '…';
          td.textContent = v == null ? '' : String(v); tr.appendChild(td);
        });
        body.appendChild(tr);
      });
    },
    renderSubtabs(domain) {
      const box = document.getElementById('domain-subtabs');
      if (!box) return;
      box.textContent = '';
      ((window.DomainTabs || {})[domain] || []).forEach((t, i) => {
        const b = document.createElement('button');
        b.className = 'domain-subtab' + (i === 0 ? ' active' : '');
        b.textContent = t[0]; b.dataset.table = t[1];
        box.appendChild(b);
      });
    }
  },

  events: {
    bind() {
      $(document).on('click', '#btn-filter', () => {
        location.href = site_url + '/monitoring?start_date=' + $('#filter-start').val() + '&end_date=' + $('#filter-end').val();
      });
      $(document).on('click', '#refreshToggle', () => { Monitoring.poll.toggle(); });
      $(document).on('click', '#btn-export', () => {
        location.href = Monitoring.constants.ENDPOINTS.EXPORT + '?table=' + Monitoring.state.activeTable
          + '&start_date=' + $('#filter-start').val() + '&end_date=' + $('#filter-end').val();
      });
      $(document).on('click', '.domain-tab', function () {
        $('.domain-tab').removeClass('active');
        $(this).addClass('active');
        Monitoring.state.activeTable = $(this).data('table');
        Monitoring.ui.renderSubtabs($(this).data('domain'));
        Monitoring.loadActiveTable();
      });
      $(document).on('click', '.domain-subtab', function () {
        $('.domain-subtab').removeClass('active');
        $(this).addClass('active');
        Monitoring.state.activeTable = $(this).data('table');
        Monitoring.loadActiveTable();
      });
      $(document).on('click', '#grid-domain tbody tr.row-clickable', function () {
        Monitoring.showDetail($(this).data('pk'));
      });
    }
  },

  poll: {
    start() {
      Monitoring.poll.stop();
      Monitoring.state.poll.timer = setInterval(() => {
        if (document.hidden || !Monitoring.state.poll.on) return;
        Monitoring.api.refreshStats(Monitoring.state.start, Monitoring.state.end)
          .done((res) => {
            Monitoring.state.stats = res.stats || {};
            Monitoring.ui.renderDomainChart(Monitoring.state.stats);
          })
          .fail((xhr) => {
            if (xhr && xhr.status === 401) { Monitoring.poll.stop(); location.href = site_url + '/login'; }
            else if (window.console && console.error) console.error(xhr);
          });
      }, Monitoring.state.poll.ms);
    },
    stop() {
      if (Monitoring.state.poll.timer) { clearInterval(Monitoring.state.poll.timer); Monitoring.state.poll.timer = null; }
    },
    toggle() {
      Monitoring.state.poll.on = !Monitoring.state.poll.on;
      const btn = document.getElementById('refreshToggle');
      const label = document.getElementById('refreshLabel');
      if (btn) btn.setAttribute('aria-pressed', String(Monitoring.state.poll.on));
      if (label) label.textContent = Monitoring.state.poll.on ? 'Auto' : 'Off';
      if (Monitoring.state.poll.on) Monitoring.poll.start(); else Monitoring.poll.stop();
    }
  },

  showDetail(pk) {
    if (!pk) return;
    const tb = document.querySelector('#mon-detail-table tbody');
    if (tb) tb.innerHTML = '<tr><td class="text-center text-muted">Memuat…</td></tr>';
    const modalEl = document.getElementById('monDetailModal');
    if (modalEl && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    this.api.loadDetail(this.state.activeTable, pk)
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

  loadActiveTable() {
    const body = document.getElementById('grid-domain-body');
    if (body) body.innerHTML = '<tr><td class="text-center text-muted">Memuat…</td></tr>';
    this.api.loadTable(this.state.activeTable, this.state.start, this.state.end, 20)
      .done((res) => { Monitoring.ui.renderDomainTable(res.items || []); })
      .fail((xhr) => {
        if (window.toastr_error) window.toastr_error('Gagal memuat data domain');
        if (body) body.innerHTML = '<tr><td class="text-center text-muted">Gagal memuat data.</td></tr>';
        if (window.console && console.error) console.error(xhr);
      });
  },

  init(pageData) {
    const pd = pageData || {};
    this.state.stats = pd.stats || {};
    this.state.start = pd.startDate || null;
    this.state.end = pd.endDate || null;
    this.ui.renderDomainChart(this.state.stats);
    this.events.bind();
    const firstTab = document.querySelector('.domain-tab.active');
    if (firstTab) {
      this.state.activeTable = firstTab.dataset.table;
      this.ui.renderSubtabs(firstTab.dataset.domain);
      this.loadActiveTable();
    }
    this.api.loadTable('session', this.state.start, this.state.end, 100)
      .done((res) => { Monitoring.ui.renderSession(res.items || []); })
      .fail((xhr) => { if (window.console && console.error) console.error(xhr); });
    this.api.loadTrend('session', 14, this.state.end)
      .done((res) => { Monitoring.ui.renderMainTrend(res.items || []); })
      .fail((xhr) => { if (window.console && console.error) console.error(xhr); });
    this.poll.start();
  }
};

$(() => Monitoring.init(window.PageData || { stats: {}, startDate: null, endDate: null }));
window.Monitoring = Monitoring;
