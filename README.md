# Podcast App

## Opis projekta
Podcast App je web aplikacija koja omogućava kreiranje, objavljivanje i slušanje podcast epizoda.  
Projekat se sastoji iz dva dela: **backend** razvijen u Laravel-u i **frontend** razvijen u React-u.  
Korisnici mogu da kreiraju naloge, postavljaju podkaste, slušaju epizode i pretražuju sadržaj.

## Funkcionalnosti
- Registracija i prijava korisnika  
- Upravljanje korisničkim nalozima  
- Kreiranje, izmena i brisanje podcast-a  
- Upload i reprodukcija audio epizoda  
- Pretraga podkasta i epizoda  
- Podela korisnika na uloge: admin, kreator, običan korisnik  

## Struktura repozitorijuma

/backend -> Laravel backend (API)
/frontend -> React frontend
README.md -> Ovaj fajl


## Instalacija i pokretanje projekta

### 1. Kloniranje repozitorijuma
```bash
git clone https://github.com/elab-development/internet-tehnologije-2024-projekat-appzapodkast_2019_0164.git
cd internet-tehnologije-2024-projekat-appzapodkast_2019_0164

2. Backend (Laravel)

cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

    Backend je dostupan na http://127.0.0.1:8000

3. Frontend (React)

cd ../frontend
npm install
npm start

    Frontend je dostupan na http://localhost:3000

4. Povezivanje frontend-a i backend-a

    Frontend koristi API endpoint-e backend-a (http://127.0.0.1:8000/api)

    Axios je konfigurisan da šalje token u Authorization header-u za autentifikaciju

Napomene

    Uverite se da je MySQL server pokrenut i da su kreirane baze podataka definisane u .env fajlu backend-a

    Preporučuje se korišćenje Node.js 18+ verzije za frontend razvoj

Autor

    Monika Simona

    GitHub: monika-simona
