# User Guide

## Table of Contents
- [Gradel Data Model](#gradel-data-model)
    - [Class Database Model](#class-database-model)
        - [Courses](#courses)
        - [Sections](#sections)
        - [Assignments](#assignments)
        - [Problems](#problems)
        - [Testcases](#testcases)
    - [User Database Model](#user-database-model)
        - [Global Roles](#global-roles)
            - [Super Users](#super-users)
            - [Administrators](#administrators)
        - [Section Roles](#section-roles)
            - [Teachers](#teachers)
            - [Judges](#judges)
            - [Students](#students)
- [Authentication](#authentication)
- [Instructions for Professors](#instructions-for-professors)
    - [Edit a User Profile (Super User Only)](#edit-a-user-profile-super-user-only)
    - [Course Manipulation](#course-manipulation)
        - [Create a Course/Contest (Super User/Admin Only)](#create-a-course-contest-super-user-admin-only)
        - [Edit/Delete a Course/Contest (Super User/Admin Only)](#edit-delete-a-course-contest-super-user-admin-only)
    - [Section Manipulation](#section-manipulation)
        - [Create a Section (Super User/Admin Only)](#create-a-section-super-user-admin-only)
        - [Edit/Delete a Section (Teachers)](#edit-delete-a-section-teachers)
        - [Cloning a Section](#cloning-a-section)
    - [Assignment Manipulation](#assignment-manipulation)
    - [Problem/Testcase Manipulation](#problem-testcase-manipulation)
    - [Impersonating a Student (Super Users Only)](#impersonating-a-student-super-users-only)
    - [View/Export Grades](#view-export-grades)
    - [View Submissions](#view-submissions)
- [Instructors for Students](#instructors-for-students)
    - [See Assignments](#see-assignments)
    - [Working on Code](#working-on-code)

## Gradel Data Model
Gradel's data model is organized hierarchically. Courses/Contests contain Sections, which contain Assignments, which contain Problems, which contain Testcases. It is designed with a variety of grading options and timings so that the professor can specify exactly how students are evaluated.

Users belong to roles of the following categories: Super Users, Administrators, Teachers, Students, and Judges.

### Class Database Model
#### Courses
Courses are the abstract idea of a class. They contain a name, a description, and a code (e.g. `CS-1210`). When a teacher wants to make a class for a semester, they choose a course that the class will be based on. An example of a course would be `CS-1220` or `Cedarville Annual Programming Contest`. These exist outside of time.

#### Sections
Sections are an instance of a class in a given semester. They contain more detailed information, such as semester, year, start time, and end time. Sections contain a list of assignments and have a list of roles for teachers and students. An example of a section would the `CS-1220 Fall 2017` or `2018 Cedarville Annual Programming Contest`.

#### Assignments
Assignments are collections of problems for students to solve. They have their own starting and ending times, weights, penalties, and descriptions. Assignment examples would be like `Homework #1` in `Calculus I` where you have a list of many problems to solve.

#### Problems
Problems are the entity that students are going to be interacting with. They consist of a list of testcases that the student must solve in order to pass the problem. They have a lot of grading options that allow the professor to set exactly how students see results.

#### Testcases
Testcases contain input and output. A problem can have any number of testcases and student needs to pass the testcases to get the problem right.

### User Database Model

Users are the entity that represent a person using the Gradel website. Users can have a variety of roles on different levels of the model.

#### Global Roles
Global roles are roles that persist anywhere on Gradel. Think of it like a characteristic of the user.

##### Super Users
Super Users persist throughout Gradel, and they have the ability to do anything. They have all the privileges of every level. In addition, they can impersonate other users and have access to the user management page to change names, email addresses, and global site roles.

##### Administrators
Admins have the ability to do almost anything except user management and limited capabilities in a contest environment.

#### Section Roles
Section roles are roles that exist only inside of their class (section).

##### Teachers
Teachers control a class. They can view the entire classes’ progress, see all submissions, export grades, and create assignments.

##### Judges
Judges are like teachers, but on a contest level. They are able to control all aspects of a contest, like judging submissions, answering questions, and creating problems.

##### Students
Students can view the classes they are in, see the assignments that are open, and submit their code to receive instant feedback on those problems. They are only able to see their own information.

## Authentication
Authentication is provided through Google OAuth, since we use Google for Cedarville email accounts. We used the [Friends of Symfony User Bundle](https://github.com/FriendsOfSymfony/FOSUserBundle) to allow us to get authentication information from Google into Symfony. 

Most of Gradel requires a user to be logged in at the very least. Anyone with a Google account can log in, but unless they are assigned to a section, they will not be able to do much.

Authentication is done mostly on a routes level. Checks will be done to make sure users are able to be on whatever page they are trying to get to.

## Instructions for Professors

### Edit a User Profile (Super User Only)
1. From the Homepage, click the `Users` button on the top nav bar.
2. Filter users using the search box on the right.
3. Double-click whichever field you wish to edit. Roles can be deleted by clicking on the role you wish to revoke. This role can then be readded from the dropdown.
4. Click `Save` to persist changes.

### Course Manipulation
#### Create a Course/Contest (Super User/Admin Only)
1. Click the `Courses` button on the top nav bar.
2. Click the circular plus button on the left nav bar to go to the New Course page.
3. Fill out the appropriate fields. Note: Code refers to the course abbreviation (e.g. `CS-1210`).
4. If you are creating a contest, be sure to set the toggle appropriately.
5. Click `Save`.

#### Edit/Delete a Course/Contest (Super User/Admin Only)
1. Click the course you wish to edit from the left nav bar on the `Courses` page.
2. Edit the fields you wish to edit or use the delete button to "delete" the course. (In reality, deleting simply hides the course and it's sections, but can be reinstated by editing the course again and clicking the reinstate button).
3. Click `Save`.

### Section Manipulation
#### Create a Section (Super User/Admin Only)
1. From the homepage, click the circular plus button on the left nav bar to go to the New Section page.
2. Fill out the appropriate fields, using the course dropdown to select the proper course. If you select a Contest, the page will redirect to the New Contest page.
3. Add linked sections if you wish to have more than one section that share characteristics. They will have their own students and teachers, but any assignments and problems will be shares among them.
4. Add teachers and students. If the teachers csvs are blank, it will default to the creator of the section as a teacher.
5. Click `Save`.

#### Edit/Delete a Section (Teachers)
1. Navigate to the Edit Section page by either clicking on the edit pencil on the left nav on the homepage or by clicking on the `Edit Section` button on the left nav on the Section page.
2. Edit the fields you wish to change.
3. Click `Save`.

#### Cloning a Section
Cloning a section can be useful when you want to copy over the assignments from a previous semester. It will copy all linked sections and empty out the teachers and students. You can clone a section by going to the Edit Section page and pushing the `Clone Section` button on the left nav bar.

It is important to note that once you push the `Clone Section` button on the Edit Section page it is persisted to the database. This means that if you change your mind, you need to delete the newly clones section.

### Assignment Manipulation
1. From the Section page, click the circular plus button on the left nav bar to go to the New Assignment page or click the edit pencil button to edit an existing assignment. You can also edit an assignment by clicking the `Edit Assignment` from the Assignment page.
2. Fill out the fields!
    -  The weight field sets a relative weight compared to other assignments in the section. For example, if other assignments are weighted 1, weighting this new assignment 1 will set it as equal importance in determining grades.
    -  Students are set to be on their own team by default. To create teams, drag students around into other boxes to reorganize teams. Right-click to send users back to the pool.
3. Click `Save`.

### Problem/Testcase Manipulation
1. From the Assignment page, click the circular plus button on the left nav bar to go to the New Problem page or click the edit pencil button to edit an existing problem.
2. Fill out the fields!
    - Choose the languages you want students to be able use by clicking on them. By default, all languages are allowed. To remove a language, click on the language tab then select 'Remove [Language].' 
        - Compiler options are for special inheritence cases or weird linking scenarios.
        - Default code can be assigned on a language basis by uploading code. If no file is provided, it will use the previously assigned default code. If default code was never provided it will use the languages default code, which is usually an empty main program.
    - Grading options can be set to allow for total customizability
        - 'Total Attempts' defines how many tries students have
        - 'Attempts Before Penalty' specifies how many attempts before students lose percentage points as defined in 'Penalty Per Attempt'.
        - Feedback options are pretty self-explanatory.
    - Weights can be assigned in the same manner as assignment weights.
    - Time Limit sets the time limit for each testcase.
    - Additional files allows students to use more than one file in the Gradel IDE.
    - Uploading files refers to the provided file uploader that populates the editor.
    - Testcases have options to specify weight and what type of input it is.
        - Testcases can be created by clicking on the giant plus. 
        - There are options for specifying feedback for the student that can be displayed if they get it wrong. 
        - Testcases can be rearranged by dragging the cards.
    - The custom validator can be implemented to allow for totally custom means of grading student code. Be careful, if the validator function contains an error, you will get an internal server error, so make sure to test it.
    - You can use the 'Generate Output' option to automatically build output from your own code. It will take the testcases with input provided and generate output.
3. Click `Save`.

### Impersonating a Student (Super Users Only)
Impersonation is a useful tool to be able to see and run a student’s code by giving you the view of the student. Any code submitted or testing done will appear as though it is done by the student, so caution is advised. This is only available to professors of the course that the student is taking. It is recommended you only do so with the student’s permission.

1. Navigate to the Section page of the class the student is having trouble with.
2. Click the 'Impersonate' dropdown in the top nav bar and select the person you want to impersonate, typing to quickly find their name.
3. You will then become this student, seeing exactly what they see.
4. Click `Exit Impersonation` on the top nav bar to return to yourself.

### View/Export Grades
On the Section page, you can see an overview of the students' grdes on all the assignments. Clicking `Download Grades Overview` will download the entire course grades as a CSV file.

To see how the class did on an individual problem, scroll to the Assignment Grades and choose a specific assignment from the dropdown. You can click `Download Assignment Grades` will download the grades as a CSV file.

### View Submissions
From the Section page, you can search for submission in the 'Submissions' card at the bottom. Use the search box to filter results by student name, submission id, problem or assignment name, and result.

## Instructors for Students

### See Assignments
From the homepage you can see which assignments are due in the next two weeks. You can either navigate by clicking on that or by navigating to specific sections and seeing what is due in there.

### Working on Code
It is recommended that you develop your code outside of Gradel and then copy and paste or upload your code into our online IDE. 

If you choose to develop in our environment, you can do a lot of neat things!
- You can click the plus tab to add a new file.
- Rename files by double-clicking on the name in the tab.
- Right-click tabs to remove the file. If you remove all the files, the code will revert back to the default code
- Drag the grey bar at the bottom of the editor to resize the environment.
- Your code automatically saves! Use Ctrl+S or wait for the little notice at the bottom to make sure it is saved. This saving also saves editor height and whether or not you hid the description.
- Download the current file with the 'Download File' button.