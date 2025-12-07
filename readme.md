# Alkon hinnasto – Web-palvelu  

**Web- ja peliteknologian perusteet -kurssin lopputyö (arvosana 5)**  
Moderni, responsiivinen ja automaattisesti päivittyvä web-sovellus Alkon koko tuotetietokannan selaamiseen.

![Kuvakaappaus](https://img.shields.io/badge/Arvosana-5-brightgreen) ![PHP](https://img.shields.io/badge/PHP-8.4%2B-blue) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-purple)

## Esikatselu (näyttökuvat)
*(Lisää myöhemmin kansioon `/screenshots/` ja korvaa linkit)*
- Pääsivu: responsiivinen taulukko + suodattimet
- Päivityssivu: reaaliaikainen edistymispalkki

## Toteutetut ominaisuudet (kaikki arvosanan 5 kriteerit)

| Ominaisuus                            | Toteutus                                                                 |
|---------------------------------------|--------------------------------------------------------------------------|
| Katalogin selaus sivutettuna          | 25 tuotetta/sivu, selkeä sivutusnumerointi                               |
| CSV-pohjainen tietokanta              | Nopea haku ja suodatus, tiedot tallennetaan `data/alkon-hinnasto-combined.csv` |
| Automaattinen hinnaston päivitys      | Lataa suoraan Alkon viralliselta sivulta, reaaliaikainen edistymispalkki |
| Kaikki suodattimet                    | Tyyppi, maa, pullokoko, hinta- ja energiaväli                            |
| Visuaalinen & responsiivinen UI       | Bootstrap 5.3.3                                                          |
| Siisti ja modulaarinen koodi          | MVC-arkkitehtuuri, kommentoitu, helposti laajennettavissa                |

## Teknologiat

- **PHP 8.4+**
- **Bootstrap 5.3.3** + Bootstrap Icons
- **SimpleXLSX** (Composer: `shuchkin/simplexlsx`)
- **CSV** tietovarastona (nopeampi kuin tietokanta pienessä projektissa)

## Kansiorakenne

```plaintext
Web_ja_peliteknologian_perusteet/
├── data/
│   ├── alkon-hinnasto.xlsx              # Alkon viralliselta sivulta ladattu Excel
│   ├── alkon-hinnasto-ascii.csv         # Puhdas tuotedata ilman otsikoita
│   └── alkon-hinnasto-combined.csv      # Header + data (sovelluksen käyttämä tiedosto)
├── vendor/                              # Composer-riippuvuudet
│   └── shuchkin/simplexlsx/
├── config.php                           # Kaikki asetukset (polut, sarakkeet, sivukoko)
├── model.php                            # Tietojen luku, suodatus, Excel→CSV-käsittely
├── controller.php                       # HTTP-pyyntöjen ja suodattimien käsittely
├── view.php                             # HTML-komponentit ja taulukon generointi
├── index.php                            # Pääsivu – tuotteiden selaus ja suodattimet
├── update.php                           # Automaattipäivitys + reaaliaikainen edistymispalkki
├── styles.css                           # Alkon brändivärit ja responsiivinen ulkoasu
├── composer.json
├── composer.lock
├── PROJEKTI_DOKUMENTAATIO.txt
└── README.md
```

---

## Käynnistys ja asennus

```bash
# 1. Kloonaa repositorio
git clone https://github.com/Jxnina/Web_ja_peliteknologian_perusteet.git
cd Web_ja_peliteknologian_perusteet

# 2. Asenna riippuvuus (SimpleXLSX)
composer require shuchkin/simplexlsx

# 3. Luo data-kansio (jos ei vielä ole)
mkdir data && chmod 755 data

# 4. Käynnistä PHP-palvelin
php -S localhost:8000

# 5. Avaa selaimessa
http://localhost:8000

```
---

## Käyttöohje
Selaus ja suodattimet

- 25 tuotetta per sivu
- Käytä yläreunan suodattimia (tyyppi, maa, pullokoko, hinta, energia)
- Pullokokoryhmät: Pienet, Keskikokoiset, Suuret, Viinit 0.75 l jne.
- Sivutus alhaalla

## Hinnaston päivitys

1. Klikkaa pääsivulla painiketta "Päivitä hinnasto Alkon sivuilta"
2. Seuraa reaaliaikaista edistymispalkkia
3. Valmis → palaa automaattisesti tuoreeseen dataan

Tekijä
Jxnina
Web- ja peliteknologian perusteet -kurssi
Joulukuu 2025
