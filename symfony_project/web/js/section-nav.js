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
            html +=         `<img id="Chevron-Section-${section.id}" class="nav-chevron" onclick="chevronClick(this, 'section-${section.id}')" src="${asset('images/white_chevron.png')}" />`;
        }
        else {
            //Empty div here for css-grid to align things correctly
            html +=         `<div></div>`
        }
        html +=             `<a id="Link-Section-${section.id}" href="${path({'section': section.id})}">${section.name}</a>
                        </div>`;
        section.assignments.forEach((assignment, assignmentIndex) => {
            html +=     `<div id="Assignment-${assignment.id}" class="nav-assignment section-${section.id}">
                            <div class="nav-chevron-title-container">`;
            if (assignment.problems.length > 0) {
                html +=         `<img id="Chevron-Assignment-${assignment.id}" class="nav-chevron" onclick="chevronClick(this, 'assignment-${assignment.id}')" src="${asset('images/white_chevron.png')}" />`;
            } else {
                //Empty div here for css-grid to align things correctly
                html +=         `<div></div>`;
            }
            html +=             `<a id="Link-Assignment-${assignment.id}" href="${path({'section': section.id, 'assignment': assignment.id})}">${assignment.name}</a>
                            </div>`;
            assignment.problems.forEach((problem, problemIndex) => {
                html +=     `<div id="Problem-${problem.id}" class="nav-problem assignment-${assignment.id}">
                                <a id="Link-Problem-${problem.id}" href="${path({'section': section.id, 'assignment': assignment.id, 'problem': problem.id})}">${problem.name}</a>
                            </div>`;
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
                html +=     `<img id="Chevron-Section-${section.id}" class="nav-chevron" onclick="chevronClick(this, 'section-${section.id}')" src="${asset('images/white_chevron.png')}" />`;
            }
            else {
                //Empty div here for css-grid to align things correctly
                html +=         `<div></div>`
            }
        html +=             `<a id="Link-Section-${section.id}" href="${path({'section': section.id})}">${section.name}</a>
                        </div>`;
        section.assignments.forEach((assignment, assignmentIndex) => {
            html +=     `<div id="Assignment-${assignment.id}" class="nav-assignment section-${section.id}">
                            <div class="nav-chevron-title-container">`;
            if (assignment.problems.length > 0) {
                html +=         `<img id="Chevron-Assignment-${assignment.id}" class="nav-chevron" onclick="chevronClick(this, 'assignment-${assignment.id}')" src="${asset('images/white_chevron.png')}" />`;
            } else {
                //Empty div here for css-grid to align things correctly
                html +=         `<div></div>`;
            }
            html +=             `<a id="Link-Assignment-${assignment.id}" href="${path({'section': section.id, 'assignment': assignment.id}, 'problem')}">${assignment.name}</a>
                            </div>`;
            assignment.problems.forEach((problem, problemIndex) => {
                html +=     `<div id="Problem-${problem.id}" class="nav-problem assignment-${assignment.id}">
                                <a id="Link-Problem-${problem.id}" href="${path({'section': section.id, 'assignment': assignment.id, 'problem': problem.id})}}">${problem.name}</a>
                            </div>`;
            });
            html +=     `</div>`;
        });
        html +=     `</div>			
                </div>`;
    });
    document.getElementById('SideNavContent').innerHTML = html;

    //Now that the nav HTML is in the page, we can work on expanding and coloring it:

    //Expand side nav to show the link for the page we are on and color that link orange
    /* SitePosition is formatted this way for longevity:
    *
    *   start: {
    *       id: Section-123,
    *       next: {
    *           id: Assignment-456,
    *           next: {
    *               id: Problem-789,
    *               next: null
    *                 }
    *             }
    *          }
    *
    *  This way we can keep digging into the 'next' attribute until it's null, expanding
    *  everything in the divs matching the ids found in this array.
    *  We then color orange the text that is in the corresponding link.
    */
    let sitePositionElement = document.getElementById('SitePosition');
    if(sitePositionElement) {
        let sitePosition = JSON.parse(sitePositionElement.dataset.pos);
        let tail = sitePosition['start'];
        while(tail !== null) {
            let selection = tail['id'];
            //Change the first character of the string to lowercase because it was an ID
            //but will now represent a class.
            selection = selection.substring(0, 1).toLowerCase() + selection.substring(1);
            //Expand this element
            let subElements = document.querySelectorAll(`.${selection}`);
            subElements.forEach((value, index) => {
                value.style.display = 'block';
            });
            //Switch the chevron to the expanded icon if there is one
            let chevronElement = document.getElementById(`Chevron-${tail['id']}`);
            if(chevronElement) {
                chevronElement.src = chevronElement.src.replace('white_chevron', 'white_expand_more');
            }
            //Another null check for tail here so that we can catch it and color the text orange
            if(tail['next'] === null) {
                document.getElementById(`Link-${tail['id']}`).classList.add('orange');
            }
            //Set tail to the next element
            tail = tail['next'];
        }
    }
    
    $.get(viewData.semestersPath, function(semesterData) {
        console.log(semesterData);
        document.getElementById('ChosenSemester').innerHTML = ` | ${semesterData.chosenSemester.term} ${semesterData.chosenSemester.year}`;
        let termsDropdown = document.getElementById('Terms');
        semesterData.semesters.forEach((value, index) => {
            termsDropdown.innerHTML += `<option value="${value.id}" ${(semesterData.chosenSemester.id === value.id ? 'selected' : '')}>${value.term} ${value.year}</option>`
        });
    });
});

function changeSelectedTerm(element) {
    window.location = path({home: element.value});
}
