/** Monitoring Hub — polling & alerts extension.
 * Depends: jQuery, site_url, window.Monitoring (main_page.js), toastr wrappers.
 * @file public/assets/js/page/monitoring/alerts.js
 */
(function () {
  if (typeof window.Monitoring === 'undefined') return;

  Monitoring.poll = {
    start() {
      Monitoring.poll.stop();
      Monitoring.state.poll.timer = setInterval(() => {
        if (document.hidden || !Monitoring.state.poll.on) return;
        Monitoring.api.refreshStats(Monitoring.state.start, Monitoring.state.end)
          .done((res) => {
            Monitoring.state.stats = res.stats || {};
            Monitoring.ui.renderDomainChart(Monitoring.state.stats);
            Monitoring.ui.renderDeltas(Monitoring.state.stats);
            Monitoring.ui.renderApprovalBreakdown((Monitoring.state.stats.by_status) || {});
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
  };

  Monitoring.alerts = {
    seen: {},
    check() {
      if (document.hidden) return;
      Monitoring.api.loadAlerts()
        .done((res) => {
          const items = res.items || [];
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
        .fail((xhr) => { if (window.console && console.error) console.error(xhr); });
    },
    start() {
      Monitoring.alerts.check();
      setInterval(() => Monitoring.alerts.check(), 300000);
    }
  };
})();
