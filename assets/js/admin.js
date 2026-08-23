/**
 * Bullet Form Builder — Admin JavaScript v2
 * Row-based layout system: FIELDS / LAYOUT / OPTIONS / CONDITIONS
 */
(function ($) {
    'use strict';

    /* ================================================================
       LAYOUT CONFIGS
       ================================================================ */
    var layoutConfigs = {
        '1-full':       { slots: 1, flexes: [1],    label: '1 Column'      },
        '2-equal':      { slots: 2, flexes: [1, 1],  label: '2 Columns'     },
        '3-equal':      { slots: 3, flexes: [1,1,1], label: '3 Columns'     },
        '4-equal':      { slots: 4, flexes: [1,1,1,1], label: '4 Columns'   },
        '1-2wide':      { slots: 2, flexes: [1, 2],  label: '1 + 2 Wide'    },
        '2wide-1':      { slots: 2, flexes: [2, 1],  label: '2 Wide + 1'    },
        'sidebar-main': { slots: 2, flexes: [1, 3],  label: 'Sidebar + Main'},
    };

    /* ================================================================
       FIELD TYPE DEFINITIONS
       ================================================================ */
    var fieldTypes = {
        name:       { label: 'Name',         placeholder: 'Your Name',       type: 'text'     },
        email:      { label: 'Email',         placeholder: 'Your Email',      type: 'email'    },
        phone:      { label: 'Phone',         placeholder: 'Your Phone',      type: 'tel'      },
        url:        { label: 'Website/URL',   placeholder: 'https://',        type: 'url'      },
        address:    { label: 'Address',       placeholder: 'Your Address',    type: 'text'     },
        text:       { label: 'Text',          placeholder: 'Enter text...',   type: 'text'     },
        textarea:   { label: 'Message',       placeholder: 'Write a message...', type: 'textarea' },
        dropdown:   { label: 'Dropdown',      placeholder: 'Select...',       type: 'select', options: ['Option 1','Option 2','Option 3'] },
        checkboxes: { label: 'Checkboxes',    placeholder: '',                type: 'checkboxes', options: ['Option 1','Option 2'] },
        radio:      { label: 'Radio Buttons', placeholder: '',                type: 'radio',   options: ['Option 1','Option 2'] },
        image:      { label: 'Image',         placeholder: '',                type: 'file'     },
        number:     { label: 'Number',        placeholder: '0',               type: 'number'   },
        date:       { label: 'Date',          placeholder: '',                type: 'date'     },
        checkbox:   { label: 'Checkbox',      placeholder: 'I agree',         type: 'checkbox' },
        // New field types
        product:       { label: 'Product',        placeholder: 'Select a product',      type: 'select' },
        price:         { label: 'Price',          placeholder: '0.00',                 type: 'number' },
        quantity:      { label: 'Quantity',       placeholder: '1',                    type: 'number' },
        coupon:        { label: 'Coupon',         placeholder: 'Enter coupon code',    type: 'text'   },
        payment_method: { label: 'Payment Method', placeholder: '',                     type: 'radio', options: ['Credit Card', 'PayPal', 'Bank Transfer'] },
        file:          { label: 'File Upload',    placeholder: '',                     type: 'file'   },
    };


    /* ================================================================
   FIELD OPTIONS SCHEMA — defines which controls each field type shows
   ================================================================ */
var fieldOptionsSchema = {
    // --- Simple text inputs ---
    text:       { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    email:      { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    phone:      { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    url:        { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    address:    { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    coupon:     { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions', 'cssClass'] }] },
    zip:        { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] }] },
    city:       { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] }] },
    zip:        { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] }] },
    first_name: { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] }] },
    last_name:  { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] }] },

    // --- Textarea ---
    textarea:   { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'rows', 'required', 'conditions','cssClass'] }] },

    // --- Selection fields (with options) ---
    dropdown:   { sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    radio:      { sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    checkboxes: { sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    payment_method: { sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    multiselect:{ sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    select_image:{ sections: [{ title: 'General', controls: ['label', 'name', 'options', 'required', 'conditions','cssClass'] }] },
    country:    { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'required', 'conditions','cssClass'] }] },
    us_states:  { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'required', 'conditions','cssClass'] }] },

    // --- Single checkbox (with defaultChecked) ---
    checkbox:   { sections: [{ title: 'General', controls: ['label', 'name', 'description', 'defaultChecked', 'required', 'conditions','cssClass'] }] },

    // --- Confirm (with confirmationText) ---
    confirm:    { sections: [{ title: 'General', controls: ['label', 'name', 'confirmationText', 'required', 'conditions','cssClass'] }] },

    // --- Number / Price / Quantity (with min/max) ---
    number:     { sections: [
        { title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] },
        { title: 'Validation', controls: ['min', 'max', 'step'] }
    ] },
    price:      { sections: [
        { title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] },
        { title: 'Validation', controls: ['min', 'max', 'step'] }
    ] },
    quantity:   { sections: [
        { title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions','cssClass'] },
        { title: 'Validation', controls: ['min', 'max', 'step'] }
    ] },

    // --- Date / DateTime ---
    date:       { sections: [{ title: 'General', controls: ['label', 'name', 'description', 'required', 'conditions','cssClass'] }] },
    datetime:   { sections: [{ title: 'General', controls: ['label', 'name', 'description', 'required', 'conditions','cssClass'] }] },

    // --- File upload ---
    file:       { sections: [{ title: 'General', controls: ['label', 'name', 'description', 'allowedTypes', 'maxSizeMb', 'required', 'conditions','cssClass'] }] },
    image:      { sections: [{ title: 'General', controls: ['label', 'name', 'description', 'allowedTypes', 'maxSizeMb', 'required', 'conditions','cssClass'] }] },

    // --- Signature ---
    signature:  { sections: [{ title: 'General', controls: ['label', 'name', 'canvasColor', 'required', 'conditions','cssClass'] }] },

    // --- Star Rating ---
    star_rating:{ sections: [{ title: 'General', controls: ['label', 'name', 'maxStars', 'required', 'conditions','cssClass'] }] },

    // --- Product (WooCommerce) ---
    product:    { sections: [{ title: 'General', controls: ['label', 'name', 'required', 'conditions','cssClass'] }] },

    // --- Layout / Structural ---
    divider:    { sections: [{ title: 'General', controls: ['label', 'style'] }] },
    html:       { sections: [{ title: 'General', controls: ['htmlContent'] }] },
    hidden:     { sections: [{ title: 'General', controls: ['name', 'defaultValue'] }] },
    repeatable: { sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'required', 'conditions','cssClass'] }] },

    // --- Spam / Captcha (no options, just a note) ---
    hcaptcha:   { sections: [{ title: 'General', controls: ['note'] }] },
    recaptcha:  { sections: [{ title: 'General', controls: ['note'] }] },
    turnstile:  { sections: [{ title: 'General', controls: ['note'] }] },
    antispam:   { sections: [{ title: 'General', controls: ['note'] }] },
};

var DEFAULT_OPTIONS_SCHEMA = {
    sections: [{ title: 'General', controls: ['label', 'name', 'placeholder', 'description', 'required', 'conditions'] }]
};

function getFieldOptionsSchema(type) {
    return fieldOptionsSchema[type] || DEFAULT_OPTIONS_SCHEMA;
}



/* ================================================================
   CONTROL DEFINITIONS for the options panel
   ================================================================ */
var optionControls = {
    'label':          { label: 'Label',          type: 'text' },
    'name':           { label: 'Field Name',     type: 'text' },
    'placeholder':    { label: 'Placeholder',    type: 'text' },
    'description':    { label: 'Description',    type: 'textarea' },
    'required':       { label: 'Required',       type: 'checkbox' },
    'conditions':     { label: 'Conditional Logic', type: 'textarea' },
    'options':        { label: 'Options (one per line)', type: 'textarea' },
    'defaultChecked': { label: 'Checked by default', type: 'checkbox' },
    'defaultValue':   { label: 'Default value',  type: 'text' },
    'htmlContent':    { label: 'HTML Content',   type: 'textarea' },
    'style':          { label: 'Style',          type: 'select', choices: [{value:'solid',label:'Solid'},{value:'dashed',label:'Dashed'}] },
    'confirmationText': { label: 'Confirmation Text', type: 'textarea', rows: 6 },
    'rows':           { label: 'Rows',           type: 'number' },
    'min':            { label: 'Min value',      type: 'number' },
    'max':            { label: 'Max value',      type: 'number' },
    'step':           { label: 'Step',           type: 'number' },
    'allowedTypes':   { label: 'Allowed file types (e.g. jpg,png,pdf)', type: 'text' },
    'maxSizeMb':      { label: 'Max size (MB)',  type: 'number' },
    'canvasColor':    { label: 'Canvas background color', type: 'text', placeholder: '#ffffff' },
    'maxStars':       { label: 'Max stars',      type: 'number' },
    'note':           { label: 'No options – site keys are set globally in the Settings tab.', type: 'note' },
    'cssClass': { label: 'CSS Class', type: 'text', placeholder: 'e.g. my-field' },
    // 'customCss':  { label: 'Custom CSS',  type: 'textarea', rows: 5, placeholder: '/* CSS rules for this field */' },
};



    /* ================================================================
       STATE
       ================================================================ */
    var formData = {
        rows:      [],  // [{id, layout, step, slots:[fieldId|null, ...]}]
        fieldDefs: {},  // {fieldId: {id, type, label, placeholder, ...}}
    };
    var selectedFieldId = null;
    var targetSlot      = null; // {rowId, slotIndex}
    var formSettings    = {};
    var formStyle       = {};
    var currentStep     = 1;

    /* ================================================================
       INIT
       ================================================================ */
    $(document).ready(function () {
        loadInitialData();
        bindEvents();


        /* ── Category Collapse Toggle ── */
$(document).on('click', '.bfmsf-category-header', function(e) {
    var $category = $(this).closest('.bfmsf-field-category');
    $category.toggleClass('collapsed');
    var categoryName = $category.data('category') || 'default';
    var isCollapsed = $category.hasClass('collapsed');
    localStorage.setItem('bfmsf_category_' + categoryName, isCollapsed);
});

/* ── Restore Category Collapse State ── */
$('.bfmsf-field-category').each(function() {
    var $cat = $(this);
    var categoryName = $cat.data('category') || 'default';
    var savedState = localStorage.getItem('bfmsf_category_' + categoryName);
    if (savedState === 'true') {
        $cat.addClass('collapsed');
    }
});

/* ── Search Field Filter ── */
$(document).on('input', '#bfmsf-field-search', function() {
    var query = $(this).val().toLowerCase();
    var hasResults = false;
    
    $('.bfmsf-field-type-card').each(function() {
        var label = $(this).find('.field-name').text().toLowerCase();
        var show = label.indexOf(query) !== -1 || query === '';
        $(this).toggle(show);
        if (show) hasResults = true;
    });
    
    // Show/hide empty categories
    $('.bfmsf-field-category').each(function() {
        var visibleFields = $(this).find('.bfmsf-field-type-card:visible').length;
        $(this).toggle(visibleFields > 0 || query === '');
    });
    
    // Show/hide search clear button
    $('#bfmsf-search-clear').toggle(query.length > 0);
});

/* ── Clear Search ── */
$(document).on('click', '#bfmsf-search-clear', function() {
    $('#bfmsf-field-search').val('').trigger('input');
});



        // Always render the canvas while the build tab is visible so that
        // jQuery UI droppable / draggable initialises correctly on visible elements.
        renderCanvas();
        renderLayoutRowsList();
        populateSettingsForm();
        populateStyleForm();

        // Now restore the last active tab. Doing this AFTER renderCanvas means
        // jQuery UI has already seen the visible slots and registered them properly.
        var savedTab = localStorage.getItem('bfmsf_active_tab');
        if (savedTab && savedTab !== 'build' && $('#tab-' + savedTab).length) {
            switchMainTab(savedTab);
        }


        /* ── Container Border Color: Picker ↔ Hex sync ── */
        $(document).on('input', '#bfmsf-container-border-color', function () {
            $('#bfmsf-container-border-color-hex').val($(this).val().replace('#', ''));
        });
        $(document).on('input', '#bfmsf-container-border-color-hex', function () {
            var hex = $(this).val().replace('#', '');
            if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
                $('#bfmsf-container-border-color').val('#' + hex);
            }
        });
    
    
    

});

    function loadInitialData() {
        if (typeof window.BFMSF_builder_data !== 'undefined') {
            var d = window.BFMSF_builder_data;
            var rows = d.rows;
            var fieldDefs = d.fieldDefs;

            if (typeof rows === 'string') {
                try { rows = JSON.parse(rows); } catch (e) { rows = []; }
            }
            if (typeof fieldDefs === 'string') {
                try { fieldDefs = JSON.parse(fieldDefs); } catch (e) { fieldDefs = {}; }
            }
            if (typeof d.settings === 'string') {
                try { d.settings = JSON.parse(d.settings); } catch (e) { d.settings = {}; }
            }
            if (typeof d.style === 'string') {
                try { d.style = JSON.parse(d.style); } catch (e) { d.style = {}; }
            }

            formData.rows = normalizeRows(rows);
            formData.fieldDefs = normalizeFieldDefs(fieldDefs);
            if (d.settings) formSettings = d.settings;
            if (d.style)    formStyle    = d.style;

            // Restore canvas title/subtitle
            if (d.canvasTitle)    setContentEditable('#bfmsf-canvas-title', d.canvasTitle);
            if (d.canvasSubtitle) setContentEditable('#bfmsf-canvas-subtitle', d.canvasSubtitle);
        }
    }

    function normalizeRows(rows) {
        var normalized = [];

        if (typeof rows === 'string') {
            try { rows = JSON.parse(rows); } catch (e) { rows = []; }
        }

        if (Array.isArray(rows)) {
            normalized = rows;
        } else if (rows && typeof rows === 'object' && Array.isArray(rows.rows)) {
            normalized = rows.rows;
        }

        return normalized.map(function (row) {
            if (!row || typeof row !== 'object') {
                return row;
            }
            if (row.step === undefined || row.step === null || row.step < 1) {
                row.step = 1;
            }
            if (!Array.isArray(row.slots)) {
                row.slots = [];
            }
            return row;
        });
    }

    function normalizeFieldDefs(fieldDefs) {
        if (!fieldDefs || typeof fieldDefs !== 'object') {
            return {};
        }

        if (fieldDefs.fieldDefs && typeof fieldDefs.fieldDefs === 'object') {
            fieldDefs = fieldDefs.fieldDefs;
        }

        if (Array.isArray(fieldDefs)) {
            var normalizedArray = {};
            fieldDefs.forEach(function (field) {
                if (field && field.id) {
                    normalizedArray[field.id] = field;
                }
            });
            return normalizedArray;
        }

        var normalized = {};
        Object.keys(fieldDefs).forEach(function (key) {
            var value = fieldDefs[key];
            if (value && typeof value === 'object') {
                if (value.id) {
                    normalized[value.id] = value;
                } else {
                    normalized[key] = value;
                }
            }
        });

        return normalized;
    }

    /* ================================================================
       BINDINGS
       ================================================================ */
    function bindEvents() {

        /* ── Main tab switching ── */
        $(document).on('click', '.bfmsf-main-tab', function () {
            switchMainTab($(this).data('tab'));
        });

        /* ── Sidebar tab switching ── */
        $(document).on('click', '.bfmsf-sidebar-tab', function () {
            switchSidebarPanel($(this).data('panel'));
        });

        /* ── Header UPDATE ── */
        $('#bfmsf-update-btn').on('click', function () { saveBuilder($(this)); });
        $(document).on('click', '#bfmsf-save-settings-btn', function () { saveBuilder($(this)); });
        $(document).on('click', '#bfmsf-save-style-btn', function () { saveBuilder($(this)); });

        /* ── Integration toggles: show/hide webhook URL field ── */
        $(document).on('change', '#bfmsf-setting-google-sheets', function () {
            $('#bfmsf-google-sheets-webhook-row').toggle($(this).is(':checked'));
        });
        $(document).on('change', '#bfmsf-setting-zapier', function () {
            $('#bfmsf-zapier-webhook-row').toggle($(this).is(':checked'));
        });
        $(document).on('change', '#bfmsf-setting-hubspot', function () {
            $('#bfmsf-hubspot-webhook-row').toggle($(this).is(':checked'));
        });

        /* ── EMBED button ── */
        $('#bfmsf-embed-btn').on('click', showEmbedModal);

        /* ── SHARE button ── */
        $('#bfmsf-share-btn').on('click', function () {
            var fid = BFMSF_admin.form_id;
            if (!fid) { showNotice('Save the form first.', 'info'); return; }
            copyToClipboard('[bfmsf_form id="' + fid + '"]');
            showNotice('Shortcode copied!', 'success');
        });

        /* ── PREVIEW button ── */
        $(document).on('click', '#bfmsf-preview-form-btn', function () {
            var fid = BFMSF_admin.form_id;
            if (!fid) { showNotice('Save the form first to preview it.', 'info'); return; }

            showNotice('Loading preview…', 'info');

            $.ajax({
                url: BFMSF_admin.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bfmsf_preview_form',
                    nonce:   BFMSF_admin.nonce,
                    form_id: fid
                },
                success: function (response) {
                    if (response && response.success && response.data && response.data.html) {
                        showFormPreviewModal(response.data.html, response.data.frontend_css || '');
                    } else {
                        var msg = (response && response.data && response.data.message) || 'Could not load form preview.';
                        showNotice(msg, 'error');
                    }
                },
                error: function () {
                    showNotice('Preview request failed. Please try again.', 'error');
                }
            });
        });

        /* ── STEP tab click ── */
        $(document).on('click', '.bfmsf-step-tab', function (e) {
            if ($(e.target).hasClass('bfmsf-step-delete-btn')) return;
            var s = parseInt($(this).data('step'), 10);
            if (s) {
                currentStep = s;
                clearTargetSlot();
                renderCanvas();
                renderLayoutRowsList();
            }
        });

        /* ── ADD STEP button click ── */
        $(document).on('click', '#bfmsf-add-step-action-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var maxStep = 1;
            formData.rows.forEach(function (row) {
                var s = parseInt(row.step || 1, 10);
                if (s > maxStep) maxStep = s;
            });
            var newStep = maxStep + 1;
            currentStep = newStep;
            clearTargetSlot();
            renderCanvas();
            renderLayoutRowsList();
            showNotice('Step ' + newStep + ' created. Add rows or fields here!', 'success');
        });

        /* ── DELETE STEP button click ── */
        $(document).on('click', '.bfmsf-step-delete-btn', function (e) {
            e.stopPropagation();
            var s = parseInt($(this).data('step'), 10);
            if (!confirm('Are you sure you want to delete Step ' + s + '? This will delete all fields inside this step.')) {
                return;
            }
            var rowsToKeep = [];
            formData.rows.forEach(function (row) {
                var rowStep = parseInt(row.step || 1, 10);
                if (rowStep === s) {
                    row.slots.forEach(function (fieldId) {
                        if (fieldId && formData.fieldDefs[fieldId]) {
                            delete formData.fieldDefs[fieldId];
                        }
                    });
                } else {
                    rowsToKeep.push(row);
                }
            });
            formData.rows = rowsToKeep.map(function (row) {
                var rowStep = parseInt(row.step || 1, 10);
                if (rowStep > s) {
                    row.step = rowStep - 1;
                }
                return row;
            });
            if (currentStep >= s && currentStep > 1) {
                currentStep = currentStep - 1;
            }
            clearTargetSlot();
            renderCanvas();
            renderLayoutRowsList();
            showNotice('Step deleted.', 'info');
        });

        /* ── CLEAR ALL ── */
        $(document).on('click', '#bfmsf-clear-all-btn', function () {
            if (!confirm('Clear all rows and fields? This cannot be undone.')) return;
            formData.rows      = [];
            formData.fieldDefs = {};
            selectedFieldId    = null;
            targetSlot         = null;
            clearTargetSlot();
            renderCanvas();
            renderLayoutRowsList();
            renderOptionsPanel(null);
            showNotice('Canvas cleared.', 'info');
        });

        /* ── FIELD TYPE CARD click → add to available slot ── */
        $(document).on('click', '.bfmsf-field-type-card', function () {
            var type = $(this).data('type');
            
            // If user clicked on a slot, use that
            if (targetSlot) {
                addFieldToSlot(type, targetSlot.rowId, targetSlot.slotIndex);
                return;
            }

            // Otherwise, find first row with empty slot in the current step
            var foundSlot = false;
            for (var i = 0; i < formData.rows.length; i++) {
                var row = formData.rows[i];
                if ((row.step || 1) !== currentStep) {
                    continue;
                }
                var emptyIdx = row.slots.indexOf(null);
                if (emptyIdx !== -1) {
                    addFieldToSlot(type, row.id, emptyIdx);
                    setTargetSlot(row.id, emptyIdx);
                    foundSlot = true;
                    break;
                }
            }

            // No empty slots - create a simple full-width row for this field on the current step.
            if (!foundSlot) {
                var field = makeFieldDef(type);
                formData.fieldDefs[field.id] = field;
                var newRow = {
                    id: 'row_' + Date.now() + '_' + Math.floor(Math.random()*9999),
                    layout: '1-full',
                    step: currentStep,
                    slots: [field.id]
                };
                formData.rows.push(newRow);
                renderCanvas();
                renderLayoutRowsList();
                selectField(field.id);
            }
        });

        /* ── LAYOUT option click → add a row to the current form ── */
        $(document).on('click', '.bfmsf-layout-option', function () {
            var layout = $(this).data('layout');
            var row = addRow(layout);
            if (row) {
                setTargetSlot(row.id, 0);
            }
            showNotice('Row added. Switch to FIELDS tab to place fields into columns.', 'info');
        });

        /* ── REMOVE ROW (from canvas bar) ── */
        $(document).on('click', '.bfmsf-remove-row-btn', function (e) {
            e.stopPropagation();
            var rowId = $(this).data('row-id');
            removeRow(rowId);
        });

        /* ── REMOVE ROW (from sidebar list) ── */
        $(document).on('click', '.bfmsf-row-list-delete', function () {
            removeRow($(this).data('row-id'));
        });

        /* ── ADD FIELD TO ROW button ── */
        $(document).on('click', '.bfmsf-add-field-to-row', function (e) {
            e.stopPropagation();
            var rowId = $(this).data('row-id');
            // Find first empty slot in this row
            var row = getRow(rowId);
            if (!row) return;
            var emptyIdx = row.slots.indexOf(null);
            if (emptyIdx === -1) {
                showNotice('No empty slots in this row.', 'info');
                return;
            }
            setTargetSlot(rowId, emptyIdx);
            switchSidebarPanel('fields');
        });

        /* ── DROP ZONE click → set as target slot ── */
        $(document).on('click', '.bfmsf-drop-zone', function (e) {
            e.stopPropagation();
            var $slot = $(this).closest('.bfmsf-slot');
            var rowId     = $slot.data('row-id');
            var slotIndex = parseInt($slot.data('slot-index'), 10);
            setTargetSlot(rowId, slotIndex);
            switchSidebarPanel('fields');
        });

        /* ── CONFIGURE field button ── */
        $(document).on('click', '.bfmsf-configure-btn', function (e) {
            e.stopPropagation();
            var fieldId = $(this).closest('.bfmsf-placed-field').data('field-id');
            selectField(fieldId);
        });

        /* ── Click placed field body → select ── */
        $(document).on('click', '.bfmsf-placed-field', function (e) {
            if (!$(e.target).is('.bfmsf-unplace-btn, .bfmsf-configure-btn')) {
                var fieldId = $(this).data('field-id');
                selectField(fieldId);
            }
        });

        /* ── UNPLACE field from slot ── */
        $(document).on('click', '.bfmsf-unplace-btn', function (e) {
            e.stopPropagation();
            var $field    = $(this).closest('.bfmsf-placed-field');
            var $slot     = $field.closest('.bfmsf-slot');
            var rowId     = $slot.data('row-id');
            var slotIndex = parseInt($slot.data('slot-index'), 10);
            var fieldId   = $field.data('field-id');
            unplaceField(rowId, slotIndex, fieldId);
        });

        /* ── OPTIONS: save field changes on every input change (auto-save) ── */
        $(document).on('click', '#bfmsf-save-field-btn', function () { saveFieldChanges(); });
        $(document).on('input change', '#panel-options input, #panel-options textarea, #panel-options select', function () {
            saveFieldChangesSilent();
        });

        /* ── CONDITIONS: save on button click and also on any change ── */
        $(document).on('click', '#bfmsf-save-cond-btn', function () { saveFieldConditions(); });
        $(document).on('input change', '#panel-conditions input, #panel-conditions select', function () {
            saveFieldConditions();
        });

        /* ── Range sliders: live display ── */
        $(document).on('input', '.bfmsf-range-input', function () {
            $(this).closest('.bfmsf-range-row').find('.bfmsf-range-value').text($(this).val());
        });

        /* ── Style options ── */
        $(document).on('click', '.bfmsf-style-option', function () {
            var group = $(this).data('group');
            if (group) {
                $('[data-group="' + group + '"]').removeClass('active');
            } else {
                $(this).siblings('.bfmsf-style-option').removeClass('active');
            }
            $(this).addClass('active');
        });
        $(document).on('click', '.bfmsf-align-btn', function () {
            $(this).siblings('.bfmsf-align-btn').removeClass('active');
            $(this).addClass('active');
        });
        /* ──────────────────────────────────────────────────────────────
           THEME SYSTEM: Click theme → apply preset colors
           ────────────────────────────────────────────────────────────── */
        var themePresets = {
            'default': { primary: '#4361ee', text: '#1f2937', btn_bg: '#4361ee', btn_text: '#ffffff' },
            'ocean':   { primary: '#0093E9', text: '#1f2937', btn_bg: '#0093E9', btn_text: '#ffffff' },
            'rose':    { primary: '#f5576c', text: '#1f2937', btn_bg: '#f5576c', btn_text: '#ffffff' },
            'sky':     { primary: '#4facfe', text: '#1f2937', btn_bg: '#4facfe', btn_text: '#ffffff' },
            'minimal': { primary: '#4361ee', text: '#374151', btn_bg: '#374151', btn_text: '#ffffff' },
            'dark':    { primary: '#6366f1', text: '#f9fafb', btn_bg: '#6366f1', btn_text: '#ffffff' },
        };

        function applyPrimaryColor(c) {
            $('#bfmsf-primary-color').val(c);
            $('#bfmsf-hex-input').val(c.replace('#',''));
        }
        function applyBtnBgColor(c) {
            $('#bfmsf-btn-bg-color').val(c);
            $('#bfmsf-btn-preview').css('background', c);
        }
        function applyBtnTextColor(c) {
            $('#bfmsf-btn-text-color').val(c);
            $('#bfmsf-btn-text-preview').css('background', c);
        }

        $(document).on('click', '.bfmsf-theme-thumb', function () {
            $('.bfmsf-theme-thumb').removeClass('active');
            $(this).addClass('active');
            var theme = $(this).data('theme');
            var preset = themePresets[theme];
            if (preset) {
                applyPrimaryColor(preset.primary);
                $('#bfmsf-text-color').val(preset.text);
                $('#bfmsf-text-color-hex').val(preset.text.replace('#',''));
                applyBtnBgColor(preset.btn_bg);
                applyBtnTextColor(preset.btn_text);
                /* Deselect swatch since theme applied its own color */
                $('.bfmsf-color-swatch').removeClass('active');
                /* Highlight matching swatch if exists */
                $('.bfmsf-color-swatch[data-color="' + preset.primary + '"]').addClass('active');
            }
        });

        /* ──────────────────────────────────────────────────────────────
           COLOR SWATCHES: Click = select, click again = deselect
           ────────────────────────────────────────────────────────────── */
        $(document).on('click', '.bfmsf-color-swatch', function () {
            var wasActive = $(this).hasClass('active');
            $('.bfmsf-color-swatch').removeClass('active');
            if (!wasActive) {
                $(this).addClass('active');
                applyPrimaryColor($(this).data('color'));
            }
            /* If deselecting, color picker retains value for manual editing */
        });

        /* ──────────────────────────────────────────────────────────────
           CLEAR/DESELECT BUTTON: Remove swatch selection
           ────────────────────────────────────────────────────────────── */
        $(document).on('click', '#bfmsf-clear-swatch', function () {
            $('.bfmsf-color-swatch').removeClass('active');
        });

        /* ──────────────────────────────────────────────────────────────
           PRIMARY COLOR: Picker ↔ Hex sync with swatch highlighting
           ────────────────────────────────────────────────────────────── */
        $(document).on('input', '#bfmsf-primary-color', function () {
            var c = $(this).val();
            $('#bfmsf-hex-input').val(c.replace('#',''));
            /* Highlight matching swatch if exists */
            var matched = false;
            $('.bfmsf-color-swatch').each(function () {
                if ($(this).data('color') === c) { $(this).addClass('active'); matched = true; }
                else { $(this).removeClass('active'); }
            });
        });

        $(document).on('input', '#bfmsf-hex-input', function () {
            var hex = $(this).val().replace('#','');
            if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
                var c = '#' + hex;
                $('#bfmsf-primary-color').val(c);
                /* Auto-highlight matching swatch */
                $('.bfmsf-color-swatch').each(function () {
                    if ($(this).data('color') === c) { $(this).addClass('active'); }
                    else { $(this).removeClass('active'); }
                });
            }
        });

        /* ──────────────────────────────────────────────────────────────
           TEXT COLOR: Picker ↔ Hex sync (NEW)
           ────────────────────────────────────────────────────────────── */
        $(document).on('input', '#bfmsf-text-color', function () {
            $('#bfmsf-text-color-hex').val($(this).val().replace('#',''));
        });

        $(document).on('input', '#bfmsf-text-color-hex', function () {
            var hex = $(this).val().replace('#','');
            if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
                $('#bfmsf-text-color').val('#' + hex);
            }
        });

        /* ──────────────────────────────────────────────────────────────
           BUTTON COLORS: Live preview updates (NEW)
           ────────────────────────────────────────────────────────────── */
        $(document).on('input', '#bfmsf-btn-bg-color', function () {
            applyBtnBgColor($(this).val());
        });

        $(document).on('input', '#bfmsf-btn-text-color', function () {
            applyBtnTextColor($(this).val());
        });

        /* ── Panel toggle collapse ── */
        $(document).on('click', '.bfmsf-panel-toggle', function () {
            $(this).closest('.bfmsf-settings-panel, .bfmsf-style-panel').find('.bfmsf-panel-body').slideToggle(160);
            $(this).text($(this).text().trim() === '∧' ? '∨' : '∧');
        });

        /* ── Accordion toggle ── */
        $(document).on('click', '.bfmsf-accordion-header', function () {
            var $item = $(this).closest('.bfmsf-accordion-item');
            $item.toggleClass('open');
            $(this).find('.bfmsf-accordion-chevron').text($item.hasClass('open') ? '∧' : '∨');
        });

        /* ── Keyboard: Escape clears target slot ── */
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') clearTargetSlot();
        });

        /* ── Sidebar resizer ── */
        initSidebarResizer();

        /* ── Canvas title / subtitle: update brand name ── */
        $(document).on('input', '#bfmsf-canvas-title', function () {
            $('#bfmsf-brand-name').text($(this).text().trim() || 'Untitled Form');
        });

        /* ── Close modals ── */
        $(document).on('click', '.bfmsf-modal-overlay', function (e) {
            if ($(e.target).is('.bfmsf-modal-overlay')) $(this).remove();
        });
        $(document).on('click', '.bfmsf-modal-close', function () {
            $(this).closest('.bfmsf-modal-overlay').remove();
        });

        /* ── View entry ── */
        $(document).on('click', '.bfmsf-view-entry-btn', function () {
            showEntryModal($(this).data('entry'), $(this).data('id'));
        });

        /* ── View / delete submissions ── */
        $(document).on('click', '.bfmsf-view-submission', function (e) {
            e.preventDefault();
            var submissionId = $(this).data('submission-id');
            if (!submissionId) return;

            $.ajax({
                url: BFMSF_admin.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bfmsf_view_submission',
                    nonce: BFMSF_admin.nonce,
                    submission_id: submissionId
                },
                success: function (response) {
                    if (response && response.success && response.data && response.data.html) {
                        $('#bfmsf-modal-body').html(response.data.html);
                        $('#bfmsf-view-modal').show();
                    } else {
                        showNotice((response && response.data && response.data.message) || 'Unable to load submission details.', 'error');
                    }
                },
                error: function () {
                    showNotice('Network error while loading submission details.', 'error');
                }
            });
        });

        $(document).on('click', '.bfmsf-delete-submission', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var submissionId = $btn.data('submission-id');
            if (!submissionId || !window.confirm('Delete this submission?')) return;

            $.ajax({
                url: BFMSF_admin.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bfmsf_delete_submission',
                    nonce: BFMSF_admin.nonce,
                    submission_id: submissionId
                },
                success: function (response) {
                    if (response && response.success) {
                        $btn.closest('tr').remove();
                        showNotice((response.data && response.data.message) || 'Submission deleted.', 'success');
                    } else {
                        showNotice((response && response.data && response.data.message) || 'Unable to delete submission.', 'error');
                    }
                },
                error: function () {
                    showNotice('Network error while deleting submission.', 'error');
                }
            });
        });

        $(document).on('click', '.bfmsf-export-csv', function (e) {
            e.preventDefault();
            var formId = $(this).data('form-id');
            if (!formId) return;
            window.location.href = BFMSF_admin.ajax_url +
                '?action=BFMSF_export_csv&form_id=' + encodeURIComponent(formId) +
                '&nonce=' + encodeURIComponent(BFMSF_admin.nonce);
        });

        $(document).on('click', '.bfmsf-close, #bfmsf-view-modal', function (e) {
            if (e.target === this || $(e.target).hasClass('bfmsf-close')) {
                $('#bfmsf-view-modal').hide();
            }
        });

        /* ── Field search ── */
        $(document).on('input', '#bfmsf-field-search', function () {
            var q = $(this).val().toLowerCase();
            $('.bfmsf-field-type-card').each(function () {
                $(this).toggle(!q || $(this).find('.field-name').text().toLowerCase().indexOf(q) !== -1);
            });
        });

        /* ── Drag field cards → drop on slots ── */
        initDragDrop();
    }

    /* ================================================================
       TAB / PANEL SWITCHING
       ================================================================ */
    function switchMainTab(tab) {
        $('.bfmsf-main-tab').removeClass('active');
        $('.bfmsf-main-tab[data-tab="' + tab + '"]').addClass('active');
        $('.bfmsf-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
        localStorage.setItem('bfmsf_active_tab', tab);

        // Re-initialise drag/drop whenever the build tab becomes visible,
        // because jQuery UI requires elements to be visible when registered.
        if (tab === 'build') {
            initSlotDroppable();
            initCanvasFieldDrag();
            initRowSortable();
        }
    }
    function switchSidebarPanel(panel) {
        $('.bfmsf-sidebar-tab').removeClass('active');
        $('.bfmsf-sidebar-tab[data-panel="' + panel + '"]').addClass('active');
        $('.bfmsf-sidebar-panel').removeClass('active');
        $('#panel-' + panel).addClass('active');
    }

    /* ================================================================
       ROW MANAGEMENT
       ================================================================ */
    function addRow(layout) {
        var cfg  = layoutConfigs[layout] || layoutConfigs['2-equal'];
        var slots = [];
        for (var i = 0; i < cfg.slots; i++) slots.push(null);
        
        var row = {
            id: 'row_' + Date.now() + '_' + Math.floor(Math.random()*9999),
            layout: layout,
            step: currentStep,
            slots: slots
        };
        formData.rows.push(row);
        renderCanvas();
        renderLayoutRowsList();

        // Scroll to bottom of rows
        setTimeout(function () {
            var $rows = $('#bfmsf-form-rows');
            $rows.animate({ scrollTop: $rows[0].scrollHeight }, 200);
            $('#bfmsf-content-area').animate({ scrollTop: $('#bfmsf-content-area')[0].scrollHeight }, 200);
        }, 50);

        return row;
    }

    function removeRow(rowId) {
        formData.rows = formData.rows.filter(function (r) { return r.id !== rowId; });
        if (selectedFieldId && !findFieldInRows(selectedFieldId)) {
            selectedFieldId = null;
            renderOptionsPanel(null);
            renderConditionsPanel(null);
        }
        clearTargetSlot();
        renderCanvas();
        renderLayoutRowsList();
    }

    function getRow(rowId) {
        return formData.rows.filter(function (r) { return r.id === rowId; })[0] || null;
    }

    function findFieldInRows(fieldId) {
        for (var i = 0; i < formData.rows.length; i++) {
            if (formData.rows[i].slots.indexOf(fieldId) !== -1) return true;
        }
        return false;
    }

    /* ================================================================
       FIELD MANAGEMENT
       ================================================================ */
    function makeFieldDef(type) {
        var def    = fieldTypes[type] || {};
        var fieldId = 'field_' + Date.now() + '_' + Math.floor(Math.random() * 9999);
        return {
            id:          fieldId,
            type:        type,
            label:       def.label       || type,
            placeholder: def.placeholder || '',
            confirmationText: '',
            description: '',
            required:    false,
            options:     def.options ? def.options.slice() : [],
            conditions:  [],
            cssClass:    '',
        };
    }

    function addFieldToSlot(type, rowId, slotIndex) {
        var row = getRow(rowId);
        if (!row) return;

        // If slot already has a field, remove it (replace)
        var oldId = row.slots[slotIndex];
        if (oldId && formData.fieldDefs[oldId]) delete formData.fieldDefs[oldId];

        var field = makeFieldDef(type);
        formData.fieldDefs[field.id] = field;
        row.slots[slotIndex] = field.id;

        clearTargetSlot();
        renderCanvas();
        renderLayoutRowsList();
        selectField(field.id);
    }

    function addFieldAsNewRow(type) {
        var field = makeFieldDef(type);
        formData.fieldDefs[field.id] = field;
        var row = {
            id: 'row_' + Date.now(),
            layout: '1-full',
            step: currentStep,
            slots: [field.id]
        };
        formData.rows.push(row);

        clearTargetSlot();
        renderCanvas();
        renderLayoutRowsList();
        selectField(field.id);
    }

    function unplaceField(rowId, slotIndex, fieldId) {
        var row = getRow(rowId);
        if (!row) return;
        row.slots[slotIndex] = null;
        // Remove field definition
        if (formData.fieldDefs[fieldId]) delete formData.fieldDefs[fieldId];

        if (selectedFieldId === fieldId) {
            selectedFieldId = null;
            renderOptionsPanel(null);
            renderConditionsPanel(null);
        }
        clearTargetSlot();
        renderCanvas();
        renderLayoutRowsList();
    }

    function getFieldDef(fieldId) {
        return formData.fieldDefs[fieldId] || null;
    }

    /* ================================================================
       TARGET SLOT
       ================================================================ */
    function setTargetSlot(rowId, slotIndex) {
        targetSlot = { rowId: rowId, slotIndex: slotIndex };
        // Highlight slot in canvas
        $('.bfmsf-slot').removeClass('bfmsf-slot-targeted');
        $('.bfmsf-slot[data-row-id="' + rowId + '"][data-slot-index="' + slotIndex + '"]').addClass('bfmsf-slot-targeted');
        // Show hint
        $('#bfmsf-target-hint').show();
    }

    function clearTargetSlot() {
        targetSlot = null;
        $('.bfmsf-slot').removeClass('bfmsf-slot-targeted');
        $('#bfmsf-target-hint').hide();
    }

    /* ================================================================
       CANVAS RENDERING
       ================================================================ */
    function renderCanvas() {
        var $rows   = $('#bfmsf-form-rows');
        var $empty  = $('#bfmsf-canvas-empty-state');

        $rows.empty();

        // Filter rows belonging to the active step
        var activeRows = formData.rows.filter(function (row) {
            return (row.step || 1) === currentStep;
        });

        if (activeRows.length === 0) {
            $empty.show();
            renderStepsBar();
            return;
        }
        $empty.hide();

        activeRows.forEach(function (row) {
            $rows.append(buildRowElement(row));
        });

        // Re-init droppable for new slots
        initSlotDroppable();

        // Allow placed fields to be dragged between slots
        initCanvasFieldDrag();

        // Allow rows to be reordered by dragging
        initRowSortable();

        // Render steps bar
        renderStepsBar();
    }

    function renderStepsBar() {
        var $list = $('#bfmsf-canvas-steps-list');
        if (!$list.length) return;
        $list.empty();

        // Find the maximum step number currently present in form rows, or at least currentStep
        var maxStep = currentStep;
        formData.rows.forEach(function (row) {
            var s = parseInt(row.step || 1, 10);
            if (s > maxStep) maxStep = s;
        });

        for (var s = 1; s <= maxStep; s++) {
            var activeClass = (s === currentStep) ? ' active' : '';
            var deleteBtn = '';
            // Only allow deleting a step if there is more than 1 step
            if (maxStep > 1) {
                deleteBtn = '<button class="bfmsf-step-delete-btn" data-step="' + s + '" title="Delete this step">×</button>';
            }
            $list.append(
                '<div class="bfmsf-step-tab' + activeClass + '" data-step="' + s + '">' +
                '<span>Step ' + s + '</span>' +
                deleteBtn +
                '</div>'
            );
        }

        // Add step button
        $list.append('<button class="bfmsf-add-step-btn" id="bfmsf-add-step-action-btn">+ Add Step</button>');
    }

    function buildRowElement(row) {
        var cfg    = layoutConfigs[row.layout] || layoutConfigs['2-equal'];
        var label  = cfg.label;
        var slotCount = row.slots.length;

        var slotsHtml = '';
        for (var i = 0; i < slotCount; i++) {
            var flex    = cfg.flexes[i] || 1;
            var fieldId = row.slots[i];
            var slotContent;
            if (fieldId && formData.fieldDefs[fieldId]) {
                slotContent = buildPlacedFieldHtml(formData.fieldDefs[fieldId], fieldId === selectedFieldId);
            } else {
                slotContent = '<div class="bfmsf-drop-zone">Drop field here</div>';
            }
            slotsHtml +=
                '<div class="bfmsf-slot"' +
                ' data-row-id="' + h(row.id) + '"' +
                ' data-slot-index="' + i + '"' +
                ' style="flex:' + flex + ';">' +
                slotContent +
                '</div>';
        }

        return $(
            '<div class="bfmsf-canvas-row" data-row-id="' + h(row.id) + '">' +
            '<div class="bfmsf-row-bar">' +
            '<span class="bfmsf-row-drag-handle" title="Drag to reorder row">⠿⠿</span>' +
            '<span>FILL THE LAYOUT — ' + h(label) + '</span>' +
            '<div class="bfmsf-row-bar-actions">' +
            '<button class="bfmsf-add-field-to-row" data-row-id="' + h(row.id) + '">+ Add Field</button>' +
            '<button class="bfmsf-remove-row-btn" data-row-id="' + h(row.id) + '" title="Remove row">×</button>' +
            '</div></div>' +
            '<div class="bfmsf-row-slots">' + slotsHtml + '</div>' +
            '</div>'
        );
    }

    function buildPlacedFieldHtml(field, isActive) {
        var activeClass = isActive ? ' bfmsf-field-active' : '';
        var input = buildMiniInputHtml(field);
        return (
            '<div class="bfmsf-placed-field' + activeClass + '" data-field-id="' + h(field.id) + '">' +
            '<div class="bfmsf-placed-field-bar">' +
            '<span class="bfmsf-field-drag-handle" title="Drag to move">⠿</span>' +
            '<span class="bfmsf-placed-field-label">' + h(field.label) + (field.required ? ' <span style="color:#ef4444;">*</span>' : '') + '</span>' +
            '<div class="bfmsf-placed-field-actions">' +
            '<button class="bfmsf-placed-action-btn bfmsf-configure-btn" title="Configure">⚙</button>' +
            '<button class="bfmsf-placed-action-btn bfmsf-unplace-btn" title="Remove">×</button>' +
            '</div></div>' +
            input +
            '</div>'
        );
    }

    function buildMiniInputHtml(field) {
        var ph  = h(field.placeholder);
        var opts = field.options || [];

        switch (field.type) {
            case 'textarea':
                return '<textarea class="bfmsf-placed-input bfmsf-placed-textarea" placeholder="' + ph + '" readonly></textarea>';
            case 'dropdown':
                var ohtml = '<option>' + ph + '</option>';
                opts.forEach(function(o) { ohtml += '<option>' + h(o) + '</option>'; });
                return '<select class="bfmsf-placed-input bfmsf-placed-select" disabled>' + ohtml + '</select>';
            case 'checkboxes':
                return (opts.length ? opts : ['Option']).map(function(o) {
                    return '<div class="bfmsf-mini-check"><input type="checkbox" disabled> ' + h(o) + '</div>';
                }).join('');
            case 'radio':
                return (opts.length ? opts : ['Option']).map(function(o) {
                    return '<div class="bfmsf-mini-check"><input type="radio" disabled> ' + h(o) + '</div>';
                }).join('');
            case 'checkbox':
                return '<div class="bfmsf-mini-check"><input type="checkbox" disabled> ' + (ph || h(field.label)) + '</div>';
            case 'image':
                return '<div class="bfmsf-placed-file-drop">📁 Upload image</div>';
            case 'date':
                return '<input type="date" class="bfmsf-placed-input" readonly>';
            default:
                var itype = ({ name:'text',email:'email',phone:'tel',url:'url',number:'number' })[field.type] || 'text';
                return '<input type="' + itype + '" class="bfmsf-placed-input" placeholder="' + ph + '" readonly>';
        }
    }

    /* ================================================================
       LAYOUT ROWS LIST (sidebar)
       ================================================================ */
    function renderLayoutRowsList() {
        var $list = $('#bfmsf-layout-rows-list');
        var $hint = $('#bfmsf-no-rows-hint');
        $list.empty();

        if (formData.rows.length === 0) {
            $list.append($hint.show());
            return;
        }
        $hint.hide();

        // Sort rows by step for clean display
        var sortedRows = formData.rows.slice().sort(function(a, b) {
            return (a.step || 1) - (b.step || 1);
        });

        sortedRows.forEach(function (row) {
            var cfg   = layoutConfigs[row.layout] || layoutConfigs['2-equal'];
            var filled = row.slots.filter(function(s) { return s !== null; }).length;
            var stepNo = row.step || 1;
            $list.append(
                '<div class="bfmsf-row-list-item">' +
                '<span>Step ' + stepNo + ' · ' + cfg.slots + ' col' + (cfg.slots>1?'s':'') + ' — ' + filled + ' field' + (filled!==1?'s':'') + '</span>' +
                '<button class="bfmsf-row-list-delete" data-row-id="' + h(row.id) + '" title="Delete row">×</button>' +
                '</div>'
            );
        });
    }

    /* ================================================================
       FIELD SELECTION → OPTIONS / CONDITIONS PANELS
       ================================================================ */
    function selectField(fieldId) {
        var field = getFieldDef(fieldId);
        if (!field) return;

        selectedFieldId = fieldId;

        // Highlight in canvas
        $('.bfmsf-placed-field').removeClass('bfmsf-field-active');
        $('.bfmsf-placed-field[data-field-id="' + fieldId + '"]').addClass('bfmsf-field-active');

        renderOptionsPanel(field);
        renderConditionsPanel(field);
        switchSidebarPanel('options');
    }

    
    function renderOptionsPanel(field) {
    var $panel = $('#panel-options');
    if (!field) {
        $panel.html(
            '<div class="bfmsf-options-empty">' +
            '<span class="dashicons dashicons-forms" style="font-size:32px;color:#d1d5db;display:block;margin-bottom:10px;"></span>' +
            '<p>Click a field in the canvas to configure its options.</p></div>'
        );
        return;
    }

    var schema = getFieldOptionsSchema(field.type);
    var sectionsHtml = '';

    schema.sections.forEach(function (section, idx) {
        var controlsHtml = '';
        section.controls.forEach(function (ctrlKey) {
            controlsHtml += buildOptionControlHtml(ctrlKey, field);
        });
        var openClass = idx === 0 ? ' open' : '';
        var chevron = idx === 0 ? '∧' : '∨';
        sectionsHtml +=
            '<div class="bfmsf-accordion-item' + openClass + '">' +
            '<div class="bfmsf-accordion-header">' + section.title + ' <span class="bfmsf-accordion-chevron">' + chevron + '</span></div>' +
            '<div class="bfmsf-accordion-body">' + controlsHtml + '</div>' +
            '</div>';
    });

    var html =
        '<p style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin:0 0 12px;">' +
        'Editing: <span style="color:#4361ee;">' + h(field.label) + '</span></p>' +
        '<div class="bfmsf-accordion">' + sectionsHtml + '</div>' +
        '<button id="bfmsf-save-field-btn" class="bfmsf-save-field-btn">Save Changes</button>';

    $panel.html(html);
}

function buildOptionControlHtml(ctrlKey, field) {
    var ctrl = optionControls[ctrlKey];
    if (!ctrl) return '';

    var val = field[ctrlKey] !== undefined ? field[ctrlKey] : (ctrl.default !== undefined ? ctrl.default : '');
    var inputId = 'bfmsf-opt-' + ctrlKey;
    var html = '<div class="bfmsf-form-group"><label for="' + inputId + '">' + ctrl.label + '</label>';

    if (ctrl.type === 'checkbox') {
        var checked = val ? 'checked' : '';
        html += '<input type="checkbox" id="' + inputId + '" ' + checked + '>';
    } else if (ctrl.type === 'textarea') {
        var rows = ctrl.rows || 4;
        html += '<textarea id="' + inputId + '" rows="' + rows + '">' + h(val) + '</textarea>';
    } else if (ctrl.type === 'select') {
        html += '<select id="' + inputId + '">';
        ctrl.choices.forEach(function (c) {
            html += '<option value="' + c.value + '"' + (c.value == val ? ' selected' : '') + '>' + c.label + '</option>';
        });
        html += '</select>';
    } else if (ctrl.type === 'note') {
        html += '<p class="bfmsf-hint">' + ctrl.label + '</p>';
    } else {
        var placeholder = ctrl.placeholder ? ' placeholder="' + h(ctrl.placeholder) + '"' : '';
        html += '<input type="' + (ctrl.type === 'number' ? 'number' : 'text') + '" id="' + inputId + '" value="' + h(val) + '"' + placeholder + '>';
    }
    html += '</div>';
    return html;
}

function buildOptionControlHtml(ctrlKey, field) {
    var ctrl = optionControls[ctrlKey];
    if (!ctrl) return '';

    var val = field[ctrlKey] !== undefined ? field[ctrlKey] : (ctrl.default !== undefined ? ctrl.default : '');
    var inputId = 'bfmsf-opt-' + ctrlKey;
    var html = '<div class="bfmsf-form-group"><label for="' + inputId + '">' + ctrl.label + '</label>';

    if (ctrl.type === 'checkbox') {
        var checked = val ? 'checked' : '';
        html += '<input type="checkbox" id="' + inputId + '" ' + checked + '>';
    } else if (ctrl.type === 'textarea') {
        var rows = ctrl.rows || 4;
        html += '<textarea id="' + inputId + '" rows="' + rows + '">' + h(val) + '</textarea>';
    } else if (ctrl.type === 'select') {
        html += '<select id="' + inputId + '">';
        ctrl.choices.forEach(function (c) {
            html += '<option value="' + c.value + '"' + (c.value == val ? ' selected' : '') + '>' + c.label + '</option>';
        });
        html += '</select>';
    } else if (ctrl.type === 'note') {
        html += '<p class="bfmsf-hint">' + ctrl.label + '</p>';
    } else {
        var placeholder = ctrl.placeholder ? ' placeholder="' + h(ctrl.placeholder) + '"' : '';
        html += '<input type="' + (ctrl.type === 'number' ? 'number' : 'text') + '" id="' + inputId + '" value="' + h(val) + '"' + placeholder + '>';
    }
    html += '</div>';
    return html;
}

    function saveFieldChanges() {
        var field = getFieldDef(selectedFieldId);
        if (!field) return;

        field.label       = $('#bfmsf-opt-label').val();
        field.placeholder = $('#bfmsf-opt-placeholder').val();
        field.description = $('#bfmsf-opt-description').val();
        field.required    = $('#bfmsf-opt-required').is(':checked');
        field.cssClass   = $('#bfmsf-opt-cssClass').val();
        // field.customCss  = $('#bfmsf-opt-customCss').val();

        // In saveFieldChangesSilent and saveFieldChanges, after reading other fields:
field.confirmationText = $('#bfmsf-opt-confirmationText').val();
field.defaultChecked   = $('#bfmsf-opt-defaultChecked').is(':checked');

        var $opts = $('#bfmsf-opt-options');
        if ($opts.length) {
            field.options = $opts.val().split('\n').map(function(s){ return s.trim(); }).filter(Boolean);
        }

        field.validation = {
            min:   $('#bfmsf-opt-min').val(),
            max:   $('#bfmsf-opt-max').val(),
            error: $('#bfmsf-opt-error').val(),
        };

        formData.fieldDefs[field.id] = field;
        renderCanvas();
        renderOptionsPanel(field);
        // Re-apply active state
        $('.bfmsf-placed-field[data-field-id="' + field.id + '"]').addClass('bfmsf-field-active');
        showNotice('Field updated! ✓ (Click UPDATE in header to save form)', 'success');
    }

    // Silent version: saves without re-rendering the panel (preserves focus/cursor)
    function saveFieldChangesSilent() {
        var field = getFieldDef(selectedFieldId);
        if (!field) return;

        field.label       = $('#bfmsf-opt-label').val();
        field.placeholder = $('#bfmsf-opt-placeholder').val();
        field.description = $('#bfmsf-opt-description').val();
        field.required    = $('#bfmsf-opt-required').is(':checked');
        field.cssClass   = $('#bfmsf-opt-cssClass').val();
        // field.customCss  = $('#bfmsf-opt-customCss').val();

        field.confirmationText = $('#bfmsf-opt-confirmationText').val();

        field.defaultChecked = $('#bfmsf-opt-defaultChecked').is(':checked');


        // For options list (if present)
    var $opts = $('#bfmsf-opt-options');
    if ($opts.length) {
        field.options = $opts.val().split('\n').map(function(s){ return s.trim(); }).filter(Boolean);
    }

    // For validation fields
    field.validation = {
        min:   $('#bfmsf-opt-min').val(),
        max:   $('#bfmsf-opt-max').val(),
        error: $('#bfmsf-opt-error').val(),
    };

       

        formData.fieldDefs[field.id] = field;

        // Update only the canvas field label/required indicator without full re-render
        var $pf = $('.bfmsf-placed-field[data-field-id="' + field.id + '"]');
        $pf.find('.bfmsf-placed-field-label').html(
            h(field.label) + (field.required ? ' <span style="color:#ef4444;">*</span>' : '')
        );
    }

    /* ================================================================
       CONDITIONS PANEL
       ================================================================ */
    function renderConditionsPanel(field) {
        var $panel = $('#panel-conditions');
        if (!field) {
            $panel.html(
                '<div class="bfmsf-options-empty">' +
                '<span class="dashicons dashicons-visibility" style="font-size:32px;color:#d1d5db;display:block;margin-bottom:10px;"></span>' +
                '<p>Select a field to set conditional logic rules.</p></div>'
            );
            return;
        }

        // Build other field options
        var otherFieldOpts = '<option value="">-- None --</option>';
        Object.keys(formData.fieldDefs).forEach(function (fid) {
            if (fid === field.id) return;
            var f = formData.fieldDefs[fid];
            var sel = (field.conditions && field.conditions[0] && field.conditions[0].fieldId === fid) ? ' selected' : '';
            otherFieldOpts += '<option value="' + h(fid) + '"' + sel + '>' + h(f.label) + '</option>';
        });

        var condValue = (field.conditions && field.conditions[0]) ? field.conditions[0].value : '';

        var html =
            '<p style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin:0 0 12px;">' +
            'Conditions: <span style="color:#4361ee;">' + h(field.label) + '</span></p>' +
            '<p class="bfmsf-hint" style="margin-bottom:12px;">Show this field only when:</p>' +
            '<div class="bfmsf-form-group"><label>Field</label><select id="bfmsf-cond-field">' + otherFieldOpts + '</select></div>' +
            '<div class="bfmsf-form-group"><label>Operator</label>' +
            '<select id="bfmsf-cond-operator">' +
            '<option value="equals">Equals</option>' +
            '<option value="not_equals">Does not equal</option>' +
            '<option value="contains">Contains</option>' +
            '<option value="not_empty">Is not empty</option>' +
            '</select></div>' +
            '<div class="bfmsf-form-group"><label>Value</label><input type="text" id="bfmsf-cond-value" value="' + h(condValue) + '" placeholder="Enter value"></div>' +
            '<button id="bfmsf-save-cond-btn" class="bfmsf-save-field-btn">Save Conditions</button>';

        $panel.html(html);
    }

    function saveFieldConditions() {
        var field = getFieldDef(selectedFieldId);
        if (!field) return;
        var condFieldId = $('#bfmsf-cond-field').val();
        var operator    = $('#bfmsf-cond-operator').val();
        var value       = $('#bfmsf-cond-value').val();
        field.conditions = condFieldId ? [{ fieldId: condFieldId, operator: operator, value: value }] : [];
        showNotice('Conditions saved! ✓ (Click UPDATE in header to save form)', 'success');
    }

    /* ================================================================
       DRAG & DROP (field cards → slots or canvas)
       ================================================================ */
    function initDragDrop() {
        // Make field cards draggable
        $(document).on('mouseenter', '.bfmsf-field-type-card:not(.ui-draggable)', function () {
            $(this).draggable({
                helper: function () {
                    var type  = $(this).data('type');
                    var label = fieldTypes[type] ? fieldTypes[type].label : type;
                    return $('<div class="bfmsf-drag-ghost">' + h(label) + '</div>');
                },
                revert: 'invalid',
                cursor: 'grabbing',
                opacity: 0.85,
                zIndex: 9999,
                start: function () {
                    // Show drop-zone hints on all empty slots and make canvas droppable
                    $('.bfmsf-drop-zone').addClass('bfmsf-drop-zone-ready');
                    $('#bfmsf-form-rows').addClass('bfmsf-canvas-drop-ready');
                },
                stop: function () {
                    $('.bfmsf-drop-zone').removeClass('bfmsf-drop-zone-ready');
                    $('#bfmsf-form-rows').removeClass('bfmsf-canvas-drop-ready');
                }
            });
        });

        // Make the canvas itself droppable so dragging a card onto the canvas
        // (not a specific slot) auto-creates a full-width row for the field.
        $(document).on('mouseenter', '#bfmsf-form-rows:not(.ui-droppable)', function () {
            $(this).droppable({
                accept: '.bfmsf-field-type-card',
                greedy: false, // slots take priority (they are inside this)
                hoverClass: 'bfmsf-canvas-drag-over',
                drop: function (event, ui) {
                    // Only fires if no slot caught it
                    var type = ui.draggable.data('type');
                    addFieldAsNewRow(type);
                }
            });
        });
    }

    function initSlotDroppable() {
        $('.bfmsf-slot').not('.ui-droppable').droppable({
            accept: '.bfmsf-field-type-card, .bfmsf-placed-field',
            hoverClass: 'bfmsf-drag-over',
            greedy: true,
            drop: function (event, ui) {
                var $target   = $(this);
                var rowId     = $target.data('row-id');
                var slotIndex = parseInt($target.data('slot-index'), 10);

                // Dropped from sidebar field card
                if (ui.draggable.hasClass('bfmsf-field-type-card')) {
                    var type = ui.draggable.data('type');
                    addFieldToSlot(type, rowId, slotIndex);
                    return;
                }

                // Dropped from canvas placed field → swap/move
                if (ui.draggable.hasClass('bfmsf-placed-field')) {
                    var draggedFieldId = ui.draggable.data('field-id');
                    var $srcSlot       = ui.draggable.closest('.bfmsf-slot');
                    var srcRowId       = $srcSlot.data('row-id');
                    var srcSlotIndex   = parseInt($srcSlot.data('slot-index'), 10);

                    if (srcRowId === rowId && srcSlotIndex === slotIndex) return; // same slot

                    var srcRow = getRow(srcRowId);
                    var dstRow = getRow(rowId);
                    if (!srcRow || !dstRow) return;

                    var dstFieldId = dstRow.slots[slotIndex]; // may be null

                    // Swap: put dragged field in destination, destination field (if any) in source
                    dstRow.slots[slotIndex] = draggedFieldId;
                    srcRow.slots[srcSlotIndex] = dstFieldId || null;

                    renderCanvas();
                    renderLayoutRowsList();
                    if (selectedFieldId === draggedFieldId) {
                        selectField(draggedFieldId);
                    }
                }
            }
        });
    }

    /* ── Drag placed fields between slots on the canvas ── */
    function initCanvasFieldDrag() {
        $('.bfmsf-placed-field').not('.ui-draggable').draggable({
            handle:     '.bfmsf-field-drag-handle',
            helper:     'clone',
            revert:     'invalid',
            cursor:     'grabbing',
            opacity:    0.75,
            zIndex:     9998,
            start: function () {
                $(this).addClass('bfmsf-dragging');
                // Light up all slots as drop targets
                $('.bfmsf-slot').addClass('bfmsf-slot-drop-hint');
            },
            stop: function () {
                $(this).removeClass('bfmsf-dragging');
                $('.bfmsf-slot').removeClass('bfmsf-slot-drop-hint');
            }
        });
    }

    /* ── Drag rows to reorder them within the same step ── */
    function initRowSortable() {
        var $rowsContainer = $('#bfmsf-form-rows');
        if ($rowsContainer.hasClass('ui-sortable')) {
            $rowsContainer.sortable('destroy');
        }
        $rowsContainer.sortable({
            items:      '> .bfmsf-canvas-row',
            handle:     '.bfmsf-row-drag-handle',
            axis:       'y',
            cursor:     'grabbing',
            opacity:    0.8,
            placeholder:'bfmsf-row-sort-placeholder',
            tolerance:  'pointer',
            update: function () {
                // Sync formData.rows order to the new DOM order, keeping only current-step rows
                var newOrder = [];
                $rowsContainer.find('> .bfmsf-canvas-row').each(function () {
                    var rid = $(this).data('row-id');
                    var row = getRow(rid);
                    if (row) newOrder.push(row);
                });
                // Keep rows from other steps unchanged, append current-step rows in new order
                var otherRows = formData.rows.filter(function (r) {
                    return (r.step || 1) !== currentStep;
                });
                formData.rows = otherRows.concat(newOrder);
                renderLayoutRowsList();
            }
        });
    }

    /* ================================================================
       SETTINGS FORM — populate & collect
       ================================================================ */
    function populateSettingsForm() {
        var s = formSettings;
        if (!s) return;
        safeVal('#bfmsf-setting-form-name', s.form_name);
        safeVal('#bfmsf-setting-description', s.description);
        safeVal('#bfmsf-setting-confirmation-type', s.confirmation_type);
        safeVal('#bfmsf-setting-message', s.confirmation_message);
        safeVal('#bfmsf-setting-redirect', s.redirect_url);
        safeVal('#bfmsf-setting-email-recipient', s.email_recipient);
        safeVal('#bfmsf-setting-email-subject', s.email_subject);
        safeVal('#bfmsf-setting-from-name', s.email_from_name);
        safeVal('#bfmsf-setting-submission-limit', s.submission_limit);
        safeVal('#bfmsf-setting-api-token', s.api_token);
        safeVal('#bfmsf-setting-api-endpoint', s.api_endpoint);
        safeVal('#bfmsf-setting-hcaptcha-site-key', s.hcaptcha_site_key);
        safeVal('#bfmsf-setting-hcaptcha-secret', s.hcaptcha_secret);
        safeVal('#bfmsf-setting-recaptcha-site-key', s.recaptcha_site_key);
        safeVal('#bfmsf-setting-recaptcha-secret', s.recaptcha_secret);
        safeVal('#bfmsf-setting-turnstile-site-key', s.turnstile_site_key);
        safeVal('#bfmsf-setting-turnstile-secret', s.turnstile_secret);
        if (s.status) $('input[name="bfmsf-form-status"][value="' + s.status + '"]').prop('checked', true);
        safeProp('#bfmsf-setting-require-login', s.require_login);
        safeProp('#bfmsf-setting-google-sheets', s.integration_google_sheets);
        safeProp('#bfmsf-setting-zapier', s.integration_zapier);
        safeProp('#bfmsf-setting-hubspot', s.integration_hubspot);
        safeVal('#bfmsf-setting-google-sheets-webhook', s.google_sheets_webhook_url);
        safeVal('#bfmsf-setting-zapier-webhook', s.zapier_webhook_url);
        safeVal('#bfmsf-setting-hubspot-webhook', s.hubspot_webhook_url);
        $('#bfmsf-google-sheets-webhook-row').toggle(!!s.integration_google_sheets);
        $('#bfmsf-zapier-webhook-row').toggle(!!s.integration_zapier);
        $('#bfmsf-hubspot-webhook-row').toggle(!!s.integration_hubspot);
    }

    function collectSettings() {
        return {
            form_name:             $('#bfmsf-setting-form-name').val(),
            description:           $('#bfmsf-setting-description').val(),
            status:                $('input[name="bfmsf-form-status"]:checked').val() || 'active',
            confirmation_type:     $('#bfmsf-setting-confirmation-type').val(),
            confirmation_message:  $('#bfmsf-setting-message').val(),
            redirect_url:          $('#bfmsf-setting-redirect').val(),
            email_recipient:       $('#bfmsf-setting-email-recipient').val(),
            email_subject:         $('#bfmsf-setting-email-subject').val(),
            email_from_name:       $('#bfmsf-setting-from-name').val(),
            submission_limit:      $('#bfmsf-setting-submission-limit').val(),
            require_login:         $('#bfmsf-setting-require-login').is(':checked'),
            integration_google_sheets: $('#bfmsf-setting-google-sheets').is(':checked'),
            integration_zapier:    $('#bfmsf-setting-zapier').is(':checked'),
            integration_hubspot:   $('#bfmsf-setting-hubspot').is(':checked'),
            google_sheets_webhook_url: $('#bfmsf-setting-google-sheets-webhook').val(),
            zapier_webhook_url:        $('#bfmsf-setting-zapier-webhook').val(),
            hubspot_webhook_url:       $('#bfmsf-setting-hubspot-webhook').val(),
            api_token:             $('#bfmsf-setting-api-token').val(),
            api_endpoint:          $('#bfmsf-setting-api-endpoint').val(),
            hcaptcha_site_key:     $('#bfmsf-setting-hcaptcha-site-key').val(),
            hcaptcha_secret:       $('#bfmsf-setting-hcaptcha-secret').val(),
            recaptcha_site_key:    $('#bfmsf-setting-recaptcha-site-key').val(),
            recaptcha_secret:      $('#bfmsf-setting-recaptcha-secret').val(),
            turnstile_site_key:    $('#bfmsf-setting-turnstile-site-key').val(),
            turnstile_secret:      $('#bfmsf-setting-turnstile-secret').val(),
        };
    }

    /* ================================================================
       STYLE FORM — populate & collect
       ================================================================ */
    function populateStyleForm() {
        var st = formStyle;
        if (!st) return;

        /* ── Restore Container Border Color ── */
        var cbc = st.container_border_color || '#e2e8f0';
        $('#bfmsf-container-border-color').val(cbc);
        $('#bfmsf-container-border-color-hex').val(cbc.replace('#',''));

        safeSlider('#bfmsf-heading-size', st.heading_size, 24);
        safeSlider('#bfmsf-body-size', st.body_font_size, 14);
        safeSlider('#bfmsf-font-weight', Math.round((st.font_weight||400)/100), 4);
        safeSlider('#bfmsf-border-radius', st.border_radius, 6);
        safeSlider('#bfmsf-padding', st.padding, 10);
        safeSlider('#bfmsf-field-spacing', st.field_spacing, 16);

        /* ── Restore Primary Color + Highlight Matching Swatch ── */
        var pc = st.primary_color || '#4361ee';
        $('#bfmsf-primary-color').val(pc);
        $('#bfmsf-hex-input').val(pc.replace('#',''));
        $('.bfmsf-color-swatch').each(function () {
            if ($(this).data('color') === pc) { $(this).addClass('active'); }
            else { $(this).removeClass('active'); }
        });

        /* ── Restore Text Color ── */
        var tc = st.text_color || '#1f2937';
        $('#bfmsf-text-color').val(tc);
        $('#bfmsf-text-color-hex').val(tc.replace('#',''));

        /* ── Restore Button Colors + Live Previews ── */
        var bbc = st.button_bg_color || '#4361ee';
        $('#bfmsf-btn-bg-color').val(bbc);
        $('#bfmsf-btn-preview').css('background', bbc);

        var btc = st.button_text_color || '#ffffff';
        $('#bfmsf-btn-text-color').val(btc);
        $('#bfmsf-btn-text-preview').css('background', btc);

        safeVal('#bfmsf-btn-text', st.button_text);
        safeProp('#bfmsf-btn-hover', st.button_hover);
        safeVal('#bfmsf-bg-image', st.bg_image);
        safeVal('#bfmsf-bg-size', st.bg_size);
        safeVal('#bfmsf-primary-font', st.primary_font);

        /* ── Restore Active Theme Thumb ── */
        var savedTheme = st.theme || 'default';
        $('.bfmsf-theme-thumb').removeClass('active');
        $('.bfmsf-theme-thumb[data-theme="' + savedTheme + '"]').addClass('active');

        if (st.input_style) {
            $('.bfmsf-style-option[data-group="input-style"]').removeClass('active');
            $('.bfmsf-style-option[data-group="input-style"][data-value="' + st.input_style + '"]').addClass('active');
        }
        if (st.label_alignment) {
            $('.bfmsf-align-btn').removeClass('active');
            $('.bfmsf-align-btn[data-value="' + st.label_alignment + '"]').addClass('active');
        }
    }

    function collectStyle() {
        return {
            heading_size:   parseInt($('#bfmsf-heading-size').val(),10)   || 24,
            body_font_size: parseInt($('#bfmsf-body-size').val(),10)      || 14,
            font_weight:    (parseInt($('#bfmsf-font-weight').val(),10)||4)*100,
            border_radius:  parseInt($('#bfmsf-border-radius').val(),10)  || 6,
            padding:        parseInt($('#bfmsf-padding').val(),10)         || 10,
            field_spacing:  parseInt($('#bfmsf-field-spacing').val(),10)  || 16,
            primary_color:      $('#bfmsf-primary-color').val()               || '#4361ee',
            text_color:         $('#bfmsf-text-color').val()                  || '#1f2937',
            button_bg_color:    $('#bfmsf-btn-bg-color').val()               || '#4361ee',
            button_text_color:  $('#bfmsf-btn-text-color').val()             || '#ffffff',
            button_text:    $('#bfmsf-btn-text').val()                    || 'Submit',
            button_hover:   $('#bfmsf-btn-hover').is(':checked'),
            input_style:    $('.bfmsf-style-option[data-group="input-style"].active').data('value') || 'outlined',
            label_alignment:$('.bfmsf-align-btn.active').data('value')  || 'top',
            primary_font:   $('#bfmsf-primary-font').val()               || 'Inter',
            bg_image:       $('#bfmsf-bg-image').val(),
            bg_size:        $('#bfmsf-bg-size').val(),
            theme:          $('.bfmsf-theme-thumb.active').data('theme') || 'default',
            border_color:   $('#bfmsf-border-color').val() || '#d1d5db',
            container_border_color: $('#bfmsf-container-border-color').val() || '#e2e8f0',
        };
    }

    /* ================================================================
       AJAX SAVE
       ================================================================ */
    // Global lock to prevent concurrent saves
    var bfmsf_is_saving = false;

    function saveBuilder($btn) {
        // If already saving, ignore this call
        if (bfmsf_is_saving) {
            showNotice('Save already in progress...', 'info');
            return;
        }

        bfmsf_is_saving = true;

        var origHtml = $btn ? $btn.html() : '';
        if ($btn) {
            $btn.html('Saving...').prop('disabled', true);
        }

        var canvasTitle    = $('#bfmsf-canvas-title').text().trim()    || '';
        var canvasSubtitle = $('#bfmsf-canvas-subtitle').text().trim() || '';
        var formTitle      = canvasTitle || 'Untitled Form';

        var normalizedRows = normalizeRows(formData.rows);
        var normalizedFieldDefs = normalizeFieldDefs(formData.fieldDefs);

        $.ajax({
            url:    BFMSF_admin.ajax_url,
            method: 'POST',
            data: {
                action:          'bfmsf_save_builder',
                nonce:           BFMSF_admin.nonce,
                form_id:         BFMSF_admin.form_id,
                form_title:      formTitle,
                canvas_title:    canvasTitle,
                canvas_subtitle: canvasSubtitle,
                rows:            JSON.stringify(normalizedRows),
                field_defs:      JSON.stringify(normalizedFieldDefs),
                settings:        JSON.stringify(collectSettings()),
                style:           JSON.stringify(collectStyle()),
            },
            success: function (res) {
                if (res.success) {
                    var newId = res.data && res.data.form_id;
                    if (newId) {
                        // Always update the form ID
                        BFMSF_admin.form_id = newId;
                        history.replaceState({}, '', '?page=bfmsf-builder&form_id=' + newId);
                    }
                    $('#bfmsf-brand-name').text(formTitle);
                    showNotice('Form saved successfully! ✓', 'success');
                } else {
                    var msg = res.data && res.data.message ? res.data.message : 'Unknown error';
                    showNotice('Error: ' + msg, 'error');
                }
            },
            error: function () {
                showNotice('Network error. Try again.', 'error');
            },
            complete: function () {
                // Release the lock and restore button state
                bfmsf_is_saving = false;
                if ($btn) {
                    $btn.html(origHtml).prop('disabled', false);
                }
            }
        });
    }

    /* ================================================================
       MODALS
       ================================================================ */
    function showFormPreviewModal(formHtml, frontendCss) {
        // Inject frontend CSS once into the page (scoped to the preview wrap)
        var styleId = 'bfmsf-preview-frontend-css';
        if (!document.getElementById(styleId) && frontendCss) {
            // Scope every rule inside .bfmsf-preview-form-wrap so admin styles are unaffected
            var scoped = frontendCss.replace(/(^|\})\s*([^@\{\/][^\{]*)\{/g, function(match, closing, selector) {
                var selectors = selector.split(',').map(function(s) {
                    return '.bfmsf-preview-form-wrap ' + s.trim();
                });
                return (closing || '') + '\n' + selectors.join(', ') + ' {';
            });
            var $style = $('<style id="' + styleId + '">').text(scoped);
            $('head').append($style);
        }

        $('body').append(
            '<div class="bfmsf-modal-overlay bfmsf-preview-overlay">' +
            '<div class="bfmsf-modal bfmsf-preview-modal" style="width:680px;max-width:95vw;max-height:88vh;display:flex;flex-direction:column;padding:0;overflow:hidden;">' +
            '<div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e2e8f0;flex-shrink:0;">' +
            '<h3 style="margin:0;font-size:15px;font-weight:700;color:#1a202c;">&#128065; Form Preview</h3>' +
            '<div style="display:flex;align-items:center;gap:10px;">' +
            '<span style="font-size:11px;color:#94a3b8;background:#f1f5f9;padding:3px 8px;border-radius:20px;">Live Preview</span>' +
            '<button class="bfmsf-modal-close" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;padding:2px 6px;">&#10005;</button>' +
            '</div></div>' +
            '<div class="bfmsf-preview-form-wrap" style="overflow-y:auto;flex:1;">' + formHtml + '</div>' +
            '</div></div>'
        );

        // Re-initialize frontend step navigation scoped to this modal
        var $modal = $('.bfmsf-preview-overlay');
        var $wrap  = $modal.find('.bfmsf-preview-form-wrap');

        (function initPreviewForm($wrap) {
            var $steps   = $wrap.find('.bfmsf-form-step');
            var maxSteps = $steps.length;
            if (maxSteps === 0) return;
            var currentStep = 1;

            function showStep(step) {
                $steps.hide();
                $wrap.find('.bfmsf-form-step[data-step="' + step + '"]').show();
                $wrap.find('.bfmsf-prev-btn').toggle(step > 1);
                $wrap.find('.bfmsf-next-btn').toggle(step < maxSteps);
                $wrap.find('.bfmsf-submit-btn').toggle(step === maxSteps);
                var pct = (step / maxSteps) * 100;
                $wrap.find('.bfmsf-progress-fill').css('width', pct + '%');
            }

            function validateStep() {
                var $cur = $wrap.find('.bfmsf-form-step[data-step="' + currentStep + '"]');
                var valid = true;
                $cur.find('input[required], select[required], textarea[required]').each(function () {
                    var $i = $(this);
                    var empty = ($i.is('[type=checkbox],[type=radio]'))
                        ? $wrap.find('input[name="' + $i.attr('name') + '"]:checked').length === 0
                        : !$i.val();
                    if (empty) { valid = false; $i.addClass('error'); }
                    else $i.removeClass('error');
                });
                return valid;
            }

            $wrap.on('click', '.bfmsf-next-btn', function (e) {
                e.preventDefault();
                if (!validateStep()) { alert('Please fill in all required fields.'); return; }
                if (currentStep < maxSteps) { currentStep++; showStep(currentStep); }
            });
            $wrap.on('click', '.bfmsf-prev-btn', function (e) {
                e.preventDefault();
                if (currentStep > 1) { currentStep--; showStep(currentStep); }
            });
            $wrap.on('submit', '.bfmsf-form', function (e) {
                e.preventDefault();
                alert('Preview mode — form submission is disabled.');
            });

            showStep(1);
        })($wrap);
    }

    function showEmbedModal() {
        var fid = BFMSF_admin.form_id;
        if (!fid) {
            showNotice('Please save the form first to generate the shortcode.', 'info');
            return;
        }
        var sc = '[bfmsf_form id="' + fid + '"]';
        $('body').append(
            '<div class="bfmsf-modal-overlay">' +
            '<div class="bfmsf-modal">' +
            '<h3>📋 Embed Your Form</h3>' +
            '<p>Copy this shortcode and paste it on any page:</p>' +
            '<input class="bfmsf-modal-shortcode" type="text" value="' + h(sc) + '" readonly onclick="this.select();">' +
            '<div class="bfmsf-modal-actions">' +
            '<button class="bfmsf-modal-copy">Copy Shortcode</button>' +
            '<button class="bfmsf-modal-close">Close</button>' +
            '</div></div></div>'
        );
        $(document).one('click', '.bfmsf-modal-copy', function () {
            copyToClipboard(sc);
            $(this).text('Copied! ✓');
        });
    }

    function showEntryModal(entryData, entryId) {
        var data = entryData;
        if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e) { data = {}; } }
        var rows = '';
        if (data && typeof data === 'object') {
            Object.keys(data).forEach(function (k) {
                var label = k;
                if (formData.fieldDefs[k] && formData.fieldDefs[k].label) {
                    label = formData.fieldDefs[k].label;
                }
                var v = Array.isArray(data[k]) ? data[k].join(', ') : data[k];
                rows += '<tr><td style="padding:7px 12px;font-weight:600;color:#475569;border-bottom:1px solid #f1f5f9;white-space:nowrap;">' + h(label) + '</td>' +
                        '<td style="padding:7px 12px;color:#374151;border-bottom:1px solid #f1f5f9;">' + h(String(v)) + '</td></tr>';
            });
        }
        $('body').append(
            '<div class="bfmsf-modal-overlay"><div class="bfmsf-modal" style="min-width:480px;">' +
            '<h3>Entry #' + entryId + '</h3>' +
            '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;">' + rows + '</table></div>' +
            '<div class="bfmsf-modal-actions" style="margin-top:18px;"><button class="bfmsf-modal-close">Close</button></div>' +
            '</div></div>'
        );
    }

    /* ================================================================
       NOTICE / TOAST
       ================================================================ */
    function showNotice(message, type) {
        var icons = { success: '✓', error: '✕', info: 'ℹ' };
        var $n = $('<div class="bfmsf-notice ' + (type||'info') + '"><span>' + (icons[type]||'ℹ') + '</span>' + message + '</div>');
        $('body').append($n);
        setTimeout(function () { $n.css({ opacity: 0, transition: 'opacity 0.35s' }); setTimeout(function () { $n.remove(); }, 380); }, 3000);
    }

    /* ================================================================
       UTILITIES
       ================================================================ */
    function h(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function safeVal(sel, val) { if (val !== undefined && val !== null) $(sel).val(val); }
    function safeProp(sel, val) { $(sel).prop('checked', !!val); }
    function safeSlider(sel, val, def) {
        var v = (val !== undefined && val !== null) ? val : def;
        $(sel).val(v);
        $(sel).closest('.bfmsf-range-row').find('.bfmsf-range-value').text(v);
    }
    function setContentEditable(sel, text) {
        var $el = $(sel);
        if ($el.length) $el.text(text);
    }
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            var $t = $('<textarea>').val(text).appendTo('body').select();
            document.execCommand('copy');
            $t.remove();
        }
    }

    /* ================================================================
       SIDEBAR RESIZER (Issue 1)
       ================================================================ */
    function initSidebarResizer() {
        var $resizer = $('#bfmsf-sidebar-resizer');
        var $sidebar = $('#bfmsf-sidebar');
        if (!$resizer.length || !$sidebar.length) return;

        var isResizing = false;
        var startX     = 0;
        var startWidth = 0;

        $resizer.on('mousedown', function (e) {
            isResizing = true;
            startX     = e.clientX;
            startWidth = $sidebar.outerWidth();
            $resizer.addClass('bfmsf-resizing');
            $('body').css({ 'cursor': 'col-resize', 'user-select': 'none' });
            e.preventDefault();
        });

        $(document).on('mousemove.bfmsfresize', function (e) {
            if (!isResizing) return;
            var newWidth = startWidth + (e.clientX - startX);
            newWidth = Math.max(160, Math.min(520, newWidth));
            $sidebar.css('width', newWidth + 'px');
        });

        $(document).on('mouseup.bfmsfresize', function () {
            if (!isResizing) return;
            isResizing = false;
            $resizer.removeClass('bfmsf-resizing');
            $('body').css({ 'cursor': '', 'user-select': '' });
        });
    }

})(jQuery);