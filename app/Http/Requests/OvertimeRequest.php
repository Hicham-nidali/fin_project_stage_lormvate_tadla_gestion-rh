<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class OvertimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'employee';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:overtime',
            'overtime_date' => [
                'required',
                'date',
                'after_or_equal:' . Carbon::now()->subDays(7)->toDateString(), // Max 7 jours en arrière
                'before_or_equal:' . Carbon::now()->addDays(30)->toDateString(), // Max 30 jours en avance
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'hours_requested' => 'required|numeric|min:0.5|max:12',
            'overtime_reason' => 'required|string|max:500',
            'overtime_type' => 'required|in:planned,urgent,project',
            'overtime_rate' => 'required|numeric|in:1.25,1.5,2',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la demande est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            
            'description.required' => 'La description est obligatoire.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            
            'overtime_date.required' => 'La date des heures supplémentaires est obligatoire.',
            'overtime_date.date' => 'La date doit être une date valide.',
            'overtime_date.after_or_equal' => 'La date ne peut pas être antérieure à 7 jours.',
            'overtime_date.before_or_equal' => 'La date ne peut pas être supérieure à 30 jours.',
            
            'start_time.required' => 'L\'heure de début est obligatoire.',
            'start_time.date_format' => 'L\'heure de début doit être au format HH:MM.',
            
            'end_time.required' => 'L\'heure de fin est obligatoire.',
            'end_time.date_format' => 'L\'heure de fin doit être au format HH:MM.',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            
            'hours_requested.required' => 'Le nombre d\'heures est obligatoire.',
            'hours_requested.numeric' => 'Le nombre d\'heures doit être un nombre.',
            'hours_requested.min' => 'Le minimum est de 0.5 heures.',
            'hours_requested.max' => 'Le maximum est de 12 heures par jour.',
            
            'overtime_reason.required' => 'La raison des heures supplémentaires est obligatoire.',
            'overtime_reason.max' => 'La raison ne peut pas dépasser 500 caractères.',
            
            'overtime_type.required' => 'Le type d\'heures supplémentaires est obligatoire.',
            'overtime_type.in' => 'Le type d\'heures supplémentaires n\'est pas valide.',
            
            'overtime_rate.required' => 'Le taux de majoration est obligatoire.',
            'overtime_rate.in' => 'Le taux de majoration doit être 125%, 150% ou 200%.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Calculer automatiquement les heures si elles ne sont pas fournies
        if ($this->has('start_time') && $this->has('end_time') && !$this->has('hours_requested')) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            
            if ($end > $start) {
                $hours = $end->diffInHours($start, true);
                $this->merge(['hours_requested' => round($hours, 1)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validation personnalisée : vérifier que l'employé n'a pas déjà une demande en attente pour la même date
            if ($this->has('overtime_date')) {
                $existingRequest = \App\Models\OvertimeRecord::where('user_id', auth()->id())
                    ->where('overtime_date', $this->overtime_date)
                    ->where('status', 'pending')
                    ->exists();
                
                if ($existingRequest) {
                    $validator->errors()->add('overtime_date', 'Vous avez déjà une demande d\'heures supplémentaires en attente pour cette date.');
                }
            }
            
            // Validation personnalisée : vérifier les heures de travail raisonnables
            if ($this->has('start_time') && $this->has('end_time')) {
                $start = Carbon::parse($this->start_time);
                $end = Carbon::parse($this->end_time);
                
                // Les heures supplémentaires doivent généralement être après 17h ou avant 8h
                if ($start->hour >= 8 && $start->hour < 17) {
                    $validator->errors()->add('start_time', 'Les heures supplémentaires sont généralement après 17h ou avant 8h.');
                }
                
                // Vérifier que la durée n'est pas excessive
                $hours = $end->diffInHours($start, true);
                if ($hours > 12) {
                    $validator->errors()->add('end_time', 'La durée ne peut pas dépasser 12 heures consécutives.');
                }
            }
        });
    }
}