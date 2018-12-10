function path(parameters = {}, trailingUrl = '') {
    let baseUrl = window.location.pathname;
    let requestPath = '/';

    //If we are in development or admin, add in all the dev URL stuff
    if (baseUrl.includes('gradel_')) {
        requestPath = `${baseUrl.substring(0, baseUrl.indexOf('web') + 3)}`;
    }

    for (let parameter in parameters) {
        requestPath += `/${parameter}/${parameters[parameter]}`;
    }
    
    if(trailingUrl !== '') {
        requestPath += `/${trailingUrl}`;
    }
    if (requestPath[0] === '/' && requestPath[1] === '/') {
        requestPath = requestPath.slice(1);
    }

    return requestPath;
}

function asset(url) {
    return path({}, url);
}

/*
* Call this function in the document ready of your page if you want to render markdowns.
* Leave the markdown data in elements like this:
*
* <span class="markdownToRender" data-markdown="{{problem.description|e('js')}}"></span>
*
* And it will fill in this span with the markdown.
*/
function renderMarkdowns() {
    let markdowns = document.querySelectorAll('.markdownToRender');
    var converter = new showdown.Converter();
    converter.setOption('tasklists', true);
    converter.setOption('backslashEscapesHTMLTags', true);
    converter.setOption('emoji', true);
    converter.setFlavor('github');
    markdowns.forEach((markdownElement) => {
        let markdownText = converter.makeHtml(markdownElement.dataset.markdown);
        markdownElement.innerHTML = markdownText;
    });
}
