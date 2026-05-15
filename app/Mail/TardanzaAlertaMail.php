<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TardanzaAlertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $docente;
    public int $cantidadTardanzas;
    public string $mes;

    public function __construct(User $docente, int $cantidadTardanzas, string $mes)
    {
        $this->docente = $docente;
        $this->cantidadTardanzas = $cantidadTardanzas;
        $this->mes = $mes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ ALERTA ACADÉMICA: Reincidencia de Tardanzas - ' . $this->docente->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tardanza_alerta',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
