(() => {
  const slug = (value) => value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'field';

  const wireFormStudio = (studio) => {
    const canvas = studio.querySelector('[data-form-canvas]');
    const schemaInput = studio.querySelector('[data-studio-schema]');
    const preview = studio.querySelector('[data-schema-preview]');
    const labelInput = studio.querySelector('[data-field-label]');
    const keyInput = studio.querySelector('[data-field-key]');
    const requiredInput = studio.querySelector('[data-field-required]');
    const sourceInput = studio.querySelector('[data-field-data-source]');
    let selected = canvas.querySelector('.canvas-field');

    const selectField = (field) => {
      selected?.classList.remove('selected');
      selected = field;
      selected.classList.add('selected');
      labelInput.value = field.textContent.trim();
      keyInput.value = field.dataset.key || slug(labelInput.value);
      requiredInput.value = field.dataset.required === 'true' ? 'true' : 'false';
      sourceInput.value = field.dataset.dataSource || '';
      updateSchema();
    };

    const addField = (component) => {
      const section = canvas.querySelector('.canvas-section');
      const field = document.createElement('div');
      field.className = 'canvas-field';
      field.draggable = true;
      field.dataset.key = slug(component.default_label);
      field.dataset.type = component.type;
      field.dataset.category = component.category;
      field.dataset.required = 'false';
      field.dataset.dataSource = component.data_source || '';
      field.textContent = component.default_label;
      section.appendChild(field);
      selectField(field);
    };

    const fields = () => [...canvas.querySelectorAll('.canvas-field')].map((field) => ({
      key: field.dataset.key || slug(field.textContent),
      label: field.textContent.trim(),
      type: field.dataset.type || 'text',
      category: field.dataset.category || 'Basic',
      section: field.closest('.canvas-section')?.querySelector('span')?.textContent?.trim() || 'General',
      required: field.dataset.required === 'true',
      data_source: field.dataset.dataSource || null,
      visibility: 'role_and_scope',
      conditions: [],
    }));

    const updateSchema = () => {
      if (!schemaInput) return;
      const currentFields = fields();
      const schema = {
        sections: [...new Set(currentFields.map((field) => field.section))],
        required: currentFields.filter((field) => field.required).map((field) => field.label),
        fields: currentFields,
        layout: { mode: studio.dataset.preview || 'Desktop', columns: 2 },
        permissions: { visibility: 'role_and_scope', field_level: true },
        conditions: [],
        data_sources: [...new Set(currentFields.map((field) => field.data_source).filter(Boolean))],
        translations: { en: true, ar_ready: true },
      };
      schemaInput.value = JSON.stringify(schema);
      preview.textContent = JSON.stringify(schema, null, 2);
    };

    studio.querySelectorAll('[data-component]').forEach((button) => {
      button.addEventListener('dragstart', (event) => event.dataTransfer.setData('application/json', button.dataset.component));
      button.addEventListener('click', () => addField(JSON.parse(button.dataset.component)));
    });

    canvas.addEventListener('dragover', (event) => event.preventDefault());
    canvas.addEventListener('drop', (event) => {
      event.preventDefault();
      const payload = event.dataTransfer.getData('application/json');
      if (payload) addField(JSON.parse(payload));
    });

    canvas.addEventListener('click', (event) => {
      const field = event.target.closest('.canvas-field');
      if (field) selectField(field);
    });

    [labelInput, keyInput, requiredInput, sourceInput].forEach((input) => input.addEventListener('input', () => {
      if (!selected) return;
      selected.textContent = labelInput.value;
      selected.dataset.key = keyInput.value || slug(labelInput.value);
      selected.dataset.required = requiredInput.value;
      selected.dataset.dataSource = sourceInput.value;
      updateSchema();
    }));

    studio.querySelectorAll('[data-preview-mode]').forEach((button) => {
      button.addEventListener('click', () => {
        studio.dataset.preview = button.dataset.previewMode;
        updateSchema();
      });
    });

    if (selected) selectField(selected);
    studio.addEventListener('submit', updateSchema);
  };

  const wireWorkflowStudio = (studio) => {
    const canvas = studio.querySelector('[data-workflow-canvas]');
    const schemaInput = studio.querySelector('[data-workflow-schema]');
    const stagesInput = studio.querySelector('[data-workflow-stages]');
    const preview = studio.querySelector('[data-workflow-preview]');

    const nodes = () => [...canvas.querySelectorAll('.workflow-node')].map((node, index) => ({
      id: `node_${index + 1}`,
      type: node.dataset.type || 'human_task',
      label: node.textContent.trim(),
      kind: node.dataset.kind || 'Task',
      assignee: index === 0 ? 'system' : 'record_owner',
      sla: node.dataset.sla || 'P3D',
    }));

    const updateWorkflow = () => {
      const currentNodes = nodes();
      const schema = {
        stages: currentNodes.map((node) => node.label),
        nodes: currentNodes,
        edges: currentNodes.slice(1).map((node, index) => ({ from: currentNodes[index].id, to: node.id })),
        sla: { business_days: true, pause_on_return: true },
        separation_of_duties: true,
      };
      stagesInput.value = schema.stages.join(', ');
      schemaInput.value = JSON.stringify(schema);
      preview.textContent = JSON.stringify(schema, null, 2);
    };

    const addNode = (node) => {
      const element = document.createElement('div');
      element.className = 'workflow-node';
      element.draggable = true;
      element.dataset.type = node.type;
      element.dataset.kind = node.kind;
      element.dataset.sla = node.type === 'timer' || node.type === 'escalation' ? 'P1D' : 'P3D';
      element.textContent = node.label;
      const end = [...canvas.querySelectorAll('.workflow-node')].find((item) => item.dataset.type === 'end');
      canvas.insertBefore(element, end || null);
      updateWorkflow();
    };

    studio.querySelectorAll('[data-node]').forEach((button) => {
      button.addEventListener('dragstart', (event) => event.dataTransfer.setData('application/json', button.dataset.node));
      button.addEventListener('click', () => addNode(JSON.parse(button.dataset.node)));
    });

    canvas.addEventListener('dragover', (event) => event.preventDefault());
    canvas.addEventListener('drop', (event) => {
      event.preventDefault();
      const payload = event.dataTransfer.getData('application/json');
      if (payload) addNode(JSON.parse(payload));
    });

    studio.querySelector('[data-simulate-workflow]')?.addEventListener('click', updateWorkflow);
    studio.addEventListener('submit', updateWorkflow);
    updateWorkflow();
  };

  document.querySelectorAll('[data-form-studio]').forEach(wireFormStudio);
  document.querySelectorAll('[data-workflow-studio]').forEach(wireWorkflowStudio);

  document.querySelectorAll('[data-observation-wizard]').forEach((wizard) => {
    const buttons = [...wizard.querySelectorAll('[data-step-button]')];
    const pages = [...wizard.querySelectorAll('[data-step-page]')];
    const previous = wizard.querySelector('[data-step-prev]');
    const next = wizard.querySelector('[data-step-next]');
    let active = 0;

    const show = (index) => {
      active = Math.max(0, Math.min(index, pages.length - 1));
      buttons.forEach((button, item) => button.classList.toggle('active', item === active));
      pages.forEach((page, item) => page.classList.toggle('active', item === active));
      if (previous) previous.disabled = active === 0;
      if (next) next.hidden = active === pages.length - 1;
    };

    buttons.forEach((button, index) => button.addEventListener('click', () => show(index)));
    previous?.addEventListener('click', () => show(active - 1));
    next?.addEventListener('click', () => show(active + 1));
    show(0);
  });

  document.querySelectorAll('[data-record-tabs]').forEach((record) => {
    const tabs = [...record.querySelectorAll('[data-record-tab]')];
    const panels = [...record.querySelectorAll('[data-record-panel]')];
    const show = (index) => {
      tabs.forEach((tab, item) => tab.classList.toggle('active', item === index));
      panels.forEach((panel, item) => panel.classList.toggle('active', item === index));
    };

    tabs.forEach((tab, index) => tab.addEventListener('click', () => show(index)));
    record.querySelectorAll('[data-record-jump]').forEach((jump) => {
      jump.addEventListener('click', (event) => {
        event.preventDefault();
        show(Number(jump.dataset.recordJump || 0));
      });
    });
  });

  document.querySelectorAll('[data-open-action-drawer]').forEach((button) => {
    button.addEventListener('click', () => {
      button.closest('[data-record-panel]')?.querySelector('[data-action-drawer] input, [data-action-drawer] textarea, [data-action-drawer] select')?.focus();
    });
  });
})();
