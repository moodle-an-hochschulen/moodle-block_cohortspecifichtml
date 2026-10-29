@block @block_cohortspecifichtml
Feature: Selecting cohorts in Text (on cohorts) blocks
  In order to restrict the visibility of Text (on cohorts) blocks without disclosing cohorts
  As a teacher
  I need to be offered only the cohorts which I am allowed to view

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
      | Category 2 | 0        | CAT2     |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "cohorts" exist:
      | name                 | idnumber | visible | contextlevel | reference |
      | System visible       | SV       | 1       | System       |           |
      | System hidden        | SH       | 0       | System       |           |
      | Category 1 visible   | C1V      | 1       | Category     | CAT1      |
      | Category 2 visible   | C2V      | 1       | Category     | CAT2      |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | CAT1     |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | SH     |

  @javascript
  Scenario: A teacher is only offered the visible cohorts from the parent contexts of the block
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Text (on cohorts)..." block
    Then the "Show to cohorts" select box should contain "System visible"
    And the "Show to cohorts" select box should contain "Category 1 visible"
    And the "Show to cohorts" select box should not contain "System hidden"
    And the "Show to cohorts" select box should not contain "Category 2 visible"

  @javascript
  Scenario: A teacher with the selecthiddencohorts capability is offered the hidden cohorts from the parent contexts as well
    Given the following "permission overrides" exist:
      | capability                                   | permission | role           | contextlevel | reference |
      | block/cohortspecifichtml:selecthiddencohorts | Allow      | editingteacher | Course       | C1        |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Text (on cohorts)..." block
    Then the "Show to cohorts" select box should contain "System visible"
    And the "Show to cohorts" select box should contain "Category 1 visible"
    And the "Show to cohorts" select box should contain "System hidden"
    And the "Show to cohorts" select box should not contain "Category 2 visible"

  @javascript
  Scenario: A teacher editing a block is offered the configured cohorts which the teacher is not allowed to select and keeps them
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Content" to "Hidden cohort content"
    And I set the field "Text block title" to "Hidden cohort header"
    And I set the field "Show to cohorts" to "System hidden"
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I configure the "Hidden cohort header" block
    And the "Show to cohorts" select box should contain "System hidden"
    And the field "Show to cohorts" matches value "System hidden"
    And I set the field "Content" to "Changed hidden cohort content"
    And I press "Save changes"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Changed hidden cohort content" in the "Hidden cohort header" "block"

  @javascript
  Scenario: A teacher editing a block removes a configured cohort which the teacher is not allowed to select
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Content" to "Hidden cohort content"
    And I set the field "Text block title" to "Hidden cohort header"
    And I set the field "Show to cohorts" to "System hidden"
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I configure the "Hidden cohort header" block
    And I set the field "Show to cohorts" to "System visible"
    And I press "Save changes"
    And I am on "Course 1" course homepage
    When I configure the "Hidden cohort header" block
    Then the "Show to cohorts" select box should not contain "System hidden"
    And the field "Show to cohorts" matches value "System visible"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Hidden cohort header"
