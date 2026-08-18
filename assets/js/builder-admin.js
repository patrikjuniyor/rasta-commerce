/**
 * Rasta Builder — admin drag-and-drop editor.
 * Dependencies: none.
 */
(() => {
  'use strict';

  const root = document.querySelector('[data-rb-admin]');
  if (!root) return;

  const canvas = root.querySelector('[data-rb-canvas]');
  const input = root.querySelector('[data-rb-input]');
  const empty = root.querySelector('[data-rb-empty]');
  const schema = JSON.parse(root.querySelector('[data-rb-schema]').textContent || '{}');

  /* ── State: read existing blocks from DOM → build model ── */
  const blocks = [];

  const readBlockFromDom = (el) => {
    const type = el.dataset.rbType;
    const props = {};
    el.querySelectorAll('[data-rb-prop]').forEach((f) => {
      props[f.dataset.rbProp] = f.value;
    });
    return { type, props };
  };

  const syncFromDom = () => {
    blocks.length = 0;
    canvas.querySelectorAll('[data-rb-block]').forEach((el) => blocks.push(readBlockFromDom(el)));
    input.value = JSON.stringify(blocks);
    empty.style.display = blocks.length ? 'none' : 'grid';
  };

  const buildBlockEl = (type, props) => {
    const def = schema[type];
    if (!def) return null;

    const el = document.createElement('div');
    el.className = 'rb-block-item';
    el.draggable = true;
    el.dataset.rbBlock = '';
    el.dataset.rbType = type;

    const head = document.createElement('div');
    head.className = 'rb-block-item__head';
    head.innerHTML = `<span class="dashicons ${def.icon}"></span><strong>${def.label}</strong>`;
    const tools = document.createElement('span');
    tools.className = 'rb-block-item__tools';
    tools.innerHTML = `
      <button type="button" class="rb-tool" data-rb-up title="بالا">↑</button>
      <button type="button" class="rb-tool" data-rb-down title="پایین">↓</button>
      <button type="button" class="rb-tool rb-tool--danger" data-rb-del title="حذف">✕</button>`;
    head.appendChild(tools);

    const fields = document.createElement('div');
    fields.className = 'rb-block-item__fields';
    (def.fields || []).forEach((f) => {
      const lab = document.createElement('label');
      lab.className = 'rb-field';
      const span = document.createElement('span');
      span.textContent = f.label;
      lab.appendChild(span);

      let ctrl;
      const val = props && props[f.key] !== undefined ? props[f.key] : (def.defaults[f.key] ?? '');
      if (f.type === 'textarea') {
        ctrl = document.createElement('textarea');
        ctrl.rows = 2;
        ctrl.value = val;
      } else if (f.type === 'select') {
        ctrl = document.createElement('select');
        Object.entries(f.options || {}).forEach(([v, l]) => {
          const o = document.createElement('option');
          o.value = v;
          o.textContent = l;
          o.selected = val === v;
          ctrl.appendChild(o);
        });
      } else if (f.type === 'number') {
        ctrl = document.createElement('input');
        ctrl.type = 'number';
        ctrl.min = 1;
        ctrl.value = val;
      } else if (f.type === 'url') {
        ctrl = document.createElement('input');
        ctrl.type = 'url';
        ctrl.value = val;
      } else {
        ctrl = document.createElement('input');
        ctrl.type = 'text';
        ctrl.value = val;
      }
      ctrl.dataset.rbProp = f.key;
      lab.appendChild(ctrl);
      fields.appendChild(lab);
    });

    el.append(head, fields);
    bindBlockEvents(el);
    return el;
  };

  const bindBlockEvents = (el) => {
    el.addEventListener('input', syncFromDom);

    el.addEventListener('click', (e) => {
      const t = e.target;
      if (t.dataset.rbUp) { moveBlock(el, -1); }
      else if (t.dataset.rbDown) { moveBlock(el, 1); }
      else if (t.dataset.rbDel) {
        el.remove();
        syncFromDom();
      }
    });

    /* Reorder drag */
    el.addEventListener('dragstart', (e) => {
      e.dataTransfer.setData('text/rb-move', '1');
      e.dataTransfer.effectAllowed = 'move';
      el.classList.add('dragging');
    });
    el.addEventListener('dragend', () => {
      el.classList.remove('dragging');
      clearDropIndicators();
    });
    el.addEventListener('dragover', (e) => {
      const moving = canvas.querySelector('.dragging');
      if (!moving || moving === el) return;
      e.preventDefault();
      e.stopPropagation();
      const r = el.getBoundingClientRect();
      const before = e.clientY < (r.top + r.height / 2);
      el.classList.toggle('dragover-top', before);
      el.classList.toggle('dragover-bottom', !before);
    });
    el.addEventListener('dragleave', () => {
      el.classList.remove('dragover-top', 'dragover-bottom');
    });
    el.addEventListener('drop', (e) => {
      const moving = canvas.querySelector('.dragging');
      if (!moving || moving === el) return;
      e.preventDefault();
      e.stopPropagation();
      const r = el.getBoundingClientRect();
      const before = e.clientY < (r.top + r.height / 2);
      if (before) canvas.insertBefore(moving, el);
      else canvas.insertBefore(moving, el.nextSibling);
      syncFromDom();
    });
  };

  const clearDropIndicators = () => {
    canvas.querySelectorAll('[data-rb-block]').forEach((b) => b.classList.remove('dragover-top', 'dragover-bottom'));
  };

  const moveBlock = (el, dir) => {
    const sib = dir < 0 ? el.previousElementSibling : el.nextElementSibling;
    if (!sib) return;
    if (dir < 0) canvas.insertBefore(el, sib);
    else canvas.insertBefore(el, sib.nextSibling);
    syncFromDom();
  };

  /* ── Palette drag → canvas ── */
  root.querySelectorAll('[data-rb-type]').forEach((item) => {
    item.addEventListener('dragstart', (e) => {
      e.dataTransfer.setData('text/rb-new', item.dataset.rbType);
      e.dataTransfer.effectAllowed = 'copy';
    });
  });

  canvas.addEventListener('dragover', (e) => {
    if (e.dataTransfer.types.includes('text/rb-new') || e.dataTransfer.types.includes('text/rb-move')) {
      e.preventDefault();
      canvas.classList.add('dragover');
    }
  });
  canvas.addEventListener('dragleave', () => canvas.classList.remove('dragover'));

  canvas.addEventListener('drop', (e) => {
    const type = e.dataTransfer.getData('text/rb-new');
    if (!type) return;
    e.preventDefault();
    canvas.classList.remove('dragover');
    const el = buildBlockEl(type, schema[type].defaults);
    if (!el) return;
    /* Drop position: before the block being hovered, else append. */
    const target = e.target.closest('[data-rb-block]');
    if (target) {
      const r = target.getBoundingClientRect();
      const before = e.clientY < (r.top + r.height / 2);
      canvas.insertBefore(el, before ? target : target.nextSibling);
    } else {
      canvas.appendChild(el);
    }
    syncFromDom();
  });

  /* ── Enable toggle ── */
  const enable = root.querySelector('[data-rb-enabled]');
  const updateDisabledState = () => {
    const off = !enable.checked;
    canvas.style.opacity = off ? '0.5' : '1';
    canvas.style.pointerEvents = off ? 'none' : '';
    root.querySelectorAll('[data-rb-type]').forEach((p) => { p.style.opacity = off ? '0.5' : '1'; p.style.pointerEvents = off ? 'none' : ''; });
  };
  enable.addEventListener('change', updateDisabledState);
  updateDisabledState();

  /* ── Initial sync ── */
  syncFromDom();
})();
