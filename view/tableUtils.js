/**
 * tableUtils.js
 * Lightweight client-side search + pagination for any table.
 *
 * Usage:
 *   const tp = new TablePager('tbodyId', { pageSize: 10, searchId: 'searchInputId' });
 *   tp.setRows(arrayOfTrElements);   // call after AJAX loads rows
 */
class TablePager {
    constructor(tbodyId, { pageSize = 10, searchId = null } = {}) {
        this.tbody    = document.getElementById(tbodyId);
        this.pageSize = pageSize;
        this.currentPage = 1;
        this.allRows  = [];   // full set of <tr> elements
        this.filtered = [];   // after search filter

        // Wire search box
        if (searchId) {
            const box = document.getElementById(searchId);
            if (box) {
                box.addEventListener('input', () => {
                    this.currentPage = 1;
                    this._filter(box.value.trim().toLowerCase());
                });
            }
        }
    }

    /** Feed fresh rows (array of <tr> DOM nodes) and re-render */
    setRows(trArray) {
        this.allRows     = trArray;
        this.currentPage = 1;
        const box = document.querySelector(`#${this._searchId}`);
        this._filter(box ? box.value.trim().toLowerCase() : '');
    }

    _filter(term) {
        if (!term) {
            this.filtered = this.allRows.slice();
        } else {
            this.filtered = this.allRows.filter(tr =>
                tr.textContent.toLowerCase().includes(term)
            );
        }
        this._render();
    }

    _render() {
        const total = this.filtered.length;
        const pages = Math.max(1, Math.ceil(total / this.pageSize));
        if (this.currentPage > pages) this.currentPage = pages;

        const start = (this.currentPage - 1) * this.pageSize;
        const slice = this.filtered.slice(start, start + this.pageSize);

        // Re-fill tbody
        while (this.tbody.firstChild) this.tbody.removeChild(this.tbody.firstChild);
        if (slice.length === 0) {
            const td = this.tbody.insertRow().insertCell();
            td.colSpan = 99;
            td.className = 'no-records';
            td.textContent = 'No records match your search.';
        } else {
            slice.forEach(tr => this.tbody.appendChild(tr));
        }

        // Update pagination controls
        const ctrlId = this.tbody.id + '_pagination';
        let ctrl = document.getElementById(ctrlId);
        if (!ctrl) {
            ctrl = document.createElement('div');
            ctrl.id = ctrlId;
            ctrl.className = 'pagination-bar';
            this.tbody.closest('table').after(ctrl);
        }

        ctrl.innerHTML = '';
        if (pages <= 1) return; // hide bar if only 1 page

        const info = document.createElement('span');
        info.className = 'pg-info';
        info.textContent = `Page ${this.currentPage} of ${pages}  (${total} records)`;
        ctrl.appendChild(info);

        const mkBtn = (label, page, disabled) => {
            const b = document.createElement('button');
            b.textContent = label;
            b.className   = 'pg-btn' + (page === this.currentPage ? ' pg-active' : '');
            b.disabled    = disabled;
            b.onclick     = () => { this.currentPage = page; this._render(); };
            ctrl.appendChild(b);
        };

        mkBtn('‹ Prev', this.currentPage - 1, this.currentPage === 1);

        // Show a window of up to 5 page numbers
        let lo = Math.max(1, this.currentPage - 2);
        let hi = Math.min(pages, lo + 4);
        lo = Math.max(1, hi - 4);
        for (let p = lo; p <= hi; p++) mkBtn(p, p, false);

        mkBtn('Next ›', this.currentPage + 1, this.currentPage === pages);
    }
}
