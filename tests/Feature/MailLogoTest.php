<?php

use App\Models\Exercice;
use App\Models\User;
use App\Notifications\OuvertureSaisieActivitesNotification;
use App\Support\MailLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\Part\DataPart;

uses(RefreshDatabase::class);

test('le logo CANAM est intégré une seule fois dans les mails, en pièce jointe en ligne', function () {
    config(['mail.default' => 'array']);
    seedRolesAndPermissions();

    User::factory()->create()->notify(new OuvertureSaisieActivitesNotification(Exercice::factory()->create()));

    $messages = app('mailer')->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(1);

    $email = $messages->first()->getOriginalMessage();
    $logos = collect($email->getAttachments())
        ->filter(fn ($piece) => $piece instanceof DataPart && $piece->getFilename() === MailLogo::FICHIER);

    expect($logos)->toHaveCount(1);
    expect($email->getHtmlBody())->toContain('src="cid:'.$logos->first()->getContentId().'"');
    expect($email->getHtmlBody())->not->toContain('logo_canam.png"');
});
