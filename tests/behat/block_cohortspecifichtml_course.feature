@block @block_cohortspecifichtml
Feature: HTML (on cohorts) blocks in a course
  In order to have one or multiple HTML (on cohorts) blocks in a course
  As a teacher
  I need to be able to create and change such blocks

  @javascript
  Scenario: Adding HTML (on cohorts) block in a course without any cohorts defined yet
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "First block content"
    And I set the field "HTML block title" to "First block header"
    Then I should see "No cohorts are defined yet. Without any cohorts the block will not be visible to anybody, even if you have entered content above."
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should not see "First block header"
