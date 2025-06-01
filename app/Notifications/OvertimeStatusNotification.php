<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\OvertimeRecord;

class OvertimeStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $overtimeRecord;
    protected $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(OvertimeRecord $overtimeRecord, string $action)
    {
        $this->overtimeRecord = $overtimeRecord;
        $this->action = $action; // 'submitted', 'approved', 'rejected'
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mailMessage = new MailMessage();
        
        switch ($this->action) {
            case 'submitted':
                return $mailMessage
                    ->subject('Nouvelle demande d\'heures supplémentaires')
                    ->greeting('Bonjour ' . $notifiable->name)
                    ->line('Une nouvelle demande d\'heures supplémentaires a été soumise par ' . $this->overtimeRecord->user->name . '.')
                    ->line('**Détails de la demande :**')
                    ->line('• Date : ' . $this->overtimeRecord->overtime_date->format('d/m/Y'))
                    ->line('• Horaires : ' . \Carbon\Carbon::parse($this->overtimeRecord->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($this->overtimeRecord->end_time)->format('H:i'))
                    ->line('• Heures demandées : ' . $this->overtimeRecord->hours_requested . 'h')
                    ->line('• Raison : ' . $this->overtimeRecord->reason)
                    ->action('Voir la demande', route('overtime.show', $this->overtimeRecord->id))
                    ->line('Merci de traiter cette demande dans les meilleurs délais.');

            case 'approved':
                return $mailMessage
                    ->subject('Heures supplémentaires approuvées')
                    ->greeting('Bonjour ' . $notifiable->name)
                    ->line('Votre demande d\'heures supplémentaires a été **approuvée** par ' . $this->overtimeRecord->approver->name . '.')
                    ->line('**Détails approuvés :**')
                    ->line('• Date : ' . $this->overtimeRecord->overtime_date->format('d/m/Y'))
                    ->line('• Heures approuvées : ' . $this->overtimeRecord->hours_approved . 'h')
                    ->line('• Taux de majoration : ' . $this->getOvertimeRate() . '%')
                    ->action('Voir les détails', route('employee.requests.show', $this->overtimeRecord->request_id))
                    ->line('Ces heures seront prises en compte dans votre prochaine paie.')
                    ->salutation('Félicitations !');

            case 'rejected':
                return $mailMessage
                    ->subject('Demande d\'heures supplémentaires rejetée')
                    ->greeting('Bonjour ' . $notifiable->name)
                    ->line('Nous regrettons de vous informer que votre demande d\'heures supplémentaires a été rejetée.')
                    ->line('**Détails de la demande :**')
                    ->line('• Date : ' . $this->overtimeRecord->overtime_date->format('d/m/Y'))
                    ->line('• Heures demandées : ' . $this->overtimeRecord->hours_requested . 'h')
                    ->line('• Traitée par : ' . $this->overtimeRecord->approver->name)
                    ->action('Voir les détails', route('employee.requests.show', $this->overtimeRecord->request_id))
                    ->line('N\'hésitez pas à contacter votre responsable pour plus d\'informations.');

            default:
                return $mailMessage->line('Mise à jour sur votre demande d\'heures supplémentaires.');
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'overtime_record_id' => $this->overtimeRecord->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'overtime_date' => $this->overtimeRecord->overtime_date->format('d/m/Y'),
            'hours' => $this->action === 'approved' ? $this->overtimeRecord->hours_approved : $this->overtimeRecord->hours_requested,
            'employee_name' => $this->overtimeRecord->user->name,
            'approver_name' => $this->overtimeRecord->approver?->name,
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the message for the notification.
     */
    private function getMessage(): string
    {
        switch ($this->action) {
            case 'submitted':
                return 'Nouvelle demande d\'heures supplémentaires de ' . $this->overtimeRecord->user->name . ' pour le ' . $this->overtimeRecord->overtime_date->format('d/m/Y');
            
            case 'approved':
                return 'Vos heures supplémentaires du ' . $this->overtimeRecord->overtime_date->format('d/m/Y') . ' ont été approuvées (' . $this->overtimeRecord->hours_approved . 'h)';
            
            case 'rejected':
                return 'Votre demande d\'heures supplémentaires du ' . $this->overtimeRecord->overtime_date->format('d/m/Y') . ' a été rejetée';
            
            default:
                return 'Mise à jour sur votre demande d\'heures supplémentaires';
        }
    }

    /**
     * Get the overtime rate percentage.
     */
    private function getOvertimeRate(): int
    {
        $metadata = json_decode($this->overtimeRecord->request->description, true);
        $rate = is_array($metadata) ? ($metadata['overtime_rate'] ?? 1.25) : 1.25;
        return (int)($rate * 100);
    }
}