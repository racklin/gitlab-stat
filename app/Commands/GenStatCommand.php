<?php

namespace App\Commands;

use App\Services\Gitlab;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Gitlab\Client;
use Gitlab\ResultPager;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class GenStatCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'genstat {date?} {--config=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Gitlab $gitlab)
    {

        $date = $this->argument('date');
        if ($date) {
            $today = Carbon::parse($date, config('gitlabstat.timezone'))->toImmutable();
        }else {
            $today = Carbon::today(config('gitlabstat.timezone'))->toImmutable();
        }
        $yesterday = $today->addDays(-1);
        $prevDay =  $today->addDays(-2);

        $this->info('Gen Gitlab Stat [' . $yesterday->toDateString() . ']');


        // initial gitlab service
        $gitlab->setConfig(config('gitlabstat'));
       // $gRepositories = $client->repositories(); // repositories object

        // all commits array
        $allCommits = [];

        $allUsers = $gitlab->getAllUser();

        $bar = $this->output->createProgressBar(count($allUsers));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');


        foreach ($allUsers as $curUsers) {

            $bar->setMessage(' [ user_id = ' . $curUsers['id'] . ']');
            // $this->info('        processing [' . $curUsers['id'] . ']');

            $events = $gitlab->getUserEvents($curUsers['id'], [
                'after' => $prevDay,
                'before' => $today
            ]);

            foreach ($events as $event) {

                if (empty($event['push_data'])) continue;
                if (empty($event['push_data']['commit_to'])) continue;

                $commitDetail = $gitlab->getCommitDetail($event['project_id'], $event['push_data']['commit_to']);

                // push to result array
                array_push($allCommits, ['event'=>$event, 'commit'=>$commitDetail]);
            }
            $bar->advance();

        }

        $bar->finish();

        $this->line('');
        $this->info('Total = [' . count($allCommits) . ']');
        $this->info('Save to [result_' . $yesterday->toDateString() . '.json]');
        $this->info('Save to [result_' . $yesterday->toDateString() . '.sql]');

        $commits = $gitlab->transformToData($allCommits);
        $commitsArray = $commits->toArray()['data'];

        // writing json
        file_put_contents('result_' . $yesterday->toDateString() . '.json', json_encode($commitsArray));

    }

    /**
     * Define the command's schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
