(function ($) {
  'use strict';

  let network = null;

  window.loadKnowledgeGraph = function () {
    const container = document.getElementById('ceac-knowledge-graph');
    if (!container) return;

    $.ajax({
      url: ceacAdmin.apiUrl + '/knowledge-graph',
      headers: { 'X-WP-Nonce': ceacAdmin.nonce }
    }).done(function (data) {
      renderGraph(container, data);
      renderStats(data.stats);
    });
  };

  function renderStats(stats) {
    if (!stats) return;
    $('#ceac-graph-stats').html(
      '<span><strong>' + stats.knowledge_count + '</strong> knowledge nodes</span>' +
      '<span><strong>' + stats.link_count + '</strong> connections</span>' +
      '<span><strong>' + stats.fallback_count + '</strong> fallback queries</span>' +
      '<span><strong>' + stats.intent_count + '</strong> user intents</span>'
    );
  }

  function renderGraph(container, data) {
    const nodes = new vis.DataSet(data.nodes.map(function (n) {
      return {
        id: n.id,
        label: n.label,
        color: { background: n.color, border: n.color, highlight: { background: n.color, border: '#fff' } },
        size: n.size || 15,
        font: { color: '#e2e8f0', size: 11, face: 'Inter, sans-serif' },
        group: n.group,
        title: n.label + ' (' + n.type + ')'
      };
    }));

    const edges = new vis.DataSet(data.edges.map(function (e, i) {
      return {
        id: i,
        from: e.from,
        to: e.to,
        width: Math.max(1, (e.weight || 0.5) * 3),
        color: { color: e.dashes ? 'rgba(229,62,62,0.5)' : 'rgba(99,179,237,0.4)', highlight: '#63b3ed' },
        dashes: e.dashes || false,
        smooth: { type: 'continuous' }
      };
    }));

    const options = {
      nodes: {
        shape: 'dot',
        borderWidth: 2,
        shadow: true
      },
      edges: {
        shadow: false
      },
      physics: {
        enabled: true,
        barnesHut: {
          gravitationalConstant: -3000,
          centralGravity: 0.3,
          springLength: 120,
          springConstant: 0.04,
          damping: 0.09
        },
        stabilization: { iterations: 150 }
      },
      interaction: {
        hover: true,
        tooltipDelay: 100,
        zoomView: true,
        dragView: true
      },
      layout: {
        improvedLayout: true
      }
    };

    if (network) {
      network.destroy();
    }

    network = new vis.Network(container, { nodes: nodes, edges: edges }, options);

    network.on('click', function (params) {
      if (params.nodes.length > 0) {
        const nodeId = params.nodes[0];
        const node = data.nodes.find(function (n) { return n.id === nodeId; });
        if (node) {
          network.selectNodes([nodeId]);
        }
      }
    });

    network.once('stabilizationIterationsDone', function () {
      network.fit({ animation: { duration: 500, easingFunction: 'easeInOutQuad' } });
    });
  }

  $(document).ready(function () {
    if ($('#ceac-knowledge-graph').length) {
      loadKnowledgeGraph();
    }

    $('#ceac-refresh-graph').on('click', loadKnowledgeGraph);
    $('#ceac-sync-and-refresh').on('click', function () {
      const $btn = $(this);
      $btn.prop('disabled', true);
      $.ajax({
        url: ceacAdmin.apiUrl + '/sync-content',
        method: 'POST',
        headers: { 'X-WP-Nonce': ceacAdmin.nonce }
      }).always(function () {
        loadKnowledgeGraph();
        $btn.prop('disabled', false);
      });
    });
  });

})(jQuery);
