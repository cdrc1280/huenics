<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;

class CustomerDailyInquiryService
{
    public function hasSentQuotationToday(Request $inquiryRequest): bool
    {
        $todayStamp = date('Y-m-d');
        $utcTodayStamp = now()->format('Y-m-d');
        $validDates = array_unique([$todayStamp, $utcTodayStamp]);

        $sessionTimestamp = $inquiryRequest->session()->get('quotation_sent_date');
        if ($sessionTimestamp && in_array($sessionTimestamp, $validDates, true)) {
            return true;
        }

        $cookieTimestamp = $inquiryRequest->cookie('quotation_sent_date');
        if ($cookieTimestamp && in_array($cookieTimestamp, $validDates, true)) {
            return true;
        }

        $clientIp = $inquiryRequest->ip() ?: '127.0.0.1';
        $ipVariants = array_unique(array_filter([
            $clientIp,
            $clientIp === '::1' ? '127.0.0.1' : ($clientIp === '127.0.0.1' ? '::1' : null),
            $inquiryRequest->server('REMOTE_ADDR'),
            $inquiryRequest->header('X-Forwarded-For') ? trim(explode(',', $inquiryRequest->header('X-Forwarded-For'))[0]) : null,
        ]));

        foreach ($ipVariants as $resolvedIp) {
            foreach ($validDates as $targetDate) {
                $cacheKey = 'quotation_daily_ip_'.str_replace([':', '.'], '_', $resolvedIp).'_'.$targetDate;
                if (Cache::has($cacheKey)) {
                    return true;
                }
            }
        }

        try {
            return Quotation::query()
                ->where(function ($query) use ($ipVariants) {
                    if (Schema::hasColumn('quotations', 'client_ip')) {
                        $query->whereIn('client_ip', $ipVariants);
                    }
                    foreach ($ipVariants as $resolvedIp) {
                        $query->orWhere('notes', 'like', "%Client IP: {$resolvedIp}%");
                    }
                })
                ->where(function ($query) use ($validDates) {
                    $query->where(function ($subQuery) use ($validDates) {
                        foreach ($validDates as $targetDate) {
                            $subQuery->orWhereDate('quotation_date', $targetDate)
                                ->orWhereDate('created_at', $targetDate);
                        }
                    });
                })
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function recordQuotationSent(Request $inquiryRequest, string $clientIp): void
    {
        $todayStamp = date('Y-m-d');
        $secondsRemainingToday = max(60, now()->diffInSeconds(now()->endOfDay()));

        $ipVariants = array_unique(array_filter([
            $clientIp,
            $clientIp === '::1' ? '127.0.0.1' : ($clientIp === '127.0.0.1' ? '::1' : null),
            $inquiryRequest->server('REMOTE_ADDR'),
            $inquiryRequest->header('X-Forwarded-For') ? trim(explode(',', $inquiryRequest->header('X-Forwarded-For'))[0]) : null,
        ]));

        foreach ($ipVariants as $resolvedIp) {
            $cacheKey = 'quotation_daily_ip_'.str_replace([':', '.'], '_', $resolvedIp).'_'.$todayStamp;
            Cache::put($cacheKey, true, $secondsRemainingToday);
        }

        $inquiryRequest->session()->put('quotation_sent_date', $todayStamp);
        $inquiryRequest->session()->put('quotation_client_ip', $clientIp);
        $inquiryRequest->session()->save();

        $cookieMinutes = (int) ceil($secondsRemainingToday / 60);
        Cookie::queue('quotation_sent_date', $todayStamp, $cookieMinutes);
        Cookie::queue('quotation_client_ip', $clientIp, $cookieMinutes);
    }
}
