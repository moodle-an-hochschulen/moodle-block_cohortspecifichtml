@block @block_cohortspecifichtml
Feature: Adding and configuring HTML (on cohorts) blocks
  In order to have custom blocks on a page
  As admin
  I need to be able to create, configure and change HTML (on cohorts) blocks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher@example.com  |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | student4 | Sam4      | Student4 | student4@example.com |
    And the following "cohorts" exist:
      | name | idnumber |
      | 1-2  | 12       |
      | 3-4  | 34       |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | 12     |
      | student2 | 12     |
      | student3 | 34     |
      | student4 | 34     |

  @javascript
  Scenario: Configuring the HTML (on cohorts) block with Javascript on
    When I log in as "admin"
    And I am on site homepage
    When I turn editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "new HTML (on cohorts)"
    And I configure the "block_cohortspecifichtml" block
    And I set the field "HTML block title" to "The HTML block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_cohortspecifichtml" "block" should exist
    And "The HTML block header" "block" should exist
    Then I should see "Static text with a header" in the "The HTML block header" "block"

  Scenario: Configuring the HTML (on cohorts) block with Javascript off
    When I log in as "admin"
    And I am on site homepage
    When I turn editing mode on
    And I add the "HTML (on cohorts)" block
    And I configure the "new HTML (on cohorts)" block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "new HTML (on cohorts)"
    And I configure the "block_cohortspecifichtml" block
    And I set the field "HTML block title" to "The HTML block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_cohortspecifichtml" "block" should exist
    And "The HTML block header" "block" should exist
    Then I should see "Static text with a header" in the "The HTML block header" "block"
