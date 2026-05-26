![#eXperiance CORE](https://brend.h0model.org/software/core/github/experiance_core_github.png)

[![Release](https://img.shields.io/github/release/HZKnight/eXperience-CORE.svg?style=for-the-badge)](https://github.com/HZKnight/eXperience-CORE/releases/latest)
[![Pre-release](https://img.shields.io/github/tag-pre/HZKnight/eXperience-CORE.svg?label=pre-release&style=for-the-badge)]([https://github.com/HZKnight/eXperience-CORE/releases/tag/v1.0.0-alfa.2](https://github.com/HZKnight/eXperience-CORE/releases/tag/v1.0.0-alfa.2))

![Open Source](https://img.shields.io/badge/Open%20Source%20%E2%9D%A4%EF%B8%8F-0854C1?style=for-the-badge&logoColor=%23ffffff)
![GitHub License](https://img.shields.io/github/license/hzknight/eXperience-CORE?style=for-the-badge&color=%23BD0000)
[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-2.0-4baaaa.svg?style=for-the-badge)](code_of_conduct.md)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D_8.1-8892BF.svg?style=for-the-badge&color=%23777BB4&logo=php&logoColor=%23ffffff)](https://php.net)

---

## 🇮🇹 Italiano

`eXperience-CORE` è un framework di sistema leggero e ad alte prestazioni in PHP, progettato per orchestrare l'architettura di rete disaccoppiata e i sistemi CMS dell'ecosistema HZKnight. Fornisce componenti solidi per l'astrazione del database (DBAL), la gestione sicura delle configurazioni aziendali, logging standardizzato e gestione avanzata delle eccezioni.

Sviluppato secondo gli standard PSR per flussi di lavoro moderni.

### 🚀 Funzionalità Chiave
* **DBAL Avanzato:** Livello di astrazione del database con supporto nativo a **MySQL/MariaDB** e **SQLite**, con traduzione trasparente della sintassi cross-platform (es. gestione automatica dell'autoincremento).
* **Configurazione Enterprise Sicura:** `EConfigManager` basato su pattern Singleton con integrazione di file `.env`, sistemi di fallback e crittografia dei dati sensibili in **AES-256-CBC**.
* **Autoloading PSR-4:** Architettura nativa conforme agli standard PSR-4 per una gestione dei componenti fluida e performante.
* **Logger Flessibile:** Sistema multi-appender (`ELogger`) in grado di smistare contemporaneamente i log su file fisici, console ed email automatizzate.
* **Integrazione UIkit:** Utility di output pre-configurate per integrarsi nativamente con interfacce e dashboard amministrative basate sul frontend framework **UIkit**.

---

## 🇬🇧 English

`eXperience-CORE` is a lightweight, high-performance PHP systems framework designed to power the core architecture of decoupled enterprise networks and custom CMS systems within the HZKnight ecosystem. It provides robust components for database abstraction (DBAL), secure enterprise configuration management, standardized logging, and advanced exception handling.

Built under PSR standards with modern PHP development workflows in mind.

### 🚀 Key Features
* **Advanced DBAL:** Multi-driver Database Abstraction Layer supporting **MySQL/MariaDB** and **SQLite** out of the box with transparent cross-platform syntax translation (e.g., automatic autoincrement handling).
* **Secure Enterprise Config:** Singleton-based `EConfigManager` with `.env` integration, background fallbacks, and **AES-256-CBC** configuration encryption.
* **PSR-4 Autoloading:** Native PSR-4 compliant autoloader architecture for reliable performance.
* **Flexible Logger:** Multi-appender logger (`ELogger`) capable of routing system states to physical files, console, and automated emails simultaneously.
* **UIkit Integration:** Pre-configured output utilities designed to work seamlessly with administrative dashboards utilizing the **UIkit** frontend framework.

---

## 🛠️ Installation / Installazione

```bash
git clone https://github.com/HZKnight/experience-core.git
```

### Directory Mapping / Struttura Namespace
Assicurati che la struttura delle cartelle mappi correttamente il namespace di sistema root:

```text
src/
└── Experience/
    └── Core/
```

---

## 💻 Quick Start / Esempio di Utilizzo

### 1. Initialization / Inizializzazione
```php
require_once "src/eautoloader.class.php";

use Experience\Core\Io\Dbal\EDbManager;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Storage\Estorage;
use Experience\Core\Io\Storage\Driver\LocalStorageDriver;

// Setup secure configuration storage
$storage = Estorage::getStorage("app_storage", new LocalStorageDriver());
$config  = new EConfigManager('config.json', $storage);

// Initialize DBAL
$db = new EDbManager($config);
```

### 2. Slicing Data / Estrazione dati (Paginazione)
```php
// Safe cross-platform tabular data subset slicing (MySQL / SQLite friendly)
$results = $db->getRowSubSet("hz_test_table", 5, 10, "id", "DESC");
```

---

## 📋 Requirements & Specs / Requisiti di Sistema

* **PHP:** `^8.1` (Consigliato `8.4+` in ambienti FastCGI / PHP-FPM)
* **Extensions:** `PDO`, `openssl`, `json`, `session`
* **Licence / Licenza:** GNU Affero General Public License v3

---

## 👥 Authors & Network / Autori ed Ecosistema

* **Main Developer:** Luca Liscio (<lucliscio@h0model.org>) - *Italy*
* **Network Ecosystem:** Part of the **H0Model.Org** software network and company ecosystem.

---
Copyright (C) 2026 HZKnight. Produced under the AGPL-3.0 License.





-----

[![eXperience-CORE](https://github.com/HZKnight/eXperience-CORE/workflows/PHP%20Composer/badge.svg)](https://github.com/HZKnight/eXperience-CORE/actions)


![Issue](https://img.shields.io/github/issues/HZKnight/eXperience-CORE.svg)
[![Code Climate](https://codeclimate.com/github/HZKnight/Experience-CORE/badges/gpa.svg)](https://codeclimate.com/github/HZKnight/Experience-CORE)


Licences
-------------------------

- **eXperience CORE code** ©2022-2026 by HZKnight is licensed under [AGPL 3.0](https://www.gnu.org/licenses/agpl-3.0.html)
- **eXperience CORE User Guide and all related documentation** ©2022-2026 by HZKnight is licensed under [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

Descriprion
-------------------------

This is a simple PHP framework that I have developed to help me to develop my PHP applications.

Documentation
-------------------------

[Technical Documentation](https://hzknight.github.io/eXperience-CORE/)
