# 🚗 NavioX – Radarová mapa s navigací

## 📌 Popis projektu

NavioX je webová aplikace zaměřená na sledování dopravních informací.

### 🟡 Původní verze

Projekt byl původně vytvořen jako:

* 📍 interaktivní mapa radarů
* 📷 zobrazení kamer
* 🗺️ filtrování podle typu objektů
* ⚠️ přehled dopravních událostí

---

### 🔥 Rozšíření mimo školní projekt

Aplikace rozšířena o:

* 🧭 navigaci v reálném čase
* 🚗 výpočet trasy
* 🔊 hlasové pokyny v češtině
* ⚠️ upozornění na radary během jízdy
* 📊 zobrazení rychlosti a ETA

---

## 🛠️ Použité technologie

* PHP (backend)
* JavaScript
* Leaflet.js (mapy)
* Leaflet Routing Machine (navigace)
* Mapy.cz API
* Docker / Docker Compose

---

## 🚀 Spuštění projektu

### 1️⃣ Klonování

```bash id="z6y98s"
git clone https://github.com/USERNAME/naviox.git
cd naviox
```

---

### 2️⃣ Docker Compose

```bash id="2zcv0f"
docker-compose up -d
```

---

### 3️⃣ Otevření aplikace

```id="ztxqgi"
http://localhost:8080
```

---

## 📱 Použití

### Radar mapa

* Zobrazení radarů a kamer
* Kliknutí na objekt → detail

### Navigace

1. Otevři `https://naviox.eu/radar/public/navigation.php`
2. Povol GPS
3. Klikni na mapu → nastavíš cíl
4. Navigace se spustí

---

## 🔊 Funkce navigace

* Hlasové pokyny:

  * „Odboč doprava za 500 metrů“
* HUD (zobrazení instrukcí)
* Ikony směru
* ETA (čas dojezdu)
* Rychlost (km/h)

---

## ⚠️ Radar systém

* Načítání dat z externího API
* Upozornění při přiblížení
* Vibrace + hlas

---

## 🐳 Docker

Projekt využívá:

* Web server (PHP)
* Databázi (dle zadání)

Docker Compose slouží pro:

* spuštění více služeb
* simulaci produkčního prostředí

---

## 📂 Struktura projektu

```id="2b4hmh"
/public
  ├── index.php        # radar mapa
  ├── [navigation.php  ] (https://naviox.eu/radar/public/) # navigace
Dockerfile
docker-compose.yml
README.md
```

---

## ⚠️ Poznámky

* Data jsou načítána z externího API
* Pokročilé funkce (např. jízdní pruhy) nejsou implementovány

---

## 🎯 Cíl projektu

Cílem bylo:

* vytvořit radarovou mapu
* rozšířit ji o navigaci mimo projekt

---

## 👤 Autor

Denis Ben-Chenni

---

## 📄 Licence

Projekt je určen pro školní účely.
