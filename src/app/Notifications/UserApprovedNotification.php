<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public string $resetUrl) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Atelier Kamu Disetujui!')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Selamat! Akun kamu di aplikasi Atelier telah disetujui oleh admin.')
            ->line('Klik tombol di bawah untuk membuat password dan mulai menggunakan akun kamu.')
            ->action('Buat Password Sekarang', $this->resetUrl)
            ->line('Link ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika kamu tidak mendaftar di Atelier, abaikan email ini.')
            ->salutation('Salam, Tim Atelier');
    }
}
