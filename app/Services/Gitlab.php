<?php


namespace App\Services;

use Carbon\Carbon;
use Gitlab\Client;
use Gitlab\ResultPager;
use Http\Client\Exception;
use League\Fractal;

class Gitlab
{

    private $config;

    private $client;

    private $users;

    private $repositories;

    private $pager;

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config): void
    {
        $this->config = $config;

        // setting authentication and token
        if(!empty(config('gitlab_url'))) $this->client->setUrl(config('gitlab_url'));
        $this->client->authenticate(config('gitlab_token') ?? 'zMs573iVHRz7gnb5LnKk', Client::AUTH_HTTP_TOKEN);
    }

    public function __construct()
    {
        $this->client = new Client();

        $this->users = $this->client->users();
        $this->repositories = $this->client->repositories();
        $this->pager = new ResultPager($this->client);
    }



    public function getAllUser($parameters = ['active' => true, 'external' => false])
    {
        try {
            return $this->pager->fetchAll($this->users, 'all', [$parameters, null]);
        } catch (Exception $e) {
        }
    }

    public function getUserEvents($userId, $parameters = [])
    {
        return $this->users->events($userId, $parameters);
    }

    public function getCommitDetail($projectId, $sha = '')
    {
        return $this->repositories->commit($projectId, $sha);
    }


    public function transformToData($events = []) {

        $config = $this->getConfig();

        // XXX @todo using anonymous class, refactoring future.
        $resource = new Fractal\Resource\Collection($events, function($event) use ($config) {
            return [
                'project_id' => $event['event']['project_id'],
                'created_at' => Carbon::parse($event['event']['created_at'], 'UTC')->setTimezone($config['timezone'] ?? 'UTC')->toDateTimeString(),
                'commit_to' => $event['event']['push_data']['commit_to'],
                'user_id' => $event['event']['author']['id'],
                'username' => $event['event']['author']['username'],
                'display_name' => $event['event']['author']['name'],
                // commit detail
                'commit_title' => '', // $event['commit']['title'],
               'email' => $event['commit']['author_email'],
               'commit_total' => $event['commit']['stats']['total'],
               'commit_additions' => $event['commit']['stats']['additions'],
               'commit_deletions' => $event['commit']['stats']['deletions'],
            ];
        });

        $manager = new Fractal\Manager();
        $manager->setSerializer(new Fractal\Serializer\ArraySerializer());
        return $manager->createData($resource);

    }
}
