# Future Features

This document attempts to address some things we did not get to, but could improve Gradel in the future.

## Table of Contents
- [Plugins](#plugins)
    - [Authentication](#authentication)
    - [Possible Solutions](#possible-solutions)
    - [Other Helpful Documentation](#other-helpful-documentation)

## Plugins
### Authentication
The first major problem that we encountered was using Google Auth in a desktop environment. We tried to use this [documentation](https://developers.google.com/identity/protocols/OAuth2InstalledApp).
However, we never managed to get it connected correctly, so we implemented a hacky web crawler with [Selenium](https://www.seleniumhq.org/).This let to a host of data passing issues caused by Http Posts that we never solved.

### Possible Solutions

1. Ignore plugins: Gradel is great without them!
2. Solve the Google OAuth 2.0 issues so that you don't have to hack around it with Selenium.
3. Implement Git. If Gradel added support for Git using a library, such as [git-auto-deploy](https://github.com/scriptburn/git-auto-deploy) or [js-git](https://www.google.com/search?q=js+git&rlz=1C1GCEA_enUS784US784&oq=js+git&aqs=chrome..69i57j0j69i60j0l3.719j0j7&sourceid=chrome&ie=UTF-8), code can be auto uploaded from desktop to Gradel without needing to copy and paste files. In Chris Brauns's opinion, this is a good option because it is easy and effective, without having to worry about messing with Netbeans and Visual Studio plugin libraries.

### Other Helpful Documentation
* [Netbeans Plugins Documentation](https://platform.netbeans.org/tutorials/nbm-google.html)
* [Visual Studio Helpful Documentation](https://docs.microsoft.com/en-us/vsts/integrate/ide/extensions/hello_world?view=vsts)
