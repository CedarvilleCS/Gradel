function chevronClick(element, selection) {
    let subElements = document.querySelectorAll(`.${selection}`);
    if (window.getComputedStyle(subElements[0], null).getPropertyValue('display') === 'block') {
        subElements.forEach((value, index) => {
            value.style.display = 'none';
        });
        element.src = element.src.replace('white_expand_more', 'white_chevron');
    } else {
        subElements.forEach((value, index) => {
            value.style.display = 'block';
        });
        element.src = element.src.replace('white_chevron', 'white_expand_more');
    }
}