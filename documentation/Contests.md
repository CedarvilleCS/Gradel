# Contests

## Table of Contents


_**NOTE: The contest WebSocket does not work off campus. You must be on campus for the Gradel contesnt environment to work. This can be remedied by opening up port 8080, or whichever one you are using for the WebSocket during setup**_


Note: This guide is not meant to give a large overview of everything that happens, but just give a few words about how some things work. We think the contest environment is pretty intuitive once you get inside it, so minutia will not be explained here.

## Overview
### Purpose
The contest environment is a specially designed set of views that give professors and administrators the ability to run contests on the Gradel website. Most of the backend compilation and database storing of information remains the same, but different webpages are created to give the proper information displayed for judges and students.

### Roles
A contest is broken up into two main roles - those taking (Takes) the contest and those judging it (Judges). 

#### Taker / Team Member
The takers are able to submit solutions for problems while the contest is active as a team, see a live-updating scoreboard of teams and their submission results, and ask questions about problems or the contest in general. They have limited permissions, similar to those of a student.

#### Judger
A judge has full control over the contest. They are able to define problems, grade submissions, and answer questions. They can navigate to any page any time they want and edit them appropriately, similar to the role of teacher in a section. Some additional functionality allows them to impersonate contestants and be on a team and judge at the same time.

#### Director (Not Implemented)
Future work calls for a director role, who would replace the judge in editing problems and setting times. He would be in charge of setting judges and teams and problems, while the judges can only grade submissions and see testcases. This way, judges are not able to change small details of a contest and have limited privileges. 

## Creating a Contest

1. Create a new section, but select a contest as the course. You will be redirected to a Contest Creation page.
2. Assign a name to the contest.
3. You can now set how many contests you would like to run. 
    - It defaults to a Practice Contest and Actual Contest, but you can 'Add Another Contest' or delete them by clicking on the trash can icon. 
    - You can change the names by clicking on them and typing.
    - Set the times, but remember that the previous contest must end before the next one starts.
    - Set the time that the scoreboard freezes for the contestants per contest.
4. You can toggle which languages to support by clicking on them.
    - It defaults to C++ and Java, but the ACM contests support Python 3 as well.
5. Decide if you want a Pre-Contest
    - The Pre-Contest option allows for an additional contest to be created that is open for a few weeks before the contest starts.
    - The purpose is to allow those taking it to become familiar with the environment. Judges should add simple problems that can be easily solves so takers feel comfortable using Gradel.
6. Decide if you want a Post-Contest
    - The Post-Contest option allows for an additional contest to be created that is open once the actual contest (the final one in the list on the edit page) is over. Once activated, it will copy over all of the submissions from the actual contest and give the contestants a few months to work on the problems whenever they want.
    - Submissions in this contest are automatically graded without a judge.
    - Questions cannot be asked, but those asked during the actual contest will carry over.
    - There are no penalty minutes or scoreboard freeze, but the scoreboard will still show when they were solved.
7. Set the penalty points for different types of wrong submissions.
    - The default values are those used at the ACM ICPC contests.
8. Add Judges and Teams
    - Type an email address in the box and hit enter or press the plus to create them
    - Drag them to a team or the Judges panel.
    - If no email tag ("@gmail.com") is given, it will default to use "@cedarville.edu". 
    - Change the team name by clicking the 'Team Name' box. 
    - Change the workstation number if you want to.
9. Hit "Save"

## How Scoreboard Calculation Works
The leaderboard is calculated using the following tiebreakers.

1. The team with the most correct submissions
2. The team with the fewest penalty points
3. The team with the quickest solution for their most-recent problem, 2nd-most-recent, 3rd-most-recent....
4. Alphabetical order by team name.

The chances of the 3rd tiebreaker being equal is amazingly small, so the 4th one is used mainly to keep the names in alphabetical order when the contest is first starting. Official ACM rules call for a coin toss if that 3rd tiebreaker is not able to distinguish teams.