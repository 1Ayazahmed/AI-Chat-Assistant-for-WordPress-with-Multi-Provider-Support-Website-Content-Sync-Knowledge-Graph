(function ($) {
  'use strict';

  const API = ceacAdmin.apiUrl;
  const NONCE = ceacAdmin.nonce;

  function apiRequest(endpoint, method, data) {
    return $.ajax({
      url: API + endpoint,
      method: method || 'GET',
      headers: { 'X-WP-Nonce': NONCE },
      contentType: 'application/json',
      data: data ? JSON.stringify(data) : undefined
    });
  }

  function showNotice(msg, type) {
    const notice = $('<div class="ceac-notice ceac-notice-' + type + '">' + msg + '</div>');
    $('.ceac-admin-wrap .ceac-card').first().before(notice);
    setTimeout(() => notice.fadeOut(300, () => notice.remove()), 4000);
  }

  function serializeForm($form) {
    const data = {};
    $form.find('[name]').each(function () {
      const $el = $(this);
      const name = $el.attr('name');
      if ($el.attr('type') === 'checkbox') {
        data[name] = $el.is(':checked') ? true : false;
      } else {
        data[name] = $el.val();
      }
    });
    return data;
  }

  // Tab switching
  $('.ceac-tab').on('click', function () {
    const $tab = $(this);
    const target = $tab.data('tab');
    const $container = $tab.closest('.ceac-card');
    $container.find('.ceac-tab').removeClass('active');
    $tab.addClass('active');
    $container.find('.ceac-tab-content').removeClass('active');
    $('#' + target).addClass('active');
  });

  // Provider form
  $('#ceac-provider-form').on('submit', function (e) {
    e.preventDefault();
    const data = serializeForm($(this));
    const manualModel = $('#ceac-model-manual').val();
    if (manualModel) data.model = manualModel;

    apiRequest('/settings', 'POST', data).done(() => {
      showNotice(ceacAdmin.i18n.saved, 'success');
    }).fail(() => showNotice(ceacAdmin.i18n.error, 'error'));
  });

  $('#ceac-widget-form, #ceac-settings-form').on('submit', function (e) {
    e.preventDefault();
    apiRequest('/settings', 'POST', serializeForm($(this))).done(() => {
      showNotice(ceacAdmin.i18n.saved, 'success');
    }).fail(() => showNotice(ceacAdmin.i18n.error, 'error'));
  });

  $('#ceac-provider').on('change', function () {
    const url = $(this).find(':selected').data('url');
    if (url) $('#ceac-api-base-url').val(url);
  });

  $('#ceac-temperature').on('input', function () {
    $('#ceac-temperature-val').text($(this).val());
  });

  $('.ceac-toggle-key').on('click', function () {
    const target = $('#' + $(this).data('target'));
    const type = target.attr('type') === 'password' ? 'text' : 'password';
    target.attr('type', type);
    $(this).text(type === 'password' ? 'Reveal' : 'Hide');
  });

  // Fetch Models
  $('#ceac-fetch-models').on('click', function () {
    const $btn = $(this);
    const $status = $('#ceac-models-status');
    $btn.prop('disabled', true);
    $status.text(ceacAdmin.i18n.fetching_models).css('color', '#646970');

    const baseUrl = $('#ceac-api-base-url').val();
    const apiKey = $('#ceac-api-key').val();

    apiRequest('/models?base_url=' + encodeURIComponent(baseUrl) + '&api_key=' + encodeURIComponent(apiKey))
      .done(function (res) {
        const $select = $('#ceac-model');
        $select.empty();
        if (res.models && res.models.length > 0) {
          res.models.forEach(function (m) {
            $select.append($('<option>', { value: m.id, text: m.id + (m.owned_by ? ' (' + m.owned_by + ')' : '') }));
          });
          $status.text(ceacAdmin.i18n.models_fetched + ' (' + res.models.length + ' models)').css('color', '#22543d');
        } else {
          $status.text('No models found. Enter model name manually.').css('color', '#c05621');
        }
        if (!res.success && res.error) {
          $status.text('Warning: ' + res.error + ' — showing fallback list.').css('color', '#c05621');
        }
      })
      .fail(function () {
        $status.text(ceacAdmin.i18n.error).css('color', '#c53030');
      })
      .always(function () { $btn.prop('disabled', false); });
  });

  // Test Connection
  $('#ceac-test-connection').on('click', function () {
    const $status = $('#ceac-connection-status');
    $status.text(ceacAdmin.i18n.testing).css('color', '#646970');

    apiRequest('/test-connection', 'POST', {
      base_url: $('#ceac-api-base-url').val(),
      api_key: $('#ceac-api-key').val()
    }).done(function (res) {
      $status.text(res.message).css('color', res.success ? '#22543d' : '#c53030');
      if (res.success && res.models && res.models.length > 0) {
        const $select = $('#ceac-model');
        $select.empty();
        res.models.forEach(function (m) {
          $select.append($('<option>', { value: m.id, text: m.id }));
        });
      }
    }).fail(function () {
      $status.text(ceacAdmin.i18n.error).css('color', '#c53030');
    });
  });

  // Sync content
  $('#ceac-sync-content, #ceac-sync-and-refresh').on('click', function () {
    const $btn = $(this);
    $btn.prop('disabled', true).text(ceacAdmin.i18n.syncing);

    apiRequest('/sync-content', 'POST').done(function () {
      showNotice(ceacAdmin.i18n.sync_complete, 'success');
      if (typeof loadKnowledgeGraph === 'function') loadKnowledgeGraph();
      $('#ceac-last-sync').text(new Date().toLocaleString());
    }).fail(() => showNotice(ceacAdmin.i18n.error, 'error'))
      .always(() => $btn.prop('disabled', false).text('Sync Website Content'));
  });

  // Dashboard stats
  if ($('#ceac-stat-chats').length) {
    apiRequest('/analytics?days=30').done(function (stats) {
      $('#ceac-stat-chats').text(stats.total_chats);
      $('#ceac-stat-resolution').text(stats.resolution_rate + '%');
      $('#ceac-stat-fallback').text(stats.fallback_rate + '%');
      $('#ceac-stat-tokens').text(stats.total_tokens.toLocaleString());
    });
  }

  // Analytics page
  function loadAnalytics() {
    const days = $('#ceac-analytics-days').val() || 30;
    apiRequest('/analytics?days=' + days).done(function (stats) {
      const $grid = $('#ceac-analytics-stats');
      $grid.html(
        statCard('Total Chats', stats.total_chats, 'chart') +
        statCard('Messages', stats.total_messages, 'email') +
        statCard('Resolution Rate', stats.resolution_rate + '%', 'yes') +
        statCard('Fallback Rate', stats.fallback_rate + '%', 'warning') +
        statCard('Escalations', stats.escalated, 'phone') +
        statCard('Avg Length', stats.avg_length, 'text')
      );

      const $intents = $('#ceac-intents-chart').empty();
      if (stats.top_intents) {
        const max = Math.max(...stats.top_intents.map(i => parseInt(i.count)));
        stats.top_intents.forEach(function (intent) {
          const pct = max > 0 ? (intent.count / max * 100) : 0;
          $intents.append(
            '<div class="ceac-intent-bar">' +
            '<span class="ceac-intent-label">' + intent.intent + '</span>' +
            '<div class="ceac-intent-bar-fill" style="width:' + pct + '%"></div>' +
            '<span class="ceac-intent-count">' + intent.count + '</span></div>'
          );
        });
      }

      const $peak = $('#ceac-peak-hours-chart').empty();
      if (stats.peak_hours) {
        const maxH = Math.max(...stats.peak_hours.map(h => parseInt(h.count)));
        stats.peak_hours.forEach(function (h) {
          const pct = maxH > 0 ? (h.count / maxH * 100) : 0;
          $peak.append(
            '<div class="ceac-intent-bar">' +
            '<span class="ceac-intent-label">' + h.hour + ':00</span>' +
            '<div class="ceac-intent-bar-fill" style="width:' + pct + '%;background:#d69e2e"></div>' +
            '<span class="ceac-intent-count">' + h.count + '</span></div>'
          );
        });
      }

      const $tbody = $('#ceac-top-queries tbody').empty();
      if (stats.top_queries) {
        stats.top_queries.forEach(function (q) {
          $tbody.append('<tr><td>' + $('<span>').text(q.content).html() + '</td><td><span class="ceac-badge ceac-badge-info">' + q.count + '</span></td></tr>');
        });
      }

      $('#ceac-cost-info').html(
        '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:12px">' +
        '<div><strong>Total Tokens:</strong><br><span style="font-size:20px;font-weight:700">' + stats.total_tokens.toLocaleString() + '</span></div>' +
        '<div><strong>Estimated Cost:</strong><br><span style="font-size:20px;font-weight:700">$' + stats.estimated_cost + '</span></div>' +
        '<div><strong>Avg Tokens/Chat:</strong><br><span style="font-size:20px;font-weight:700">' + Math.round(stats.total_tokens / (stats.total_chats || 1)) + '</span></div>' +
        '</div>'
      );
    });
  }

  function statCard(label, value, icon) {
    const icons = { chart: '📊', email: '💬', yes: '✅', warning: '⚠️', phone: '📞', text: '📝' };
    return '<div class="ceac-stat-card"><div class="ceac-stat-icon">' + (icons[icon] || '📊') + '</div><h3>' + label + '</h3><p class="ceac-stat-value">' + value + '</p></div>';
  }

  if ($('#ceac-analytics-stats').length) {
    loadAnalytics();
    $('#ceac-analytics-days').on('change', loadAnalytics);
  }

  // Export CSV
  $('#ceac-export-csv').on('click', function () {
    const days = $('#ceac-analytics-days').val() || 30;
    apiRequest('/export?days=' + days + '&format=csv').done(function (data) {
      if (!data.rows || !data.rows.length) { showNotice('No data to export', 'info'); return; }
      let csv = 'ID,Session,Language,Status,Escalated,Tokens,Date,Messages\n';
      data.rows.forEach(function (row) {
        csv += [row.id, row.session_id, row.language, row.status, row.escalated, row.token_count, row.created_at, '"' + (row.messages || '').replace(/"/g, '""') + '"'].join(',') + '\n';
      });
      const blob = new Blob([csv], { type: 'text/csv' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'ceac-conversations-' + days + 'd.csv';
      a.click();
    });
  });

  // Conversation viewer
  $('.ceac-view-conv').on('click', function () {
    const id = $(this).data('id');
    const $modal = $('#ceac-conv-modal');
    $modal.show();
    $('#ceac-conv-messages').html('<div style="text-align:center;padding:20px"><span class="ceac-spinner"></span><p>Loading conversation #' + id + '...</p></div>');

    $.post(ajaxurl, {
      action: 'ceac_get_conversation',
      id: id,
      _wpnonce: NONCE
    }).done(function (res) {
      if (res.success && res.data) {
        let html = '';
        res.data.forEach(function (msg) {
          const role = msg.role === 'user' ? 'User' : 'Assistant';
          html += '<div class="ceac-conv-msg ceac-conv-msg-' + msg.role + '"><strong>' + role + ':</strong><br>' + $('<span>').text(msg.content).html() + '<br><small style="color:#94a3b8">' + (msg.created_at || '') + '</small></div>';
        });
        $('#ceac-conv-messages').html(html || '<div class="ceac-empty"><p>No messages found.</p></div>');
      } else {
        $('#ceac-conv-messages').html('<div class="ceac-empty"><p>Could not load conversation.</p></div>');
      }
    }).fail(function () {
      $('#ceac-conv-messages').html('<div class="ceac-empty"><p>Error loading conversation.</p></div>');
    });
  });

  $('.ceac-modal-close, .ceac-modal').on('click', function (e) {
    if (e.target === this) $('#ceac-conv-modal').hide();
  });

  // Conversation search
  $('#ceac-conv-search').on('keyup', function () {
    const query = $(this).val().toLowerCase();
    const $rows = $('#ceac-conv-table tbody tr');
    let visible = 0;
    $rows.each(function () {
      const text = $(this).text().toLowerCase();
      const match = text.indexOf(query) !== -1;
      $(this).toggle(match);
      if (match) visible++;
    });
    $('#ceac-conv-count').text(visible + ' of ' + $rows.length);
  });

  // Keyboard shortcuts: Escape to close modals
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      $('.ceac-modal').hide();
    }
  });

})(jQuery);