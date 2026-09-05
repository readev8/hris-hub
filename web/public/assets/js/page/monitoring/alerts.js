/** Monitoring Hub — polling & alerts extension.
 * Depends: jQuery, site_url, window.Monitoring (main_page.js), toastr wrappers.
 * @file public/assets/js/page/monitoring/alerts.js
 */
(function () {
  if (typeof window.Monitoring === 'undefined') return;

  /** FIFO promise queue: 1 request aktif. Cegah tabrakan konkurensi + session-lock serialize. */
  Monitoring.queue = {
    tail: Promise.resolve(),
    add(fn) {
      const run = () => Promise.resolve().then(fn).catch((e) => MonLog.error('queue item fail', e && e.status));
      Monitoring.queue.tail = Monitoring.queue.tail.then(run, run);
      return Monitoring.queue.tail;
    }
  };

  Monitoring.poll = {
    start() {
      Monitoring.poll.stop();
      Monitoring.state.poll.timer = setInterval(() => {
        if (document.hidden || !Monitoring.state.poll.on) return;
        Monitoring.queue.add(() => Monitoring.api.refreshStats(Monitoring.state.start, Monitoring.state.end)
          .done((res) => {
            Monitoring.state.stats = res.stats || {};
            Monitoring.ui.renderDomainChart(Monitoring.state.stats);
            Monitoring.ui.renderDeltas(Monitoring.state.stats);
            Monitoring.ui.renderApprovalBreakdown((Monitoring.state.stats.by_status) || {});
          })
          .fail((xhr) => {
            if (xhr && xhr.status === 401) { Monitoring.poll.stop(); location.href = site_url + '/login'; }
            else window.MonLog?.error('poll refresh fail', xhr.status);
          }));
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
  };

  Monitoring.alerts = {    seen: {},
    check() {
      if (document.hidden) return;
      Monitoring.queue.add(() => Monitoring.api.loadAlerts()
        .done((res) => {
          const items = res.items || [];
          window.MonLog?.debug('alerts check', items.length);
          const badge = document.getElementById('monAlertCount');
          if (badge) {
            badge.hidden = !items.length;
            badge.textContent = String(res.total || items.length);
          }
          items.forEach((a) => {
            const key = a.table + '|' + a.message;
            if (Monitoring.alerts.seen[key]) return;
            Monitoring.alerts.seen[key] = true;
            if (window.toastr_warning) window.toastr_warning(a.message, 'Monitoring Alert');
            else if (window.toastr_error) window.toastr_error(a.message);
          });
        })
        .fail((xhr) => { window.MonLog?.error('alerts check fail', xhr.status); }));
    },
    start() {
      Monitoring.alerts.check();
      setInterval(() => Monitoring.alerts.check(), 300000);
    }
  };

  Monitoring.presets = {
    key: 'monitoring_presets',
    all() {
      try { return JSON.parse(localStorage.getItem(Monitoring.presets.key) || '[]'); }
      catch (e) { return []; }
    },
    saveAll(list) {
      try { localStorage.setItem(Monitoring.presets.key, JSON.stringify(list.slice(0, 5))); } catch (e) { /* abaikan */ }
    },
    current() {
      return {
        start_date: document.getElementById('filter-start')?.value || '',
        end_date: document.getElementById('filter-end')?.value || '',
        domain: document.querySelector('.domain-tab.active')?.dataset.domain || '',
        table: Monitoring.state.activeTable
      };
    },
    apply(p) {
      const url = site_url + '/monitoring?start_date=' + encodeURIComponent(p.start_date)
        + '&end_date=' + encodeURIComponent(p.end_date)
        + '&domain=' + encodeURIComponent(p.domain) + '&table=' + encodeURIComponent(p.table);
      location.href = url;
    },
    render() {
      const sel = document.getElementById('preset-select');
      if (!sel) return;
      sel.querySelectorAll('option[data-preset]').forEach((o) => o.remove());
      Monitoring.presets.all().forEach((p, i) => {
        const o = document.createElement('option');
        o.value = String(i); o.dataset.preset = '1'; o.textContent = p.name;
        sel.appendChild(o);
      });
    },
    init() {
      Monitoring.presets.render();
      $(document).on('change', '#preset-select', function () {
        const i = parseInt($(this).val(), 10);
        if (isNaN(i)) return;
        const p = Monitoring.presets.all()[i];
        if (p) Monitoring.presets.apply(p);
        $(this).val('');
      });
      $(document).on('click', '#btn-preset-save', () => {
        const name = window.prompt('Nama preset:');
        if (!name) return;
        const list = Monitoring.presets.all();
        list.push(Object.assign({ name: name.slice(0, 40) }, Monitoring.presets.current()));
        Monitoring.presets.saveAll(list);
        Monitoring.presets.render();
      });
      // deep-link: ?domain=&table= → aktifkan tab terkait saat init
      const qs = new URLSearchParams(location.search);
      const domain = qs.get('domain');
      const table = qs.get('table');
      if (domain) {
        const tab = document.querySelector('.domain-tab[data-domain="' + domain.replace(/[^a-z_]/g, '') + '"]');
        if (tab) {
          document.querySelectorAll('.domain-tab').forEach((t) => t.classList.remove('active'));
          tab.classList.add('active');
          Monitoring.state.activeTable = tab.dataset.table;
          Monitoring.ui.renderSubtabs(domain);
        }
      }
      if (table && /^[a-z_]+$/.test(table)) {
        Monitoring.state.activeTable = table;
        const sub = document.querySelector('.domain-subtab[data-table="' + table + '"]');
        if (sub) {
          document.querySelectorAll('.domain-subtab').forEach((t) => t.classList.remove('active'));
          sub.classList.add('active');
        }
        Monitoring.state.grid.skip = 0;
        Monitoring.loadActiveTable();
      }
    }
  };
})();
