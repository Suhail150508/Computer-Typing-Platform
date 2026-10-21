<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** পাসওয়ার্ড রিসেটের ইমেইল (বাংলায়) */
class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = (int) config('auth.passwords.users.expire', 60);
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
        $app = config('app.name', 'TypingMoja');

        return (new MailMessage)
            ->subject('পাসওয়ার্ড রিসেট করুন — '.$app)
            ->greeting('আসসালামু আলাইকুম, '.($notifiable->name ?: 'বন্ধু').'!')
            ->line('আপনার '.$app.' একাউন্টের পাসওয়ার্ড রিসেটের অনুরোধ পেয়েছি।')
            ->action('নতুন পাসওয়ার্ড দিন', $url)
            ->line('এই লিংকটি '.$minutes.' মিনিটের মধ্যে ব্যবহার করতে হবে এবং শুধু একবারই কাজ করবে।')
            ->line('আপনি যদি অনুরোধ না করে থাকেন, তাহলে এই ইমেইল উপেক্ষা করুন — আপনার পাসওয়ার্ড বদলাবে না।')
            ->salutation('ধন্যবাদ, '.$app.' টিম');
    }
}
