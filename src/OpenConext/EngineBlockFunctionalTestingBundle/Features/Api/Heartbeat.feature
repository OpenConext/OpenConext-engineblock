Feature:
  In order to determine whether EngineBlock is available
  As a client service or availability monitor
  I want to be able to check EngineBlock's heartbeat

  @WIP
  Scenario: EngineBlock returns a heartbeat
     When I check EngineBlock's heartbeat
     Then the response status code should be 200
