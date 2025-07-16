<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use MailerSend\MailerSend;
use Illuminate\Http\Request;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\ActivityParams;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;

class MailerSendControllers extends Controller
{
    //
    public function activity()
    {
        // Get the current timestamp
        $nowTimestamp = now()->timestamp;

        // Add 6 days to the current timestamp
        $timestampPlusSixDays = Carbon::createFromTimestamp($nowTimestamp)->subDays(6)->timestamp;

        $mailersend = new MailerSend(['api_key' => env('MAILERSEND_API_KEY')]);

        // $activityParams = (new ActivityParams())
        //     ->setPage(1)
        //     ->setLimit(15)
        //     ->setDateFrom($timestampPlusSixDays)
        //     ->setDateTo($nowTimestamp)
        //     ->setEvent(['queued', 'sent']);

        // $mailersend->activity->getAll('7dnvo4d399xg5r86', $activityParams);

        $activityAnalyticsParams = (new ActivityAnalyticsParams($timestampPlusSixDays, $nowTimestamp))
            ->setDomainId('7dnvo4d399xg5r86')
            ->setGroupBy(Constants::GROUP_BY_DAYS)
            ->setTags(['tag'])
            ->setEvent(['sent', 'delivered']);

        $analyticsData = $mailersend->analytics->activityDataByDate($activityAnalyticsParams);

        return response()->json("MyTest");
    }
}
