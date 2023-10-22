@block @block_cohortspecifichtml
Feature: Adding and configuring Text (on cohorts) blocks
  In order to have custom blocks on a page
  As admin
  I need to be able to create, configure and change Text (on cohorts) blocks

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
  Scenario: Configuring the Text (on cohorts) block with Javascript on
    When I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    # Basically, we just want to add a block outside a course.
    # This could be done on the site homepage.
    # However, there may be user tours or other side effects which prevent us
    # from adding a block right away.
    # Thus, we proceed to course index which is problem-free.
    And I am on course index
    And I add the "Text (on cohorts)..." block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "New Text (on cohorts)"
    And I configure the "block_cohortspecifichtml" block
    And I set the field "Text block title" to "The Text block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_cohortspecifichtml" "block" should exist
    And "The Text block header" "block" should exist
    Then I should see "Static text with a header" in the "The Text block header" "block"

  Scenario: Configuring the Text (on cohorts) block with Javascript off
    When I log in as "admin"
    And I am on site homepage
    When I turn editing mode on
    And I add the "Text (on cohorts)" block
    And I configure the "New Text (on cohorts)" block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "New Text (on cohorts)"
    And I configure the "block_cohortspecifichtml" block
    And I set the field "Text block title" to "The Text block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_cohortspecifichtml" "block" should exist
    And "The Text block header" "block" should exist
    Then I should see "Static text with a header" in the "The Text block header" "block"
