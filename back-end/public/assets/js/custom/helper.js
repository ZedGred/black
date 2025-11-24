function toCurrency(num) {
    return $.isNumeric(num) ? Intl.NumberFormat('id-ID').format(num) : num;
}

function toCurrencyFraction(num) {
    return toCurrency(num, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function mask_reference(value, references = []) {
    if (Array.isArray(references)) {
        const search = references.find(v => v.original_value == value);
        return search ? search.defined_as : value;
    }
    return value;
}

function currenyToFloat(currency) {
    if (currency.indexOf(',') !== -1 && currency.indexOf('.') !== -1) {
        if (currency.indexOf(',') > currency.indexOf('.')) {
            currency = currency.replace(/\./g,'')
            currency = currency.replace(/,/g, '.')
        } else {
            currency = currency.replace(/,/g, '')
        }
    } else if (currency.indexOf(',') !== -1) {
        currency = currency.replace(/,/g, '');
    } else if (currency.indexOf('.') !== -1) {
        currency = currency.replace(/\./g, '');
    }
    return parseFloat(currency)
}

function updateQueryParams(newParams, callback = () => {}) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    Object.keys(newParams).forEach(key => {
        params.set(key, newParams[key]);
    });
    history.replaceState(null, '', `${url.pathname}?${params.toString()}`);
    if (typeof callback === 'function') (callback)();
}

function urlAddParams(url, params) {
    let newUrl = new URL(url, window.location.origin); // Ensure it's a valid URL
    Object.keys(params).forEach(key => newUrl.searchParams.append(key, params[key]));
    return newUrl.toString();
}

function getQueryParams(key) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    return params.get(key);
}

function urlGetParams(urlText, keys) {
    const url = urlText.startsWith('http://') || urlText.startsWith('https://')
        ? new URL(urlText)
        : new URL(urlText, window.location.origin);

    const params = new URLSearchParams(url.search);
    let searchKeys = keys;
    if (typeof keys === 'string') {
        searchKeys = [keys];
    }
    const returnKeys = {};
    for (const key of searchKeys) {
        if (params.get(key)) {
            returnKeys[key] = params.get(key);
        }
    }
    return returnKeys;
}


function resetForm(target) {
    const form = document.querySelector(target);
    if (form) {
        const inputs = form.querySelectorAll('input');
        const selects = form.querySelectorAll('select');
        const textareas = form.querySelectorAll('textarea');
        for (let i = 0; i < inputs.length; i++) {
            if (inputs[i]) {
                inputs[i].type === 'file' ? inputs[i].value = null : inputs[i].value = '';
            }
        }
        for (let i = 0; i < selects.length; i++) {
            if (selects[i]) {
                selects[i].value = '';
            }
        }
        for (let i = 0; i < textareas.length; i++) {
            if (textareas[i]) {
                textareas[i].value = '';
            }
        }
        // inputs.forEach(el => el.type === 'file' ? el.target.value = null : el.target.value = '')
        // selects.forEach(el => el.target.value = '')
        // textareas.forEach(el => el.target.value = '')
    }
}

function paramsToString(params) {
    const paramsArray = [];
    Object.keys(params).forEach((key, index) => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            paramsArray.push(`${key}=${params[key]}`);
        }
    })
    return paramsArray.join('&');
}

function renderPagination(elmID, pagination = null, MAX_PAGINATION_BUTTON = 5, totalFiltered = null) {
    const elm = document.querySelector(elmID);
    if (!elm) return;
    if (pagination === null) {
        elm.innerHTML = [
            '<ul class="pagination">',
            '<span class="page-link page-link-first disabled">««</span>',
            '<span class="page-link disabled">« Prev</span>',
            '<span class="page-link disabled">Next »</span>',
            '<span class="page-link page-link-last disabled">»»</span>',
            '</ul>',
        ].join('');
        return;
    }
    // const createPaginationItem = (link) => `
    //     <li class="page-item ${link.active ? 'active' : link.url ? '' : 'disabled'}">
    //         ${link.url ? `<a class="page-link" href="${link.url.replace('/api/', '/')}" data-page="${link.page||link.label}">${link.label}</a>` : `<span class="page-link">${link.label}</span>`}
    //     </li>`;

    const createPaginationItem = (link) => {
        let page = link.page || link.label;
        if (link.url) {
            const urlObj = new URL(link.url, window.location.origin);
            const pageParam = urlObj.searchParams.get("page");
            if (pageParam) {
                page = pageParam;
            }
        }

        return `
        <li class="page-item ${link.active ? 'active' : link.url ? '' : 'disabled'}">
            ${link.url ? `<a class="page-link" href="${link.url.replace('/api/', '/')}" data-page="${page}">${link.label}</a>` : `<span class="page-link">${link.label}</span>`}
        </li>`;
    };



    const { links, page, total_page } = pagination;
    const firstLink = links[1];
    const lastLink = links[links.length - 2];
    const prevLink = links[0];
    const nextLink = links[links.length - 1];
    let paginationHtml = '<ul class="pagination">';
    if (page !== 1 && total_page > 1) {
        paginationHtml += createPaginationItem({ active: false, label: '««', url: firstLink.url, page: 1 });
    } else {
        paginationHtml += `<a class="page-link page-link-first disabled">««</a>`;
    }
    paginationHtml += createPaginationItem(prevLink);

    const middleLinks = links.slice(1, links.length - 1);
    const maxPagination = Math.min(MAX_PAGINATION_BUTTON, total_page);
    let startPage = Math.max(1, page - Math.floor(maxPagination / 2));
    let endPage = Math.min(total_page, startPage + maxPagination - 1);
    if (endPage - startPage + 1 < maxPagination) {
        startPage = Math.max(1, endPage - maxPagination + 1);
    }
    for (let i = startPage; i <= endPage; i++) {
        const link = middleLinks.find((l) => l.label == i);
        if (link) paginationHtml += createPaginationItem(link);
    }
    paginationHtml += createPaginationItem(nextLink);
    if (page !== total_page && total_page > 1) {
        paginationHtml += createPaginationItem({ active: false, label: '»»', url: lastLink.url, page: total_page });
    } else {
        paginationHtml += `<a class="page-link page-link-first disabled">»»</a>`;
    }
    if (totalFiltered !== null) {
        paginationHtml += `<a class="page-link page-link-first disabled text-black">Show ${toCurrency(totalFiltered?.filtered||0)} of ${toCurrency(totalFiltered?.total||0)}</a>`;
    }
    paginationHtml += '</ul>';
    elm.innerHTML = paginationHtml;
}

function formDataToObject(formData) {
    const obj = {};

    formData.forEach((value, key) => {
        let keys = key.replace(/\[/g, '.').replace(/\]/g, '').split('.');
        let lastKey = keys.pop();
        let nestedObj = obj;

        keys.forEach(k => {
            if (!nestedObj[k]) nestedObj[k] = {};
            nestedObj = nestedObj[k];
        });

        nestedObj[lastKey] = value;
    });

    return obj;
}


function formatIntoLabel(str){
    return str.split('_').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const formatMonth = (monthString) => {
    const [year, month] = monthString.split('-');
    const date = new Date(year, month - 1); // Bulan di JS mulai dari 0
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'long' }).format(date);
};

document.addEventListener('DOMContentLoaded', function () {
    $(document).find('#form-filter input').on('change', function () {
        const name = $(this).attr('name');
        const value = $(this).val();
        updateQueryParams({ [name]: value })
    })
    $(document).find('#form-filter select').on('change', function () {
        const name = $(this).attr('name');
        const value = $(this).val();
        updateQueryParams({ [name]: value })
    })
});

function buttonSpinner(el, onLoadingMessage = 'Loading', disabledOnLoading = true) {
    const $el = $(el);
    const originalHtml = $el.html();
    const onFinishMessage = originalHtml;
    return {
        ...$el,
        showLoading: () => {
            if (disabledOnLoading) $el.prop('disabled', true);
            $el.html(`
                <div class="spinner-border" role="status" style="width:15px;height:15px;"></div>
                <span>${onLoadingMessage}...</span>
            `);
        },
        hideLoading: () => {
            if (disabledOnLoading) $el.prop('disabled', false);
            $el.html(onFinishMessage);
        }
    };
}
