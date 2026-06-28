(function (blocks, element) {
  var el = element.createElement;
  blocks.registerBlockType('ceac/chatbot', {
    edit: function () {
      return el('div', { className: 'ceac-block-preview', style: { padding: '20px', background: '#f0f4f8', borderRadius: '8px', textAlign: 'center' } },
        el('span', { style: { fontSize: '24px' } }, '💬'),
        el('p', {}, 'AI Assistant Chatbot'),
        el('small', {}, 'Chat widget will render here on the frontend.')
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp.blocks, window.wp.element);
