document.addEventListener('DOMContentLoaded', function () {
    // 1. Dynamic Table Sizing Logic
    const initTableSizing = () => {
        const wrappers = document.querySelectorAll('.data-table-scroll-wrapper');
        const count = wrappers.length;

        wrappers.forEach(wrapper => {
            if (count === 1) {
                wrapper.classList.add('single-table-view');
            } else if (count > 1) {
                wrapper.classList.add('multi-table-view');
            }
        });
    };
    initTableSizing();

    const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

    const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
        v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
    )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

    // do the work...
    document.querySelectorAll('.data-table th').forEach(th => th.addEventListener('click', (() => {
        const table = th.closest('table');
        const tbody = table.querySelector('tbody');
        if (!tbody) return; // Skip if no body

        // Toggle sort order
        const asc = th.dataset.asc = (th.dataset.asc === 'true' ? 'false' : 'true');

        // Update UI classes
        th.parentElement.querySelectorAll('th').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        th.classList.add(asc === 'true' ? 'sort-asc' : 'sort-desc');

        Array.from(tbody.querySelectorAll('tr'))
            .sort(comparer(Array.from(th.parentElement.children).indexOf(th), asc === 'true'))
            .forEach(tr => tbody.appendChild(tr));
    })));
});
