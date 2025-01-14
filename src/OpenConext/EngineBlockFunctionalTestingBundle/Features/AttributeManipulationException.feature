Feature:
  In order to be to influence the released attribute values
  As an IdP or SP
  I want to be able to throw a custom exception through configured code

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy-IdP"
      And an Identity Provider named "IdP-with-Attribute-Manipulations"
      And a Service Provider named "Dummy-SP"
      And a Service Provider named "SP-with-Attribute-Manipulations"

  Scenario: The Service Provider can have an attribute added
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
$e = new EngineBlock_Attributes_Manipulator_CustomException("AM_ERROR Authorization Incorrect _ Affilliation Incorrect", EngineBlock_Attributes_Manipulator_CustomException::CODE_NOTICE);
$e->setFeedbackTitle(array("nl" => "Autorisatie Incorrect", "en" => "Authorization Incorrect"));
$e->setFeedbackDescription(array(
    "en" => 'This user does not have access to desired service. ' .
        'Contact the system administrator.',
    "nl" => 'Deze gebruikersnaam heeft geen toegang tot de gewenste dienst. ' .
        'Neem contact op met de systeem beheerder. '
  ));
throw $e;
      """
    When I log in at "SP-with-Attribute-Manipulations"
      And I select "Dummy-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
    Then I should see "Authorization Incorrect"
      And I should see "This user does not have access to desired service. Contact the system administrator."
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"

#
#  Scenario: Sp and IdP attribute manipulation exception
