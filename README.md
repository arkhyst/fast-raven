# FastRaven  
**State:** use only for small and non-critical projects
**Version:** v0.4  

FastRaven is a minimalistic and fast PHP framework for **monolithic apps**.  
It focuses on simplicity, clarity, and raw performance: no unnecessary layers, no dependencies you don’t control.

---

### Requeriments  
- **PHP** ^8.4  
- **MySQL** ^8.0  
- **Composer** ^2.9.2  
- **Apache** ^2.4 (if using Apache)  

> **Note:** Remember that web server user should have full permissions to the project folder.
> **Note:** You need to install PHP modules for MySQL, Apache, and other optional extensions.

---

### Installation  
1. `composer create-project fast-raven/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside fast-raven project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require fast-raven/library` : Install fast-raven framework inside existing composer project.

---

### Roadmap: v0.4
- ~~Research and fixing of security issues.~~
- ~~Research and fixing of performance issues.~~
- ~~Improvement of DataSlave: regex filtering for injected strings.~~
- ~~Implementation of Request file retrieval controls.~~
- ~~Implementation of rate-limiting configuration.~~
- ~~Implementation of custom developer middleware.~~
- ~~Addition of StorageWorker for cache and file storage management.~~

### Roadmap: v1.0
- Improvement of ValidationWorker complexity and ValidationFlags.
- Implementation of unauthorized flow with redirects after login.
- Implementation of Multi-DB support for DataSlave.
- ~~Upgrade of Router component: refactor to support more complex architectures.~~
- Upgrade of PHP version to 8.5 (if is globally supported by hosting providers).
- Upgrade of DataSlave: implementation of index and join for scalable queries.
- Upgrade of ValidationWorker: implementation of validation details when validation fails.

---

**Author:** arkhyst  
**License:** MIT (see LICENSE)  