//Set main_content min-height to the height of the content plus 100px to make sure
//that the floating controls will never cover something with no way to see it.
let mainContent = document.querySelector('.main_content');
let contentHeight = mainContent.clientHeight;
mainContent.style.minHeight = `${contentHeight + 100}px`;
