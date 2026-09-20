/* ============================================
   USMS Dashboard — Shared JavaScript
   Zero external dependencies
   ============================================ */

/* ---------- MODAL ---------- */
function openModal(id) {
    var el = document.getElementById(id);
    if (el) {
        el.classList.add('show');
        // Initialize any searchable selects inside modal
        if (typeof initSearchableSelects === 'function') {
            initSearchableSelects();
        }
    }
}

function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('show');
}

// Close modal when clicking the dark overlay
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(function(m) {
            m.classList.remove('show');
        });
        // Also close any open searchable dropdowns
        document.querySelectorAll('.searchable-select-dropdown').forEach(function(dd) {
            dd.style.display = 'none';
        });
        document.querySelectorAll('.searchable-select-trigger').forEach(function(tr) {
            tr.classList.remove('active');
        });
    }
});

/* ---------- TAB SWITCHING (in-page) ---------- */
function switchTab(btnEl, groupName, contentId) {
    var btns = btnEl.parentElement.querySelectorAll('.tab-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    btnEl.classList.add('active');

    document.querySelectorAll('[data-tab-group="' + groupName + '"]').forEach(function(panel) {
        panel.classList.remove('active');
    });
    var target = document.getElementById(contentId);
    if (target) target.classList.add('active');
}

/* ============================================
   USMS IN-APP CONFIRMATION POPUP (NO BROWSER ALERTS)
   ============================================ */
window.USMS = window.USMS || {};

USMS.confirm = function(options) {
    return new Promise(function(resolve) {
        options = options || {};
        var title = options.title || 'Confirm Action';
        var message = options.message || 'Are you sure you want to proceed?';
        var confirmText = options.confirmText || 'Yes, Confirm';
        var cancelText = options.cancelText || 'Cancel';
        var type = options.type || 'teal'; // 'danger', 'warning', 'teal', 'primary'

        var iconName = 'help_outline';
        if (type === 'danger') iconName = 'delete';
        else if (type === 'warning') iconName = 'warning';
        else if (type === 'teal' || type === 'success') iconName = 'check_circle';

        // Check if modal already in DOM
        var overlay = document.getElementById('usmsGlobalConfirmModal');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'usmsGlobalConfirmModal';
            overlay.className = 'usms-confirm-overlay';
            document.body.appendChild(overlay);
        }

        var confirmBtnClass = 'btn-teal';
        if (type === 'danger') confirmBtnClass = 'btn-danger';
        else if (type === 'primary') confirmBtnClass = 'btn-primary';

        overlay.innerHTML = 
            '<div class="usms-confirm-card ' + type + '">' +
                '<div class="usms-confirm-header">' +
                    '<div class="usms-confirm-icon ' + type + '">' +
                        '<span class="material-symbols-outlined">' + iconName + '</span>' +
                    '</div>' +
                    '<div class="usms-confirm-title">' + title + '</div>' +
                '</div>' +
                '<div class="usms-confirm-msg">' + message + '</div>' +
                '<div class="usms-confirm-actions">' +
                    '<button type="button" class="btn btn-outline" id="usmsConfirmCancelBtn">' + cancelText + '</button>' +
                    '<button type="button" class="btn ' + confirmBtnClass + '" id="usmsConfirmOkBtn">' + confirmText + '</button>' +
                '</div>' +
            '</div>';

        overlay.classList.add('active');

        function cleanup(confirmed) {
            overlay.classList.remove('active');
            resolve(confirmed);
        }

        document.getElementById('usmsConfirmCancelBtn').onclick = function() {
            cleanup(false);
        };
        document.getElementById('usmsConfirmOkBtn').onclick = function() {
            cleanup(true);
        };
        overlay.onclick = function(e) {
            if (e.target === overlay) cleanup(false);
        };
    });
};

USMS.confirmForm = function() {
    var arg0 = arguments[0];
    var arg1 = arguments[1];
    var arg2 = arguments[2];
    var arg3 = arguments[3];

    var evt = null;
    var targetEl = null;
    var options = {};

    // 1. Identify parameters
    if (arg0 && typeof arg0.preventDefault === 'function') {
        // arg0 is Event
        evt = arg0;
        targetEl = arg1;
        if (typeof arg2 === 'string') {
            options.message = arg2;
            if (typeof arg3 === 'object' && arg3 !== null) {
                Object.assign(options, arg3);
            }
        } else if (typeof arg2 === 'object' && arg2 !== null) {
            Object.assign(options, arg2);
        }
    } else if (arg0 && (arg0.tagName || arg0.nodeType)) {
        // arg0 is DOM Element (form or button)
        targetEl = arg0;
        if (typeof arg1 === 'string') {
            options.message = arg1;
            if (typeof arg2 === 'object' && arg2 !== null) {
                Object.assign(options, arg2);
            }
        } else if (typeof arg1 === 'object' && arg1 !== null) {
            Object.assign(options, arg1);
        }
    } else if (typeof arg0 === 'object' && arg0 !== null) {
        Object.assign(options, arg0);
    }

    if (evt && typeof evt.preventDefault === 'function') {
        evt.preventDefault();
    }

    // Resolve form element
    var form = null;
    if (targetEl) {
        if (targetEl.tagName && targetEl.tagName.toUpperCase() === 'FORM') {
            form = targetEl;
        } else if (typeof targetEl.closest === 'function') {
            form = targetEl.closest('form');
        }
    }

    // If form has already been verified and approved, allow submit
    if (form && form.dataset && form.dataset.usmsConfirmed === 'true') {
        form.dataset.usmsConfirmed = '';
        return true;
    }

    // Options sanitization
    options.title = options.title || 'Confirm Action';
    options.message = options.message || 'Are you sure you want to proceed?';

    var lowerMsg = (options.message + ' ' + (options.title || '')).toLowerCase();
    if (!options.type) {
        if (lowerMsg.includes('delete') || lowerMsg.includes('remove') || lowerMsg.includes('reject') || lowerMsg.includes('cancel') || lowerMsg.includes('clear')) {
            options.type = 'danger';
            options.confirmText = options.confirmText || 'Yes, Delete';
        } else if (lowerMsg.includes('return')) {
            options.type = 'teal';
            options.confirmText = options.confirmText || 'Yes, Mark Returned';
        } else if (lowerMsg.includes('approve') || lowerMsg.includes('authorize') || lowerMsg.includes('broadcast') || lowerMsg.includes('publish') || lowerMsg.includes('save')) {
            options.type = 'teal';
            options.confirmText = options.confirmText || 'Yes, Confirm';
        } else {
            options.type = 'teal';
            options.confirmText = options.confirmText || 'Confirm';
        }
    }

    USMS.confirm(options).then(function(confirmed) {
        if (confirmed) {
            if (form) {
                // If the element has a name and value (like a specific button), append hidden input
                if (targetEl && targetEl !== form && targetEl.name && targetEl.value) {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = targetEl.name;
                    hiddenInput.value = targetEl.value;
                    form.appendChild(hiddenInput);
                }
                form.dataset.usmsConfirmed = 'true';
                if (typeof HTMLFormElement.prototype.submit === 'function') {
                    HTMLFormElement.prototype.submit.call(form);
                } else {
                    form.submit();
                }
            } else if (targetEl && targetEl.href) {
                window.location.href = targetEl.href;
            }
        }
    });

    return false;
};

// Backwards compatibility
function confirmAction(message) {
    return USMS.confirm({ message: message });
}

/* ---------- HEADER DROPDOWNS (Notifications & Profile) ---------- */
function toggleHeaderDropdown(menuId) {
    var menu = document.getElementById(menuId);
    if (!menu) return;
    var isOpen = menu.classList.contains('active');

    // Close all header dropdowns first
    document.querySelectorAll('.header-dropdown-menu').forEach(function(m) {
        m.classList.remove('active');
    });

    if (!isOpen) {
        menu.classList.add('active');
    }
}

/* Accordion toggle for Notification Dropdown items */
function toggleNotifAccordion(item) {
    var details = item.querySelector('.notif-expandable-content');
    var arrow = item.querySelector('.notif-toggle-arrow');
    if (!details) return;
    var isOpen = details.style.display === 'block';
    details.style.display = isOpen ? 'none' : 'block';
    if (arrow) {
        arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }
}

// Close header dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.header-dropdown-wrap')) {
        document.querySelectorAll('.header-dropdown-menu.active').forEach(function(m) {
            m.classList.remove('active');
        });
    }
});


/* ---------- SEARCHABLE SELECT DROPDOWN (PURE VANILLA JS) ---------- */
function initSearchableSelects() {
    var selects = document.querySelectorAll('select.searchable, select.searchable-select, select[data-searchable="true"]');
    
    selects.forEach(function(sel) {
        if (sel.dataset.searchableInit === 'true') return;
        sel.dataset.searchableInit = 'true';

        // Hide original select
        sel.style.display = 'none';

        // Create container wrap
        var wrap = document.createElement('div');
        wrap.className = 'searchable-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);

        // Find initial selected option or placeholder
        var selectedOpt = sel.options[sel.selectedIndex];
        var defaultText = selectedOpt && selectedOpt.value !== '' ? selectedOpt.text : (sel.getAttribute('data-placeholder') || (sel.options[0] ? sel.options[0].text : 'Select...'));
        var isPlaceholder = !selectedOpt || selectedOpt.value === '';

        // Trigger button
        var trigger = document.createElement('div');
        trigger.className = 'searchable-select-trigger';
        trigger.tabIndex = 0;

        var triggerText = document.createElement('span');
        triggerText.className = 'selected-text' + (isPlaceholder ? ' placeholder' : '');
        triggerText.textContent = defaultText;

        var arrow = document.createElement('span');
        arrow.className = 'material-symbols-outlined dropdown-arrow';
        arrow.textContent = 'arrow_drop_down';

        trigger.appendChild(triggerText);
        trigger.appendChild(arrow);
        wrap.appendChild(trigger);

        // Dropdown menu
        var dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown';
        dropdown.style.display = 'none';

        // Search box inside dropdown
        var searchBox = document.createElement('div');
        searchBox.className = 'searchable-select-search';

        var searchIcon = document.createElement('span');
        searchIcon.className = 'material-symbols-outlined';
        searchIcon.textContent = 'search';

        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Type to search…';
        searchInput.autocomplete = 'off';

        searchBox.appendChild(searchIcon);
        searchBox.appendChild(searchInput);
        dropdown.appendChild(searchBox);

        // Options container
        var optionsContainer = document.createElement('div');
        optionsContainer.className = 'searchable-select-options';

        var noResults = document.createElement('div');
        noResults.className = 'searchable-select-no-results';
        noResults.textContent = 'No matching items found';
        noResults.style.display = 'none';

        function buildOptions() {
            optionsContainer.replaceChildren();
            for (var i = 0; i < sel.options.length; i++) {
                var opt = sel.options[i];
                if (opt.value === '') continue; // skip placeholder option in list

                var optDiv = document.createElement('div');
                optDiv.className = 'searchable-select-option' + (opt.selected ? ' selected' : '');
                optDiv.dataset.value = opt.value;
                optDiv.textContent = opt.text;

                (function(optionElement, value) {
                    optDiv.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sel.value = value;
                        triggerText.textContent = optionElement.text;
                        triggerText.classList.remove('placeholder');

                        // Update selected class
                        var allOpts = optionsContainer.querySelectorAll('.searchable-select-option');
                        allOpts.forEach(function(o) { o.classList.remove('selected'); });
                        this.classList.add('selected');

                        // Trigger native change event
                        sel.dispatchEvent(new Event('change', { bubbles: true }));

                        // Close dropdown
                        closeDropdown();
                    });
                })(opt, opt.value);

                optionsContainer.appendChild(optDiv);
            }
            optionsContainer.appendChild(noResults);
        }

        buildOptions();
        dropdown.appendChild(optionsContainer);

        var hintDiv = document.createElement('div');
        hintDiv.className = 'searchable-select-hint';
        hintDiv.style.display = 'none';
        dropdown.appendChild(hintDiv);

        wrap.appendChild(dropdown);

        function openDropdown() {
            // Close other dropdowns
            document.querySelectorAll('.searchable-select-dropdown').forEach(function(d) {
                if (d !== dropdown) d.style.display = 'none';
            });
            document.querySelectorAll('.searchable-select-trigger').forEach(function(t) {
                if (t !== trigger) t.classList.remove('active');
            });

            dropdown.style.display = 'flex';
            trigger.classList.add('active');
            searchInput.value = '';
            filterOptions('');
            setTimeout(function() { searchInput.focus(); }, 50);
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            trigger.classList.remove('active');
        }

        function toggleDropdown(e) {
            e.stopPropagation();
            if (dropdown.style.display === 'none') {
                openDropdown();
            } else {
                closeDropdown();
            }
        }

        trigger.addEventListener('click', toggleDropdown);
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown(e);
            }
        });

        // Filter search: display max 5 items by default, search reveals all matching items
        function filterOptions(query) {
            var q = query.toLowerCase().trim();
            var itemElements = optionsContainer.querySelectorAll('.searchable-select-option');
            var totalCount = itemElements.length;
            var matchCount = 0;

            if (q === '') {
                var visibleCount = 0;
                itemElements.forEach(function(item) {
                    if (visibleCount < 5) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                noResults.style.display = 'none';
                if (totalCount > 5) {
                    hintDiv.textContent = 'Showing top 5 of ' + totalCount + ' items. Type to search all…';
                    hintDiv.style.display = 'block';
                } else {
                    hintDiv.style.display = 'none';
                }
            } else {
                itemElements.forEach(function(item) {
                    var txt = item.textContent.toLowerCase();
                    if (txt.indexOf(q) > -1) {
                        item.style.display = '';
                        matchCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                noResults.style.display = matchCount === 0 ? 'block' : 'none';
                hintDiv.style.display = 'none';
            }
        }

        var ajaxDebounceTimer = null;
        searchInput.addEventListener('input', function() {
            var q = this.value;
            filterOptions(q);

            if (sel.dataset.ajaxUrl && q.trim().length >= 2) {
                clearTimeout(ajaxDebounceTimer);
                ajaxDebounceTimer = setTimeout(function() {
                    var sep = sel.dataset.ajaxUrl.indexOf('?') > -1 ? '&' : '?';
                    fetch(sel.dataset.ajaxUrl + sep + 'q=' + encodeURIComponent(q.trim()))
                        .then(function(res) { return res.json(); })
                        .then(function(res) {
                            if (res && res.status === 'success' && Array.isArray(res.data)) {
                                res.data.forEach(function(item) {
                                    var exists = false;
                                    for (var j = 0; j < sel.options.length; j++) {
                                        if (String(sel.options[j].value) === String(item.id)) {
                                            exists = true; break;
                                        }
                                    }
                                    if (!exists) {
                                        var opt = document.createElement('option');
                                        opt.value = item.id;
                                        opt.text = item.name + (item.subtitle ? ' — ' + item.subtitle : '') + (item.faculty ? ' (' + item.faculty + ')' : '');
                                        if (item.faculty) opt.dataset.faculty = item.faculty;
                                        if (item.subtitle) opt.dataset.index = item.subtitle;
                                        if (item.emergency_contact) opt.dataset.contact = item.emergency_contact;
                                        if (item.blood_group) opt.dataset.blood = item.blood_group;
                                        if (item.height) opt.dataset.height = item.height;
                                        if (item.weight) opt.dataset.weight = item.weight;
                                        if (item.medical_status) opt.dataset.status = item.medical_status;
                                        if (item.allergies) opt.dataset.allergies = item.allergies;
                                        if (item.medical_notes) opt.dataset.notes = item.medical_notes;
                                        if (item.sports_list) opt.dataset.sports = item.sports_list;
                                        sel.appendChild(opt);
                                    }
                                });
                                buildOptions();
                                filterOptions(q);
                            }
                        })
                        .catch(function(err) { console.warn('AJAX search error:', err); });
                }, 220);
            }
        });

        // Prevent clicking search box from closing dropdown
        searchBox.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Sync if native select changes programmatically
        sel.addEventListener('change', function() {
            var cur = sel.options[sel.selectedIndex];
            if (cur) {
                triggerText.textContent = cur.text;
                triggerText.classList.toggle('placeholder', !cur.value);
                var allOpts = optionsContainer.querySelectorAll('.searchable-select-option');
                allOpts.forEach(function(o) {
                    o.classList.toggle('selected', o.dataset.value === cur.value);
                });
            }
        });
    });
}

// Global click to close searchable dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('.searchable-select-wrap')) {
        document.querySelectorAll('.searchable-select-dropdown').forEach(function(dd) {
            dd.style.display = 'none';
        });
        document.querySelectorAll('.searchable-select-trigger').forEach(function(tr) {
            tr.classList.remove('active');
        });
    }
});

/* ---------- DYNAMIC TABLE PAGINATION & SEARCH (PURE VANILLA JS) ---------- */
function setupTablePagination(tableId, pageSize, searchInputId) {
    var table = document.getElementById(tableId);
    if (!table) return;

    pageSize = pageSize || 8;
    var tableCard = table.closest('.table-card');
    var paginationEl = tableCard ? tableCard.querySelector('.pagination') : null;
    var searchInput = searchInputId ? document.getElementById(searchInputId) : null;

    var currentPage = 1;

    function getValidRows() {
        var allRows = Array.from(table.querySelectorAll('tbody tr'));
        // Filter out empty-state rows that span all columns
        return allRows.filter(function(row) {
            return !row.querySelector('td[colspan]');
        });
    }

    function render() {
        var validRows = getValidRows();
        var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        // Filter rows by query
        var filteredRows = validRows.filter(function(row) {
            if (!query) return true;
            return row.textContent.toLowerCase().indexOf(query) > -1;
        });

        var totalItems = filteredRows.length;
        var totalPages = Math.ceil(totalItems / pageSize) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var startIndex = (currentPage - 1) * pageSize;
        var endIndex = startIndex + pageSize;

        // Hide all valid rows first
        validRows.forEach(function(row) {
            row.style.display = 'none';
        });

        // Show only the sliced items for current page
        filteredRows.forEach(function(row, idx) {
            if (idx >= startIndex && idx < endIndex) {
                row.style.display = '';
            }
        });

        // Update pagination UI
        if (paginationEl) {
            var infoSpan = paginationEl.querySelector('span');
            if (infoSpan) {
                if (totalItems === 0) {
                    infoSpan.textContent = 'No matching entries found';
                } else {
                    infoSpan.textContent = 'Showing ' + (startIndex + 1) + ' to ' + Math.min(endIndex, totalItems) + ' of ' + totalItems + ' entries';
                }
            }

            var btnsContainer = paginationEl.querySelector('.page-btns');
            if (!btnsContainer) {
                btnsContainer = document.createElement('div');
                btnsContainer.className = 'page-btns';
                paginationEl.appendChild(btnsContainer);
            }

            btnsContainer.replaceChildren();

            // Prev button
            var prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.innerHTML = '<span class="material-symbols-outlined">chevron_left</span>';
            prevBtn.disabled = currentPage <= 1;
            prevBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });
            btnsContainer.appendChild(prevBtn);

            // Page number buttons
            for (var p = 1; p <= totalPages; p++) {
                (function(pageNumber) {
                    var pBtn = document.createElement('button');
                    pBtn.className = 'page-btn' + (pageNumber === currentPage ? ' active' : '');
                    pBtn.textContent = pageNumber;
                    pBtn.addEventListener('click', function() {
                        currentPage = pageNumber;
                        render();
                    });
                    btnsContainer.appendChild(pBtn);
                })(p);
            }

            // Next button
            var nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<span class="material-symbols-outlined">chevron_right</span>';
            nextBtn.disabled = currentPage >= totalPages;
            nextBtn.addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    render();
                }
            });
            btnsContainer.appendChild(nextBtn);
        }
    }

    // Bind search input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            render();
        });
    }

    // Initial render
    render();
}

// Backwards-compatible setupSearch function
function setupSearch(inputId, tableId) {
    setupTablePagination(tableId, 8, inputId);
}

// Auto-run on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initSearchableSelects();
});

