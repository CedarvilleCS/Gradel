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
