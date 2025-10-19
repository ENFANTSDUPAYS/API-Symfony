<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Cron\CronExpression;

final class NewsletterScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        $cron = new CronExpression('30 8 * * 1');

        $schedule->add(
            RecurringMessage::every(
                new CronExpressionTrigger($cron),
                new RunCommandMessage('SendNewsletterCommand')
            )
        );

        return $schedule;
    }
}
