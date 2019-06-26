@block @block_cohortspecifichtml
Feature: HTML (on cohorts) blocks in a course with several cohort restrictions
  In order to have one or multiple HTML (on cohorts) blocks in a course
  As a teacher
  I need to be able to create and change such blocks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | student4 | Sam4      | Student4 | student4@example.com |
    And the following "cohorts" exist:
      | name    | idnumber |
      | 1-2     | 12       |
      | 3-4     | 34       |
      | teacher | 5        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
    And the following "cohort members" exist:
      | user     | cohort   |
      | student1 | 12       |
      | student2 | 12       |
      | student3 | 34       |
      | student4 | 34       |
      | teacher1 | 5        |

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course and restrict visibility to cohort "1-2"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-2 content"
    And I set the field "HTML block title" to "Cohort 1-2 header"
    And I set the field "id_config_cohorts" to "1-2"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Cohort 1-2 content" in the "Cohort 1-2 header" "block"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should not see "Cohort 1-2 header"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course and restrict visibility to cohort "3-4"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 3-4 content"
    And I set the field "HTML block title" to "Cohort 3-4 header"
    And I set the field "id_config_cohorts" to "3-4"
    And I press "Save changes"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should see "Cohort 3-4 content" in the "Cohort 3-4 header" "block"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Cohort 3-4 header"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course and restrict visibility to multiple cohorts
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-4 content"
    And I set the field "HTML block title" to "Cohort 1-4 header"
    And I set the field "id_config_cohorts" to "1-2, 3-4"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Cohort 1-4 content" in the "Cohort 1-4 header" "block"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should see "Cohort 1-4 content" in the "Cohort 1-4 header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course, select no cohorts and invert the selection
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort (none) inverted content"
    And I set the field "HTML block title" to "Cohort (none) inverted header"
    And I set the field "config_invertcohortselection" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Cohort (none) inverted content" in the "Cohort (none) inverted header" "block"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should see "Cohort (none) inverted content" in the "Cohort (none) inverted header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course, select cohort "1-2" and invert the selection
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-2 inverted content"
    And I set the field "HTML block title" to "Cohort 1-2 inverted header"
    And I set the field "id_config_cohorts" to "1-2"
    And I set the field "config_invertcohortselection" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Cohort 1-2 inverted header"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should see "Cohort 1-2 inverted content" in the "Cohort 1-2 inverted header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course, select cohorts "1-2" and "3-4" and invert the selection
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-4 inverted content"
    And I set the field "HTML block title" to "Cohort 1-4 inverted header"
    And I set the field "id_config_cohorts" to "1-2, 3-4"
    And I set the field "config_invertcohortselection" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Cohort 1-4 inverted header"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I should not see "Cohort 1-4 inverted header"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course with capability block/cohortspecifichtml:viewalways and select cohort "1-2"
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
    | capability                          | permission |
    | block/cohortspecifichtml:viewalways | Allow      |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-2 content"
    And I set the field "HTML block title" to "Cohort 1-2 header"
    And I set the field "id_config_cohorts" to "1-2"
    And I press "Save changes"
    And I should see "Restricted" in the "Cohort 1-2 header" "block"
    And I should see "Only visible to cohorts:" in the "Cohort 1-2 header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course with capability block/cohortspecifichtml:viewalways and select cohort "1-2" and invert this
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                          | permission |
      | block/cohortspecifichtml:viewalways | Allow      |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-2 inverted content"
    And I set the field "HTML block title" to "Cohort 1-2 inverted header"
    And I set the field "id_config_cohorts" to "1-2"
    And I set the field "config_invertcohortselection" to "1"
    And I press "Save changes"
    And I should see "Restricted" in the "Cohort 1-2 inverted header" "block"
    And I should see "Not visible to cohorts:" in the "Cohort 1-2 inverted header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course with capability block/cohortspecifichtml:viewalways and select no cohort and invert this
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                          | permission |
      | block/cohortspecifichtml:viewalways | Allow      |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "No cohort inverted content"
    And I set the field "HTML block title" to "No cohort inverted header"
    And I set the field "config_invertcohortselection" to "1"
    And I press "Save changes"
    And I should see "Unrestricted" in the "No cohort inverted header" "block"
    And I should see "This block is visible to all users." in the "No cohort inverted header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course with capability block/cohortspecifichtml:viewalways and select no cohort and save this
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                          | permission |
      | block/cohortspecifichtml:viewalways | Allow      |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "No cohort content"
    And I set the field "HTML block title" to "No cohort header"
    And I press "Save changes"
    And I should see "Restricted" in the "No cohort header" "block"
    And I should see "This block is not visible to any user." in the "No cohort header" "block"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course without capability block/cohortspecifichtml:viewalways, select a cohort I'm not a member in and save this
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                          | permission |
      | block/cohortspecifichtml:viewalways | Prevent    |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort 1-2 content"
    And I set the field "HTML block title" to "Cohort 1-2 header"
    And I set the field "id_config_cohorts" to "1-2"
    And I press "Save changes"
    Then I should see "Restricted" in the "Cohort 1-2 header" "block"
    And I should see "Cohort 1-2 content" in the "Cohort 1-2 header" "block"
    And I turn editing mode off
    Then I should not see "Cohort 1-2 header"

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course without capability block/cohortspecifichtml:viewalways, select a cohort I'm a member in and save this
    When I log in as "admin"
    And I set the following system permissions of "Teacher" role:
      | capability                          | permission |
      | block/cohortspecifichtml:viewalways | Prevent    |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Cohort Teacher content"
    And I set the field "HTML block title" to "Cohort Teacher header"
    And I set the field "id_config_cohorts" to "teacher"
    And I press "Save changes"
    Then I should see "Restricted" in the "Cohort Teacher header" "block"
    And I should see "Cohort Teacher content" in the "Cohort Teacher header" "block"
    And I turn editing mode off
    Then I should see "Cohort Teacher content" in the "Cohort Teacher header" "block"
