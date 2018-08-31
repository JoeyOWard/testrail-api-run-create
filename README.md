EntanetQA TestRail API Run Creator

Why?
----------------

This was made to help with pipelining Behat testing + testrail integration.

The following package leverages BehatTestrailreporter but automatically:

Creates a new test run for the project
Uses the latest test suite for the test run
Uses the currently active milestone for the test run
Updates and reformats the Behat.yml so that the runId is the newly created run 

Installing
----------------

```bash
$> composer require entanet-qa/testrail-api-run-create
```


Ensure that you have setup behat.yml to include:


     flexperto\BehatTestrailReporter\TestrailReporterExtension:
            enabled: true
            baseUrl: Appropriate TestRail API url
            testidPrefix: test_rail_
            username: Your Username
            apiKey: Your API key
            runId:
            customFields:
                custom_environment: '1'
                
Use
----------------

Call CreateRun from project root. Append your project in testrail as a param

```bash
$> php vendor/entanet-qa/testrail-api-run-create/createRun.php --name='Testing'
```

Then call behat, if you have setup BehatTestrailReporter correctly, your new run on testrail wil be automatically updated. 


Problems
----------------


Validation still needs work, this is a very early version


Notes
----------------

GLHF :) :)

Does not write tests for you....