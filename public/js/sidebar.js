const filterRoute='/catalog/getFilteredProductsAJAX'

function getFilters(){
    return{
          minPrice: document.getElementById('minPriceInput')?.value,
          maxPrice: document.getElementById('maxPriceInput')?.value,
          category: document.getElementById('category')?.value,
          providers: [...document.querySelectorAll(".provider-checkbox:checked")].map(p=>p.value)
         };
}

async function getProducts() {
    let filters = getFilters();
    let filterParams = new URLSearchParams()

    if (filters.category)
        filterParams.set('category', filters.category)
    if (filters.minPrice)
        filterParams.set('minPrice', filters.minPrice)
    if (filters.maxPrice)
        filterParams.set('maxPrice', filters.maxPrice)
    filters.providers.forEach(p => filterParams.append('providers[]', p));
    let result = await fetch(`${filterRoute}?${filterParams}`);
    let html = await result.text();
    let container = document.querySelector(".catalog-products");
    if (container) {
        container.innerHTML = html;
    }
}

// Only add event listeners if the elements exist on the page
const minPriceInput = document.getElementById('minPriceInput');
const maxPriceInput = document.getElementById('maxPriceInput');
const categorySelect = document.getElementById('category');
const providerCheckboxes = document.querySelectorAll('.provider-checkbox');

if (minPriceInput) minPriceInput.addEventListener('input', getProducts);
if (maxPriceInput) maxPriceInput.addEventListener('input', getProducts);
if (categorySelect) categorySelect.addEventListener('change', getProducts);
if (providerCheckboxes.length) {
    providerCheckboxes.forEach(p => p.addEventListener('change', getProducts));
}