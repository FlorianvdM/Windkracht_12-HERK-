# Kitesurfschool Windkracht-12 – reserveringssysteem (prototype)

Prototype voor het examen Verdieping Software (K0505), herkansing.
Gemaakt door Florian van der Meulen.

Webapplicatie waarin klanten zelf kitesurflessen reserveren, instructeurs hun lessen zien en annuleren,
en de eigenaar reserveringen, annuleringen, materiaal en het inlog-logboek beheert.

**Techniek:** Laravel (PHP) + React via Inertia + MySQL.

## Wat heb je nodig?

- PHP 8.3 of hoger
- Composer
- Node.js en npm
- MySQL, met een lege database `windkracht_12`

## Installeren (PowerShell)

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Controleer in `.env` de databasegegevens (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Daarna:

```powershell
php artisan migrate --seed
composer run dev
```

Open daarna de website op **http://localhost:8012**.
De poort staat in `.env` bij `SERVER_PORT` en `APP_URL`.

## Testaccounts

Alle accounts hebben hetzelfde wachtwoord: `Windkracht-12!Test`

| Rol | E-mailadres |
|---|---|
| Eigenaar | terence@kitesurfschool-windkracht12.nl |
| Instructeur | duco@kitesurfschool-windkracht12.nl |
| Klant | lara.visser@email.nl |

Er zijn meer instructeurs (waldemar, ruud, saskia, bernie @kitesurfschool-windkracht12.nl)
en klanten (eva.mol@email.nl, noah.degroot@email.nl).

## Wat zit er in het prototype?

- Registreren en inloggen met de wachtwoordregels uit de casus
- Drie rollen met beveiligde pagina's
- Reserveren in stappen: pakket, locatie, lesdagen, duo-lesser, controle
- Reserveringsstatus voorlopig → definitief, betaling melden, overzicht van openstaande betalingen
- Dag-, week- en maandoverzicht voor instructeurs
- Lessen annuleren: klant met reden (eigenaar keurt goed), instructeur/eigenaar bij ziekte of slecht weer
- Logboek van in- en uitloggen (microseconde nauwkeurig)
- Materiaalbeheer (extra, niet in de casus)

## Waar vind je het bewijs?

- **E-mails:** worden niet echt verstuurd (`MAIL_MAILER=log`). Ze staan in `storage/logs/laravel.log`.
- **In- en uitloggen:** staat in `storage/logs/inlog.log`. De eigenaar ziet het ook op de pagina Logboek.
- **Automatische tests:** draai `php artisan test`.

## Bewust niet gebouwd (Won't have)

- Echte e-mails en activatiemail
- BSN-gegevens van instructeurs
- Hosting en domein