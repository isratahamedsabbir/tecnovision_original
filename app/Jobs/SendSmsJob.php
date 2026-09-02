<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http; 

class SendSmsJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The SMS message to send.
     *
     * @var string
     */
    protected $sms;
    protected $number;

    /**
     * Create a new job instance.
     */
    public function __construct($sms, $number)
    {
        $this->sms    = $sms;
        $this->number = $number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = 'https://sms-service.xylub.com/?api=send-single-message';

        $txt = str_replace(["\r\n", "\n"], "\n", $this->sms);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($url, [
            'api_key' => "c4addabeaa62e56cea0eac102e87db73",
            'mobile'  => $this->number,
            'text'    => $txt,
        ]);

        logger()->info('SMS Response:', $response->json());
    }

}
