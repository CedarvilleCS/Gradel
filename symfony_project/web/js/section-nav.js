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
var viewData = document.getElementById('SideNavData').dataset;
//This ajax callback function has a lot of wierd non-standard formatting, however this is so that you can read the HTML it generates more easily
$.get(viewData.path, function (data) {
    var html = '';
    //List all of the sections being taken
    data.sections_taking.forEach((section, sectionIndex) => {
        html += `<div class="section-nav-container">
                    <div id="Section-${section.id}">
                        <div class="nav-chevron-title-container">`;
        if (section.assignments.length > 0) {
            html +=         `<img class="nav-chevron" onclick="chevronClick(this, 'section-${section.id}')" src="${asset('images/white_chevron.png')}" />`;
        }
        html +=             `<a href="${path({'section': section.id})}">${section.name}</a>
                        </div>`;
        section.assignments.forEach((assignment, assignmentIndex) => {
            html +=     `<div id="Assignment-${assignment.id}" class="nav-assignment section-${section.id}">
                            <div class="nav-chevron-title-container">`;
            if (assignment.problems.length > 0) {
                html +=         `<img class="nav-chevron" onclick="chevronClick(this, 'assignment-${assignment.id}')" src="${asset('images/white_chevron.png')}" />`;
            } else {
                //Empty div here for css-grid to align things correctly
                html +=         `<div></div>`;
            }
            html +=             `<a href="${path({'section': section.id, 'assignment': assignment.id}, 'problem')}">${assignment.name}</a>
                            </div>`;
            assignment.problems.forEach((problem, problemIndex) => {
                html +=     `<div id="Problem-${problem.id}" class="nav-problem assignment-${assignment.id}"><a href="${path({'section': section.id, 'assignment': assignment.id, 'problem': problem.id})}">${problem.name}</a></div>`;
            });
            html +=     `</div>`;
        });
        html +=     `</div>
                </div>`;
    });
    //List all of the sections being taught
    data.sections_teaching.forEach((section, sectionIndex) => {
        html += `<div class="section-nav-container">
                    <div id="Section-${section.id}">
                        <div class="nav-chevron-title-container">`;
            if (section.assignments.length > 0) {
                html +=     `<img class="nav-chevron" onclick="chevronClick(this, 'section-${section.id}')" src="${asset('images/white_chevron.png')}" />`;
            }
        html +=             `<a href="${path({'section': section.id})}">${section.name}</a>
                        </div>`;
        section.assignments.forEach((assignment, assignmentIndex) => {
            html +=     `<div id="Assignment-${assignment.id}" class="nav-assignment section-${section.id}">
                            <div class="nav-chevron-title-container">`;
            if (assignment.problems.length > 0) {
                html +=         `<img class="nav-chevron" onclick="chevronClick(this, 'assignment-${assignment.id}')" src="${asset('images/white_chevron.png')}" />`;
            } else {
                //Empty div here for css-grid to align things correctly
                html +=         `<div></div>`;
            }
            html +=             `<a href="${path({'section': section.id, 'assignment': assignment.id}, 'problem')}">${assignment.name}</a>
                            </div>`;
            assignment.problems.forEach((problem, problemIndex) => {
                html +=     `<div id="Problem-${problem.id}" class="nav-problem assignment-${assignment.id}"><a href="${path({'section': section.id, 'assignment': assignment.id, 'problem': problem.id})}}">${problem.name}</a></div>`;
            });
            html +=     `</div>`;
        });
        html +=     `</div>			
                </div>`;
    });
    document.getElementById('SideNavContent').innerHTML = html;
});

//Expand side nav to show the link for the page we are on and color that link orange
/* SitePosition is formatted this way for longevity:
*
*   start: [
*       id: Section-123,
*       next: [
*           id: Assignment-456,
*           next: [
*               id: Problem-789,
*               next: null
*                 ]
*             ]
*          ]
*
*  This way we can keep digging into the 'next' attribute until it's null, expanding
*  everything in the divs matching the ids found in this array.
*  We then color orange the text that is either in the anchor under the
*  nav-chevron-title-container div under the div containing the id, or else we the
*  text under the anchor under the div containing the id.
*/
let sitePosition = document.getElementById('SitePosition').dataset.pos;
let tail = sitePosition['start'];
while(tail['next'] !== null) {


    tail = tail['next'];
}