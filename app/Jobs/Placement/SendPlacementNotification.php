<?php

namespace App\Jobs\Placement;

use App\Models\placement\PlacementNotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use Twilio\Rest\Client as TwilioClient;

/**
 * Delivers one placement_notification_log row over email (PHPMailer, the institute's smtp_details)
 * or WhatsApp (Twilio, the institute's whatapp_user_details), then records the outcome.
 * A failure is stored on the row (status=failed) and can be resent from the log screen.
 */
class SendPlacementNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    protected int $logId;

    public function __construct(int $logId)
    {
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = PlacementNotificationLog::withoutGlobalScopes()->find($this->logId);
        if (! $log || ! in_array($log->status, ['queued', 'failed'], true)) {
            return;
        }

        try {
            $student = DB::table('tblstudent')
                ->where('id', $log->student_id)->where('sub_institute_id', $log->sub_institute_id)->first();
            if (! $student) {
                throw new \RuntimeException('Student not found');
            }

            $log->channel === 'email' ? $this->sendEmail($log, $student) : $this->sendWhatsapp($log, $student);

            $log->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }

    protected function sendEmail(PlacementNotificationLog $log, $student): void
    {
        if (empty($student->email) || ! filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Student has no valid email address');
        }
        $smtp = DB::table('smtp_details')->where('sub_institute_id', $log->sub_institute_id)->first();
        if (! $smtp) {
            throw new \RuntimeException('Mail client is not set up for this institute');
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->isHTML(true);
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = (int) $smtp->port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Host = $smtp->server_address;
            $mail->Port = (int) $smtp->port;
            $mail->Username = $smtp->gmail;
            $mail->Password = $smtp->password;
            $mail->setFrom($smtp->gmail, $smtp->gmail);
            $mail->addAddress($student->email);
            $mail->Subject = $log->subject ?: 'Placement update';
            $mail->Body = nl2br(e($log->body));
            $mail->AltBody = $log->body;
            $mail->send();
        } catch (MailException $e) {
            throw new \RuntimeException($mail->ErrorInfo ?: $e->getMessage());
        }
    }

    protected function sendWhatsapp(PlacementNotificationLog $log, $student): void
    {
        $mobile = (string) $student->mobile;
        if (strlen($mobile) !== 10 || ! ctype_digit($mobile)) {
            throw new \RuntimeException('Student has no valid 10-digit mobile number');
        }
        $account = DB::table('whatapp_user_details')->where('sub_institute_id', $log->sub_institute_id)->first();
        if (! $account) {
            throw new \RuntimeException('WhatsApp is not set up for this institute');
        }

        $client = new TwilioClient($account->user_whatsapp_sid, $account->user_whatsapp_token);
        // Business-initiated WhatsApp needs an approved template; the ERP's text template takes the message as variable 1.
        $response = $client->messages->create('whatsapp:+91'.$mobile, [
            'contentSid'          => config('placement.whatsapp.content_sid'),
            'messagingServiceSid' => config('placement.whatsapp.messaging_service_sid'),
            'from'                => 'whatsapp:+91'.$account->user_whatsapp_no,
            'contentVariables'    => json_encode(['1' => $log->body]),
        ]);

        if ($response->errorCode) {
            throw new \RuntimeException($response->errorMessage ?: 'WhatsApp error '.$response->errorCode);
        }
    }
}
