Feature: Provisioning
    In order to be able to supply data of users
    As engineblock
    I need to provision user data

    Background:
       Given we have a SURFguest user with the username "lucas-test2", name "Lucas" and password "_welkom!"

    Scenario: Lucas logs in at the Portal and his data is registered
        When I go to the Test SP
        And I select from the WAYF "SURFguest (TEST)"
        And I log in as "lucas-test2" with password "_welkom!"
        And I pass through EngineBlock
        And I should be on the Test SP
        Then I should be able to retrieve user "lucas-test2" via open social api

    Scenario: Lucas changes his lastname to a dummy value
        When I go to "https://test.surfguest.nl/user/edit"
        And I log in at SURFguest as "lucas-test2" with password "_welkom!"
        And I fill in "abcdef" for "last_name"
        And I press "edit"
        And I check for form errors

        And I go to the Test SP
        And I select from the WAYF "SURFguest (TEST)"
        And I log in as "lucas-test2" with password "_welkom!"
        And I press "I Accept"
        And I pass through EngineBlock

        Then the open social attribute "name.familyName" of user "lucas-test2" should be "abcdef"

    # Reset last name to make sure is changed again during next run
    Scenario: Lucas changes his lastname back to his real lastname
        When I go to "https://test.surfguest.nl/user/edit"
        And I log in at SURFguest as "lucas-test2" with password "_welkom!"
        And I fill in "van Lierop" for "last_name"
        And I press "edit"
        And I check for form errors

        And I go to the Test SP
        And I select from the WAYF "SURFguest (TEST)"
        And I log in as "lucas-test2" with password "_welkom!"
        And I press "I Accept"
        And I pass through EngineBlock

        Then the open social attribute "name.familyName" of user "lucas-test2" should be "van Lierop"
