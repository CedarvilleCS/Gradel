function path(baseUrl, parameters = {}, trailingUrl = '') {
    let requestPath = '/';
    if (baseUrl && baseUrl !== '/' && baseUrl !== '') {
        requestPath += `${baseUrl}/`;
    }

    for (let parameter in parameters) {
        requestPath += `${parameter}/${parameters[parameter]}/`;
    }
    requestPath += trailingUrl;

    return requestPath;
}