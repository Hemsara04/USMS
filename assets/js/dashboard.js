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

/* ---------- CONFIRM ACTION ---------- */
function confirmAction(message) {
    return confirm(message);
}

/* ---------- SEARCHABLE SELECT DROPDOWN (PURE VANILLA JS) ---------- */
function initSearchableSelects() {
    var selects = document.querySelectorAll('select.searchable, select[data-searchable="true"]');
    
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
                if (opt.disabled && opt.value === '') continue; // skip placeholder option in list

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

        // Filter search
        function filterOptions(query) {
            var q = query.toLowerCase().trim();
            var itemElements = optionsContainer.querySelectorAll('.searchable-select-option');
            var matchCount = 0;

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

