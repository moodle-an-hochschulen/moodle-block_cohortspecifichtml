@block @block_cohortspecifichtml @editor_tiny
Feature: Serving files of Text (on cohorts) blocks
  In order to use images in Text (on cohorts) blocks
  As a user
  I need to get the block's files served if I am allowed to see or edit the block

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
    And the following "cohorts" exist:
      | name | idnumber |
      | 1-2  | 12       |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | 12     |

  @javascript @_file_upload
  Scenario: Place an image in a Text (on cohorts) block and serve it to the members of the configured cohort
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student3 | C1     | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Text block title" to "Cohort image block"
    And I click on the "Image" button for the "Content" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert image" "dialogue"
    And I upload "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png" to the file picker for TinyMCE
    And I set the field "altentry" to "Cohort image"
    And I click on "Save" "button" in the "Image details" "dialogue"
    And I set the field "Show to cohorts" to "1-2"
    And I press "Save changes"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then "//img[@alt='Cohort image']" "xpath_element" should exist
    And the image at "//img[@alt='Cohort image']" "xpath_element" should be identical to "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png"
    And the file "cohortspecifichtml.png" of the "Cohort image block" Text (on cohorts) block should be served
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And "//img[@alt='Cohort image']" "xpath_element" should not exist
    And the file "cohortspecifichtml.png" of the "Cohort image block" Text (on cohorts) block should not be served

  @javascript @_file_upload
  Scenario Outline: Place an image in a Text (on cohorts) block on the site homepage and serve it to visitors, guests and users depending on the cohort configuration
    Given the following config values are set as admin:
      | forcelogin   | 0 |
      | enablemyhome | 1 |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Text block title" to "Frontpage image block"
    And I click on the "Image" button for the "Content" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert image" "dialogue"
    And I upload "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png" to the file picker for TinyMCE
    And I set the field "altentry" to "Frontpage image"
    And I click on "Save" "button" in the "Image details" "dialogue"
    And I set the field "Show to cohorts" to "1-2"
    And I set the field "Invert the selection (hide it for the selected cohorts)" to "<invert>"
    And I press "Save changes"
    And I log out
    # Visitor who is not logged in.
    When I am on site homepage
    Then "//img[@alt='Frontpage image']" "xpath_element" <guest> exist
    And the file "cohortspecifichtml.png" of the "Frontpage image block" Text (on cohorts) block <guest> be served
    # Guest.
    And I log in as "guest"
    And I am on site homepage
    And "//img[@alt='Frontpage image']" "xpath_element" <guest> exist
    And the file "cohortspecifichtml.png" of the "Frontpage image block" Text (on cohorts) block <guest> be served
    And I log out
    # Member of the configured cohort.
    And I log in as "student1"
    And I am on site homepage
    And "//img[@alt='Frontpage image']" "xpath_element" <member> exist
    And the file "cohortspecifichtml.png" of the "Frontpage image block" Text (on cohorts) block <member> be served
    And I log out
    # User who is not a member of the configured cohort.
    And I log in as "student3"
    And I am on site homepage
    And "//img[@alt='Frontpage image']" "xpath_element" <nonmember> exist
    And the file "cohortspecifichtml.png" of the "Frontpage image block" Text (on cohorts) block <nonmember> be served

    Examples:
      | invert | guest      | member     | nonmember  |
      | 0      | should not | should     | should not |
      | 1      | should     | should not | should     |

  @javascript @_file_upload
  Scenario Outline: Counter-check: Serve an image of a Text (on cohorts) block to a user who is not a member of the configured cohort but edits the block on their dashboard
    Given the following config values are set as admin:
      | forcelogin | <forcelogin> |
    And the following "permission overrides" exist:
      | capability                             | permission | role | contextlevel | reference |
      | block/cohortspecifichtml:myaddinstance | Allow      | user | System       |           |
    And I log in as "student3"
    And I follow "Dashboard"
    And I turn editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Text block title" to "Dashboard image block"
    And I click on the "Image" button for the "Content" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert image" "dialogue"
    And I upload "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png" to the file picker for TinyMCE
    And I set the field "altentry" to "Dashboard image"
    And I click on "Save" "button" in the "Image details" "dialogue"
    And I set the field "Show to cohorts" to "1-2"
    And I press "Save changes"
    Then "//img[@alt='Dashboard image']" "xpath_element" should exist
    And the image at "//img[@alt='Dashboard image']" "xpath_element" should be identical to "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png"
    And the file "cohortspecifichtml.png" of the "Dashboard image block" Text (on cohorts) block should be served
    And I turn editing mode off
    And "//img[@alt='Dashboard image']" "xpath_element" should not exist
    And the file "cohortspecifichtml.png" of the "Dashboard image block" Text (on cohorts) block should not be served

    Examples:
      | forcelogin |
      | 0          |
      | 1          |

  @javascript @_file_upload
  Scenario Outline: Counter-check: Serve an image of a Text (on cohorts) block to a user who is not a member of the configured cohort but edits the block on their profile page
    Given the following config values are set as admin:
      | forcelogin | <forcelogin> |
    And the following "permission overrides" exist:
      | capability                           | permission | role | contextlevel | reference |
      | block/cohortspecifichtml:addinstance | Allow      | user | System       |           |
    And I log in as "student3"
    And I follow "Profile" in the user menu
    And I turn editing mode on
    And I add the "Text (on cohorts)..." block
    And I set the field "Text block title" to "Profile image block"
    And I click on the "Image" button for the "Content" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert image" "dialogue"
    And I upload "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png" to the file picker for TinyMCE
    And I set the field "altentry" to "Profile image"
    And I click on "Save" "button" in the "Image details" "dialogue"
    And I set the field "Show to cohorts" to "1-2"
    And I press "Save changes"
    Then "//img[@alt='Profile image']" "xpath_element" should exist
    And the image at "//img[@alt='Profile image']" "xpath_element" should be identical to "blocks/cohortspecifichtml/tests/fixtures/cohortspecifichtml.png"
    And the file "cohortspecifichtml.png" of the "Profile image block" Text (on cohorts) block should be served
    And I turn editing mode off
    And "//img[@alt='Profile image']" "xpath_element" should not exist
    And the file "cohortspecifichtml.png" of the "Profile image block" Text (on cohorts) block should not be served

    Examples:
      | forcelogin |
      | 0          |
      | 1          |
