<?php
/**
 * Created by PhpStorm.
 * User: joeward
 * Date: 22/08/2018
 * Time: 14:33
 */

namespace TestrailAPIRunCreate;

use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Symfony\Component\Yaml\Yaml;

class GenerateTestRun{


    private $baseUrl;
    private $username;
    private $apiKey;
    private $projectname;
    private $yaml;
    private $milestone;


    /**
     * GenerateTestRun constructor.
     * @param $projectName
     * @throws TestRailRunCreateException
     */
    public function __construct($projectName){
        
        $this->readYaml();

        if($projectName == null){
            throw new TestRailRunCreateException('Project name is null');

        }
        $this->projectname = $projectName;

    }


    public function readYaml(){


        $yaml = (Yaml::parseFile(getcwd().'/behat.yml'));

        $this->yaml = $yaml;
        $this->parseYaml();

        $extensions = $this->yaml['default']['extensions']['flexperto\BehatTestrailReporter\TestrailReporterExtension'];

        $this->baseUrl = $extensions['baseUrl'];
        $this->username = $extensions['username'];
        $this->apiKey = $extensions['apiKey'];
        
        printf("Behat Yaml read successfully...\n");

    }


    public function parseYaml(){

        if(array_key_exists('default', $this->yaml)){

            if(array_key_exists('extensions', $this->yaml['default'])){

                if(array_key_exists('flexperto\BehatTestrailReporter\TestrailReporterExtension', $this->yaml['default']['extensions'])){

                    $extensionyaml = $this->yaml['default']['extensions']['flexperto\BehatTestrailReporter\TestrailReporterExtension'];

                    if(!(array_key_exists('baseUrl', $extensionyaml))){
                        throw new TestRailRunCreateException("Behat yaml is missing baseUrl, test run creation aborting...\n");
                    }else{
                        if(!(isset($extensionyaml['baseUrl']))){
                            throw new TestRailRunCreateException("Behat yaml is missing baseUrl value, test run creation aborting...\n");
                        }
                    }
                    if(!(array_key_exists('username', $extensionyaml))){
                        throw new TestRailRunCreateException("Behat yaml is missing username, test run creation aborting...\n");


                    }else{
                        if(!(isset($extensionyaml['username']))){
                            throw new TestRailRunCreateException("Behat yaml is missing username value, test run creation aborting...\n");
                        }
                    }

                    if(!(array_key_exists('apiKey', $extensionyaml))){

                        throw new TestRailRunCreateException("Behat yaml is missing apiKey, test run creation aborting...\n");
                    }else{

                        if(!(isset($extensionyaml['apiKey']))){
                            throw new TestRailRunCreateException("Behat yaml is missing apiKey value, test run creation aborting...\n");
                        }
                    }
                    if(!(array_key_exists('runId', $extensionyaml))){

                        throw new TestRailRunCreateException("Behat yaml is missing runId, test run creation aborting...\n");
                    }

                } else{
                    throw new TestRailRunCreateException("Behat yaml is missing flexperto testrailreporterextension, test run creation aborting...\n");
                }

            } else{

                throw new TestRailRunCreateException("Behat yaml is missing extensions inside default, test run creation aborting...\n");

            }


        }else{

            throw new TestRailRunCreateException("Behat yaml is missing default, test run creation aborting...\n");
        }


    }

    /**
     * @throws TestRailRunCreateException
     */
    public function createNewRun(){

        $this->findAppropriateProject();


    }

    /**
     * @throws TestRailRunCreateException
     */
    public function findAppropriateProject(){

        $projectResponse = null;
        $correctProject = null;
        $request = Request::init();


        try {
            $projectResponse = $request
                ->uri("{$this->baseUrl}/get_projects")
                ->contentType('application/json')
                ->basicAuth($this->username, $this->apiKey)
                ->method("GET")
                ->send();


        } catch (ConnectionErrorException $e) {
            throw new TestRailRunCreateException('Cannot find project via api');
        }

        $decodedResponse = (json_decode($projectResponse));

        foreach($decodedResponse as $decodedProject){

            if($decodedProject->name == $this->projectname) {

                $correctProject = $decodedProject;

            }
        }


        printf ("Project found successfully...\n");
        $this->findCorrectMilestone($correctProject);
        $this->findLatestSuite($correctProject);

    }

    /**
     * @param $correctProject
     * @throws TestRailRunCreateException
     */
    public function findCorrectMilestone($correctProject){


        $milestoneResponse = null;
        $milestoneResponse = null;
        $request = Request::init();
        try {
            $milestoneResponse = $request
                ->uri("{$this->baseUrl}/get_milestones/{$correctProject->id}&is_completed=0")
                ->contentType('application/json')
                ->basicAuth($this->username, $this->apiKey)
                ->method("GET")
                ->send();
        } catch (ConnectionErrorException $e) {
            throw new TestRailRunCreateException('Cannot find test suite via api');
        }

        $decodedResponse = (json_decode($milestoneResponse));


        if(!Empty($decodedResponse)){

            if(count($decodedResponse) == 1) {

                $this->milestone = ($decodedResponse[0]->id);
                printf("Milestone found successfully...\n");

            }
            else{
                throw new TestRailRunCreateException('Invalid number of Milestones returned. Create a milestone or complete legacy versions');
            }
        }

    }


    /**
     * @param $correctProject
     * @throws TestRailRunCreateException
     */
    public function findLatestSuite($correctProject){

        $suiteResponse = null;

        $request = Request::init();
        try {
            $suiteResponse = $request
                ->uri("{$this->baseUrl}/get_suites/{$correctProject->id}")
                ->contentType('application/json')
                ->basicAuth($this->username, $this->apiKey)
                ->method("GET")
                ->send();


        } catch (ConnectionErrorException $e) {
            throw new TestRailRunCreateException('Cannot find test suite via api');
        }

        $decodedResponse = (json_decode($suiteResponse));

        $latestSuite = end($decodedResponse);


        printf("Suites found successfully. Using latest suite, a.k.a " . $latestSuite->name . "...\n");

        $this->createTestRun($correctProject, $latestSuite);

    }

    /**
     * @param $correctProject
     * @param $latestSuite
     * @throws TestRailRunCreateException
     */
    public function createTestRun($correctProject, $latestSuite){


        $newTestRun = null;
        $newRunArray = ['suite_id' => $latestSuite->id, 'name' => $latestSuite->name . ' Full Functionality', 'include_all' => true];

        if($this->milestone != null){

            $newRunArray['milestone_id'] = $this->milestone;
        }


        $request = Request::init();
        try {
            $newTestRun = $request
                ->uri("{$this->baseUrl}/add_run/{$correctProject->id}")
                ->contentType('application/json')
                ->basicAuth($this->username, $this->apiKey)
                ->body($newRunArray)
                ->method("POST")
                ->send();


        } catch (ConnectionErrorException $e) {
            throw new TestRailRunCreateException('Cannot create test run via api');
        }



        $decodedResponse = (json_decode($newTestRun));

         printf("Test run successfully created with id " . $decodedResponse->id . "...\n");

        $this->writeUpdatedYaml($decodedResponse);



    }


    /**
     * @param $decodedTestRun
     */
    public function writeUpdatedYaml($decodedTestRun){

        $this->yaml['default']['extensions']['flexperto\BehatTestrailReporter\TestrailReporterExtension']['runId'] = $decodedTestRun->id;


        file_put_contents(getcwd().'/behat.yml', Yaml::dump($this->yaml, 9));


        printf("Updating behat.yml with test run id " . $decodedTestRun->id . "...\n");
        printf("Please run behat to update your new test run...\n");

    }

}

class TestRailRunCreateException extends Exception
{


}