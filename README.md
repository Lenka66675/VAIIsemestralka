Najskôr je potrebné mať nainštalované XAMPP (alebo MySQL a PHP samostatne), Composer (PHP Dependency Manager), Node.js (pre npm a frontendové závislosti) a Git (pre klonovanie projektu). Projekt stiahneš z GitHubu príkazom git clone a následne prejdeš do priečinka s projektom cd VAII. Keďže Laravel používa Composer, spustíš composer install na nainštalovanie potrebných balíčkov.
V koreňovom adresári aplikácie je potrebné nastaviť .env súbor. Ak neexistuje, vytvoríš ho príkazom cp .env.example .env. Potom v ňom nastavíš pripojenie na databázu nasledovne:
DB_CONNECTION=.. 
DB_HOST=..
DB_PORT=..
DB_DATABASE= ..
DB_USERNAME= ..
DB_PASSWORD=..

DB_USERNAME = root a heslo ostáva prázdne.
Uisti sa, že MySQL server beží v XAMPP a následne spusti migrácie na vytvorenie databázových tabuliek príkazom php artisan migrate. Ak chceš naplniť databázu demo dátami, spustíš php artisan db:seed. Aby aplikácia správne fungovala, je potrebné vygenerovať application key pomocou php artisan key:generate.
Po úspešnom nastavení môžeš aplikáciu spustiť príkazom php artisan serve. Aplikácia bude dostupná na http://127.0.0.1:8000. Ak je v aplikácii frontend, je potrebné nainštalovať a spustiť frontendové balíčky pomocou npm install && npm run dev.
Aplikácia obsahuje autentifikáciu,  Admin účet – Email: admin@example.com, Heslo: password.

Ak sa vyskytne chyba, skontroluj, či beží MySQL v XAMPP, skontroluj .env a správne nastavenie databázy, prípadne spusti php artisan migrate:refresh, ak sú problémy s tabuľkami. Ak Laravel hodí chybu, vyčistíš cache príkazmi:
php artisan config:clear
php artisan cache:clear
php artisan view:clear
Ak je problém s npm, pomôže npm install && npm run dev.
