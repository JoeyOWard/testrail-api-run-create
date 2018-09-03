EntanetQA TestRail API Run Creator

Why?
----------------

This was made to help with pipelining Behat testing + testrail integration.

The following package leverages BehatTestrailreporter but automatically:

Creates a new test run for the project \
Uses the latest test suite for the test run \
Uses the currently active milestone for the test run \
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
            customFields:
                custom_environment: '1'
                
Use
----------------

Call CreateRun from project root. Append your project in testrail as a param

```bash
$> vendor/entanet-qa/testrail-api-run-create/src/createrun --name='Testing'
```

Then call behat, if you have setup BehatTestrailReporter correctly, your new run on testrail wil be automatically updated. 


Known Issues
----------------

Validation looks at behat.yml and asesses if has correct information and if keys and values are present and populated \
Currently does not validate if value format is correct \

 

Notes
----------------

GLHF :) :)

Does not write tests for you....