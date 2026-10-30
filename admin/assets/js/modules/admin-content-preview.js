const root = document.querySelector('[data-preview-root]');
if (root) {
  const safeUrl = value => {
    const v = String(value || '').trim();
    return v.startsWith('/') && !v.startsWith('//')
      ? v
      : (/^https:\/\/[a-z0-9.-]+(?::[0-9]+)?(?:\/[^\s]*)?$/i.test(v) ? v : '#');
  };
  const el = (tag, cls, text) => {
    const node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text !== undefined) node.textContent = String(text);
    return node;
  };
  const mediaUrl = id => {
    const url = new URL(root.dataset.apiUrl, location.href);
    url.searchParams.set('path', '/admin/media/' + Number(id) + '/file');
    return url.toString();
  };
  const image = (id, alt) => {
    if (!Number.isInteger(Number(id)) || Number(id) < 1) return null;
    const node = el('img', 'cms-live-section__image');
    node.src = mediaUrl(id);
    node.alt = String(alt || '');
    return node;
  };
  const setStyles = (node, style = {}) => {
    const mode = style.background_type || 'transparent';
    node.style.setProperty('--cms-background', mode === 'solid' && /^#[0-9a-f]{3,8}$/i.test(String(style.background || '')) ? style.background : 'transparent');
    const gradient = mode === 'gradient';
    node.style.setProperty('--cms-gradient-from', gradient && /^#[0-9a-f]{3,8}$/i.test(String(style.gradient_from || '')) ? style.gradient_from : 'transparent');
    node.style.setProperty('--cms-gradient-to', gradient && /^#[0-9a-f]{3,8}$/i.test(String(style.gradient_to || '')) ? style.gradient_to : 'transparent');
    [['color', '--cms-color'], ['muted_color', '--cms-muted'], ['accent_color', '--cms-accent']].forEach(([key, property]) => {
      if (/^#[0-9a-f]{3,8}$/i.test(String(style[key] || ''))) node.style.setProperty(property, style[key]);
    });
    node.style.setProperty('--cms-gradient-angle', Math.max(0, Math.min(360, Number(style.gradient_angle) || 135)) + 'deg');
    [['padding_top', 'padding-top'], ['padding_bottom', 'padding-bottom'], ['radius', 'radius'], ['container_width', 'container-width']].forEach(([key, property]) => {
      if (Number.isFinite(Number(style[key]))) node.style.setProperty('--cms-' + property, Math.max(0, Math.min(1600, Number(style[key]))) + 'px');
    });
    if (['right', 'center', 'left'].includes(style.text_align)) node.style.setProperty('--cms-text-align', style.text_align);
  };
  const render = data => {
    const section = el('section', 'cms-live-section cms-live-section--' + String(data.section_type || 'custom').replace(/[^a-z0-9_-]/g, ''));
    setStyles(section, data.style || {});
    const inner = el('div', 'cms-live-section__inner');
    const content = data.content || {};
    if (content.eyebrow) inner.append(el('p', 'cms-live-section__eyebrow', content.eyebrow));
    if (content.heading || data.title) inner.append(el(data.section_type === 'intro' ? 'h1' : 'h2', 'cms-live-section__heading', content.heading || data.title));
    if (content.body) inner.append(el('p', 'cms-live-section__body', content.body));
    const mainImage = image(content.image_id, content.image_alt || content.heading);
    if (mainImage) inner.append(mainImage);
    if (Array.isArray(content.items) && content.items.length && data.section_type === 'faq') {
      const faq = el('div', 'cms-faq-list');
      content.items.forEach(item => {
        const details = el('details', '');
        details.append(el('summary', '', item.title || ''));
        if (item.body) details.append(el('p', '', item.body));
        faq.append(details);
      });
      inner.append(faq);
    } else if (Array.isArray(content.items) && content.items.length) {
      const grid = el('div', 'cms-live-section__grid');
      content.items.forEach(item => {
        const card = el('article', 'cms-live-card');
        const itemImage = image(item.image_id, item.image_alt || item.title);
        if (itemImage) card.append(itemImage);
        if (item.eyebrow) card.append(el('span', 'cms-live-card__eyebrow', item.eyebrow));
        if (item.title) card.append(el('h3', '', item.title));
        if (item.body) card.append(el('p', '', item.body));
        if (item.link_label) {
          const link = el('a', '', item.link_label);
          link.href = safeUrl(item.url);
          card.append(link);
        }
        grid.append(card);
      });
      inner.append(grid);
    }
    if (content.button_label && content.button_url) {
      const button = el('a', 'cms-live-section__button', content.button_label);
      button.href = safeUrl(content.button_url);
      inner.append(button);
    }
    section.append(inner);
    root.replaceChildren(section);
  };
  const updateNative = data => {
    const node = root.querySelector('[data-cms-native]');
    if (!node) return false;
    const content = data.content || {};
    setStyles(node, data.style || {});
    ['eyebrow', 'heading', 'body', 'button_label'].forEach(key => {
      const target = node.querySelector('[data-cms-field="' + key + '"]');
      if (!target || content[key] === undefined) return;
      if (key === 'button_label') {
        const textNode = [...target.childNodes].find(child => child.nodeType === Node.TEXT_NODE);
        if (textNode) textNode.textContent = String(content[key] || '');
        else target.textContent = String(content[key] || '');
      } else {
        target.textContent = String(content[key] || '');
      }
    });
    const button = node.querySelector('.hero-button-primary,.home-setup-preview__cta,.home-compatibility-preview__cta,.new-products-more');
    if (button && content.button_url !== undefined) button.href = content.button_url ? safeUrl(content.button_url) : '#';
    let imageNode = node.querySelector('[data-cms-image],.cms-live-section__image');
    if (Number(content.image_id) > 0) {
      if (!imageNode) {
        imageNode = el('img', 'cms-live-section__image');
        node.prepend(imageNode);
      }
      imageNode.src = mediaUrl(content.image_id);
      imageNode.alt = String(content.image_alt || content.heading || '');
    } else if (imageNode && imageNode.classList.contains('cms-live-section__image')) {
      imageNode.remove();
    }
    return true;
  };
  window.addEventListener('message', event => {
    if (event.origin !== location.origin || event.source !== parent || event.data?.type !== 'cms-preview') return;
    const data = event.data.section || {};
    if (!updateNative(data)) render(data);
  });
}
