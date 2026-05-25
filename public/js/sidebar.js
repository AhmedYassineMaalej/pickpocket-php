const filterRoute='/catalog/getFilteredProductsAJAX'
function getFilters(){
    return{
          minPrice: document.getElementById('minPriceInput').value,
          maxPrice: document.getElementById('maxPriceInput').value,
          category: document.getElementById('category').value,
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
    document.querySelector(".catalog-products").innerHTML=html;



}
document.getElementById('minPriceInput').addEventListener('input',getProducts);
document.getElementById('maxPriceInput').addEventListener('input',getProducts);
document.getElementById('category').addEventListener('change',getProducts);
document.querySelectorAll('.provider-checkbox').forEach(p=>p.addEventListener('change',getProducts));