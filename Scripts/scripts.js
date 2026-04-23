// edit.html — photo preview
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var wrap = input.closest('.edit-photo-container').querySelector('.edit-photo-wrap');
        var img = wrap.querySelector('img');
        var initials = wrap.querySelector('.edit-photo-initials');
        img.src = e.target.result;
        img.style.display = 'block';
        if (initials) initials.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

// index.html — table search, filter, pagination
(function () {
    var searchInput  = document.getElementById('tableSearch');
    if (!searchInput) return;
    var searchClear  = document.getElementById('searchClear');
    var colFilter    = document.getElementById('colFilter');
    var perPageSel   = document.getElementById('perPage');
    var paginationBar = document.getElementById('paginationBar');
    var countEl      = document.getElementById('visibleCount');
    var tbody        = document.querySelector('#studentsTable tbody');
    var allRows      = Array.from(tbody.querySelectorAll('tr'));
    var currentPage  = 1;

    function getFilteredRows() {
        var q   = searchInput.value.trim().toLowerCase();
        var col = parseInt(colFilter.value, 10);
        return allRows.filter(function (row) {
            if (!q) return true;
            if (col === -1) return row.textContent.toLowerCase().includes(q);
            var cell = row.cells[col];
            return cell && cell.textContent.toLowerCase().includes(q);
        });
    }

    function render() {
        var filtered  = getFilteredRows();
        var perPage   = parseInt(perPageSel.value, 10);
        var totalPages = (perPage === 0) ? 1 : Math.ceil(filtered.length / perPage);
        if (currentPage > totalPages) currentPage = 1;

        var start = (perPage === 0) ? 0 : (currentPage - 1) * perPage;
        var end   = (perPage === 0) ? filtered.length : start + perPage;
        var pageRows = filtered.slice(start, end);

        allRows.forEach(function (r) { r.style.display = 'none'; });
        pageRows.forEach(function (r) { r.style.display = ''; });

        countEl.textContent = filtered.length + ' record(s)';
        renderPagination(totalPages, filtered.length);
    }

    function renderPagination(totalPages, total) {
        paginationBar.innerHTML = '';
        if (totalPages <= 1) return;
        var perPage = parseInt(perPageSel.value, 10);
        var start = (currentPage - 1) * perPage + 1;
        var end   = Math.min(currentPage * perPage, total);

        var info = document.createElement('span');
        info.className = 'page-info';
        info.textContent = start + '–' + end + ' of ' + total;
        paginationBar.appendChild(info);

        var nav = document.createElement('div');
        nav.className = 'page-nav';

        function btn(label, page, disabled) {
            var b = document.createElement('button');
            b.className = 'page-btn' + (page === currentPage ? ' active' : '');
            b.textContent = label;
            b.disabled = disabled;
            b.addEventListener('click', function () { currentPage = page; render(); });
            return b;
        }

        nav.appendChild(btn('«', 1, currentPage === 1));
        nav.appendChild(btn('‹', currentPage - 1, currentPage === 1));

        var range = [], delta = 2;
        for (var i = Math.max(1, currentPage - delta); i <= Math.min(totalPages, currentPage + delta); i++) range.push(i);
        if (range[0] > 1) { nav.appendChild(btn('1', 1, false)); if (range[0] > 2) { var dots = document.createElement('span'); dots.className = 'page-dots'; dots.textContent = '…'; nav.appendChild(dots); } }
        range.forEach(function (p) { nav.appendChild(btn(p, p, false)); });
        if (range[range.length-1] < totalPages) { if (range[range.length-1] < totalPages - 1) { var dots2 = document.createElement('span'); dots2.className = 'page-dots'; dots2.textContent = '…'; nav.appendChild(dots2); } nav.appendChild(btn(totalPages, totalPages, false)); }

        nav.appendChild(btn('›', currentPage + 1, currentPage === totalPages));
        nav.appendChild(btn('»', totalPages, currentPage === totalPages));
        paginationBar.appendChild(nav);
    }

    searchInput.addEventListener('input', function () {
        searchClear.style.display = this.value ? '' : 'none';
        currentPage = 1; render();
    });
    searchClear.addEventListener('click', function () {
        searchInput.value = ''; searchClear.style.display = 'none';
        currentPage = 1; render();
    });
    colFilter.addEventListener('change', function () { currentPage = 1; render(); });
    perPageSel.addEventListener('change', function () { currentPage = 1; render(); });

    render();
})();
