# Fast Raven  
**State:** production ready  
**Version:** v1.0.0  

**Fast Raven** is a **high-performance PHP** framework for building **fast, monolithic applications**.  
Optimized for **multi-site subdomain architectures**, it processes requests in 1ms (average speed in shared hosting) and makes **agile development** feel effortless. No bloat. No magic. Just speed.  

---

### Requeriments  
- **PHP** ^8.4  
- **MySQL** ^8.0  
- **Composer** ^2.9.2  
- **Apache** ^2.4 (if using Apache)  

> **Note:** Remember that web server user should have full permissions to the project folder.  
> **Note:** You need to install PHP modules for MySQL, Apache, and optional fast-cgi and apcu to reach that 1ms sweet spot.  

---

### Installation  
1. `composer create-project fast-raven/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside fast-raven project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require fast-raven/library` : Install fast-raven framework inside existing composer project.

---

### Documentation
- **[Documentation](docs/FRAMEWORK.md)** - Complete API reference and framework usage guide
- **[Roadmap](docs/ROADMAP.md)** - Future features and planned improvements

---

**Author:** arkhyst  
**License:** MIT (see LICENSE)  
