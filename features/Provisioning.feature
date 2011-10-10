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

