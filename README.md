# github-lab
# Lista elementów — PHP + JS + HTML + CSS

## Zespół
- Damian — Backend
- Mateusz — Frontend
- Paweł — Koordynator

## Uruchomienie
W katalogu repo:
php -S localhost:8000 -t .

Aplikacja:
http://localhost:8000/frontend/index.html

API:
http://localhost:8000/backend/api/items.php

## Funkcje
- GET lista elementów
- POST dodanie elementu (title min 3 znaki)
- Dane w backend/storage/data.json

## Jak pracowaliśmy
- Branch na osobę/funkcję
- Pull Request + review przed merge
3.	Commit na main.
________________________________________
## Testy ręczne (checklista)
1. GET: strona ładuje listę bez błędów.
2. POST: dodanie elementu (min 3 znaki) → pojawia się na liście.
3. POST invalid: tytuł < 3 → komunikat błędu.
4. PUT title: edycja tytułu → zapis i odświeżenie listy.
5. PUT done: checkbox zmienia status → filtr działa.
6. DELETE: usunięcie elementu → znika z listy.
7. Search: wpisanie frazy filtruje listę.
8. Filtry: aktywne/zrobione/wszystkie działają.

**
